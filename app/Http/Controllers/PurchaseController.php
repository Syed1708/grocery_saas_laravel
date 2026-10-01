<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('chalan_no', 'like', "%{$s}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $purchases = $query->latest('purchase_date')->paginate(15)->withQueryString();
        $totalPurchases = Purchase::sum('grand_total');
        $totalDue = Purchase::sum('due_amount');

        return view('purchases.index', compact('purchases', 'totalPurchases', 'totalDue'));
    }


    public function create()
    {
        // 🚀 Fetch all active suppliers and products for the manual dropdowns
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::with('unit')->active()->orderBy('name')->get();

        $activeBranch = Branch::find(
            session('active_branch_id')
                ?? Branch::where('is_main', true)->value('id')
                ?? Branch::first()?->id
        );

        $autoChalan = 'CHAL-' . date('ymd') . '-' . rand(100, 999);

        return view('purchases.create', compact('suppliers', 'products', 'activeBranch', 'autoChalan'));
    }

    // 🔍 AJAX 1: Search Suppliers by Name, Phone, or Company
    public function searchSuppliers(Request $request)
    {
        $query = $request->input('q', '');
        $suppliers = Supplier::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('company_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'company_name', 'phone', 'current_due']);

        return response()->json($suppliers);
    }

    // 🔍 AJAX 2: Barcode Scanner & Product Search
    public function searchProducts(Request $request)
    {
        $query = $request->input('q', '');
        $products = Product::with('unit')
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('barcode', $query) // Exact barcode match for scanner gun
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('name_en', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(15)
            ->get();

        return response()->json($products->map(function ($p) {
            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'barcode'        => $p->barcode,
                'unit_id'        => $p->unit_id,
                'unit_name'      => $p->unit ? $p->unit->short_code : 'pc',
                'purchase_price' => (float) $p->purchase_price,
                'current_stock'  => $p->currentStock(),
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'chalan_no'          => 'required|string|max:100|unique:purchases,chalan_no',
            'purchase_date'      => 'required|date',
            'payment_method'     => 'required|string|in:cash,bank,bkash,nagad',
            'paid_amount'        => 'required|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'vat_percent'        => 'nullable|numeric|min:0|max:100',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.commission' => 'nullable|numeric|min:0',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        DB::transaction(function () use ($request, $activeBranchId) {
            $subtotal = 0;
            $totalQty = 0;

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $comm = (float) ($item['commission'] ?? 0);
                $lineTotal = ($qty * $cost) - $comm;

                $subtotal += $lineTotal;
                $totalQty += $qty;
            }

            $discount = (float) $request->input('discount', 0);
            $transport = (float) $request->input('transport_cost', 0);
            $vatPercent = (float) $request->input('vat_percent', 0);

            $baseAmount = max(0, $subtotal - $discount);
            $vatAmount = ($baseAmount * $vatPercent) / 100;
            $grandTotal = $baseAmount + $transport + $vatAmount;

            $supplier = Supplier::findOrFail($request->supplier_id);
            $previousDue = (float) $supplier->current_due;

            $paid = (float) $request->input('paid_amount', 0);
            $netPayable = $grandTotal + $previousDue;
            $remainingDue = max(0, $netPayable - $paid);

            $paymentStatus = 'due';
            if ($paid >= $grandTotal) {
                $paymentStatus = 'paid';
            } elseif ($paid > 0) {
                $paymentStatus = 'partial';
            }

            // 1. Create Purchase Invoice
            $purchase = Purchase::create([
                'branch_id'      => $activeBranchId,
                'supplier_id'    => $supplier->id,
                'chalan_no'      => $request->chalan_no,
                'purchase_date'  => $request->purchase_date,
                'subtotal'       => $subtotal,
                'total_quantity' => $totalQty,
                'discount'       => $discount,
                'transport_cost' => $transport,
                'vat_percent'    => $vatPercent,
                'vat_amount'     => $vatAmount,
                'grand_total'    => $grandTotal,
                'previous_due'   => $previousDue,
                'paid_amount'    => $paid,
                'due_amount'     => max(0, $grandTotal - $paid),
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'notes'          => $request->notes,
            ]);

            // 2. Process Items: Stock Auto-Increment, Commission & Cost Update
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $cost = (float) $itemData['unit_cost'];
                $comm = (float) ($itemData['commission'] ?? 0);
                $lineTotal = ($qty * $cost) - $comm;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $product->id,
                    'unit_id'     => $product->unit_id,
                    'quantity'    => $qty,
                    'unit_cost'   => $cost,
                    'commission'  => $comm,
                    'line_total'  => $lineTotal,
                ]);

                // Increment branch stock
                // 🚀 Clean & safe atomic branch stock increment:
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $activeBranchId],
                    ['quantity' => 0]
                );
                $stock->increment('quantity', $qty);

                // Update product wholesale cost
                $product->update(['purchase_price' => $cost]);
            }

            // 3. Update Supplier Ledger
            $invoiceDue = max(0, $grandTotal - $paid);
            if ($invoiceDue > 0) {
                $supplier->increment('current_due', $invoiceDue);
            } elseif ($paid > $grandTotal) {
                // If overpaid against previous dues, reduce supplier's previous debt
                $extraPaid = $paid - $grandTotal;
                $supplier->decrement('current_due', min($supplier->current_due, $extraPaid));
            }
        });

        return redirect()->route('purchases.index')->with('success', 'চালান সফলভাবে সম্পন্ন হয়েছে! স্টক ও মহাজন খাতা আপডেট করা হয়েছে।');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product.unit', 'branch']);
        return view('purchases.show', compact('purchase'));
    }


    // In app/Http/Controllers/PurchaseController.php

    public function edit(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product.unit']);
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::with('unit')->active()->orderBy('name')->get();
        $activeBranch = $purchase->branch;

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'activeBranch'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'chalan_no'          => 'required|string|max:100|unique:purchases,chalan_no,' . $purchase->id,
            'purchase_date'      => 'required|date',
            'payment_method'     => 'required|string|in:cash,bank,bkash,nagad',
            'paid_amount'        => 'required|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'vat_percent'        => 'nullable|numeric|min:0|max:100',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.commission' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            // 1. REVERSE OLD STOCK & OLD SUPPLIER DUE
            foreach ($purchase->items as $oldItem) {
                ProductStock::where('product_id', $oldItem->product_id)
                    ->where('branch_id', $purchase->branch_id)
                    ->decrement('quantity', $oldItem->quantity);
            }

            if ($purchase->due_amount > 0) {
                Supplier::where('id', $purchase->supplier_id)->decrement('current_due', $purchase->due_amount);
            }

            // 2. CALCULATE NEW TOTALS
            $subtotal = 0;
            $totalQty = 0;

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $comm = (float) ($item['commission'] ?? 0);
                $subtotal += ($qty * $cost) - $comm;
                $totalQty += $qty;
            }

            $discount = (float) $request->input('discount', 0);
            $transport = (float) $request->input('transport_cost', 0);
            $vatPercent = (float) $request->input('vat_percent', 0);

            $baseAmount = max(0, $subtotal - $discount);
            $vatAmount = ($baseAmount * $vatPercent) / 100;
            $grandTotal = $baseAmount + $transport + $vatAmount;

            $paid = (float) $request->input('paid_amount', 0);
            $due = max(0, $grandTotal - $paid);

            $paymentStatus = 'due';
            if ($paid >= $grandTotal) {
                $paymentStatus = 'paid';
            } elseif ($paid > 0) {
                $paymentStatus = 'partial';
            }

            // 3. UPDATE PURCHASE INVOICE
            $purchase->update([
                'supplier_id'    => $request->supplier_id,
                'chalan_no'      => $request->chalan_no,
                'purchase_date'  => $request->purchase_date,
                'subtotal'       => $subtotal,
                'total_quantity' => $totalQty,
                'discount'       => $discount,
                'transport_cost' => $transport,
                'vat_percent'    => $vatPercent,
                'vat_amount'     => $vatAmount,
                'grand_total'    => $grandTotal,
                'paid_amount'    => $paid,
                'due_amount'     => $due,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'notes'          => $request->notes,
            ]);

            // 4. RE-CREATE ITEMS & APPLY NEW STOCK INCREMENTS
            $purchase->items()->delete();

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $cost = (float) $itemData['unit_cost'];
                $comm = (float) ($itemData['commission'] ?? 0);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $product->id,
                    'unit_id'     => $product->unit_id,
                    'quantity'    => $qty,
                    'unit_cost'   => $cost,
                    'commission'  => $comm,
                    'line_total'  => ($qty * $cost) - $comm,
                ]);

                // Increment stock with new quantity
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $purchase->branch_id],
                    ['quantity' => 0]
                );
                $stock->increment('quantity', $qty);

                // Update product purchase cost
                $product->update(['purchase_price' => $cost]);
            }

            // 5. APPLY NEW DUE TO SUPPLIER
            if ($due > 0) {
                Supplier::where('id', $request->supplier_id)->increment('current_due', $due);
            }
        });

        return redirect()->route('purchases.show', ['purchase' => $purchase->id])
            ->with('success', 'ক্রয় চালান সফলভাবে আপডেট করা হয়েছে এবং স্টক পুনর্গণনা করা হয়েছে!');
    }
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                ProductStock::where('product_id', $item->product_id)
                    ->where('branch_id', $purchase->branch_id)
                    ->decrement('quantity', $item->quantity);
            }

            if ($purchase->due_amount > 0) {
                Supplier::where('id', $purchase->supplier_id)->decrement('current_due', $purchase->due_amount);
            }

            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'চালানটি সফলভাবে বাতিল ও স্টক রিভার্স করা হয়েছে!');
    }
}
