<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'cashier', 'items.product']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest('sale_date')->paginate(15)->withQueryString();
        $totalSales = Sale::sum('grand_total');
        $totalDue = Sale::sum('due_amount');

        return view('sales.index', compact('sales', 'totalSales', 'totalDue'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'cashier', 'items.product.unit', 'branch']);
        return view('sales.show', compact('sale'));
    }

    // ✏️ 1. Edit Sale Invoice Screen

    public function edit(Sale $sale)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
            abort(403, 'বিক্রয় ইনভয়েস সংশোধন করার অনুমতি শুধুমাত্র অ্যাডমিন ও ম্যানেজারের রয়েছে।');
        }

        $sale->load(['customer', 'items.product.unit']);
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::with('unit')->active()->orderBy('name')->get();

        // 🚀 Prepare items for JavaScript safely
        $saleItems = $sale->items->map(function ($it) {
            return [
                'product_id' => $it->product_id,
                'name'       => $it->product?->name ?? 'Product',
                'unit'       => $it->unit?->short_code ?? 'pc',
                'price'      => (float) $it->unit_price,
                'quantity'   => (float) $it->quantity,
            ];
        });

        return view('sales.edit', compact('sale', 'customers', 'products', 'saleItems'));
    }

    // 💾 2. Update Sale Invoice (Auto Reconciles Stock & Customer Due)
    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'customer_id'        => 'nullable|exists:customers,id',
            'sale_date'          => 'required|date',
            'paid_amount'        => 'required|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'vat_percent'        => 'nullable|numeric|min:0|max:100',
            'payment_method'     => 'required|string|in:cash,card,bkash,nagad,rocket,bank,mixed,due',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $sale) {
            
            // 🔄 STEP 1: REVERSE OLD EFFECTS (Stock & Customer Due)
            foreach ($sale->items as $oldItem) {
                ProductStock::where('product_id', $oldItem->product_id)
                    ->where('branch_id', $sale->branch_id)
                    ->increment('quantity', $oldItem->quantity); // Put stock back
            }

            // Reverse old customer due if existed
            if ($sale->customer_id && $sale->due_amount > 0) {
                Customer::where('id', $sale->customer_id)->decrement('current_due', $sale->due_amount);
            }

            // 🧮 STEP 2: CALCULATE NEW TOTALS
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']);
            }

            $discount = (float) $request->input('discount', 0);
            $vatPercent = (float) $request->input('vat_percent', 0);
            $baseAmount = max(0, $subtotal - $discount);
            $vatAmount = ($baseAmount * $vatPercent) / 100;
            $grandTotal = $baseAmount + $vatAmount;

            $paid = (float) $request->input('paid_amount', 0);
            $newDue = max(0, $grandTotal - $paid);
            $change = max(0, $paid - $grandTotal);

            $paymentStatus = 'paid';
            if ($newDue > 0) {
                $paymentStatus = $paid > 0 ? 'partial' : 'due';
            }

            // 📝 STEP 3: UPDATE SALE RECORD
            $sale->update([
                'customer_id'    => $request->customer_id,
                'sale_date'      => $request->sale_date,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'vat_percent'    => $vatPercent,
                'vat_amount'     => $vatAmount,
                'grand_total'    => $grandTotal,
                'paid_amount'    => min($paid, $grandTotal),
                'due_amount'     => $newDue,
                'change_amount'  => $change,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'notes'          => $request->notes ?? "Edited on " . now()->format('d M, Y h:i A'),
            ]);

            // 📦 STEP 4: RE-CREATE ITEMS & DEDUCT NEW STOCK
            $sale->items()->delete();

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'unit_id'       => $product->unit_id,
                    'quantity'      => $itemData['quantity'],
                    'unit_price'    => $itemData['unit_price'],
                    'purchase_cost' => $product->purchase_price,
                    'discount'      => $itemData['discount'] ?? 0.00,
                    'line_total'    => ($itemData['quantity'] * $itemData['unit_price']),
                ]);

                // Deduct newly specified stock from branch
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $sale->branch_id],
                    ['quantity' => 0]
                );
                $stock->decrement('quantity', $itemData['quantity']);
            }

            // 👤 STEP 5: APPLY NEW DUE TO CUSTOMER
            if ($request->customer_id && $newDue > 0) {
                Customer::where('id', $request->customer_id)->increment('current_due', $newDue);
            }
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'বিক্রয় মেমো সফলভাবে সংশোধন করা হয়েছে এবং স্টক পুনর্গণনা করা হয়েছে!');
    }

    // 🗑️ 3. Void / Cancel Sale Invoice
    public function destroy(Sale $sale)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'বিক্রয় চালান বাতিল করার অনুমতি শুধুমাত্র অ্যাডমিনের রয়েছে।');
        }

        DB::transaction(function () use ($sale) {
            // Restore branch inventory
            foreach ($sale->items as $item) {
                ProductStock::where('product_id', $item->product_id)
                    ->where('branch_id', $sale->branch_id)
                    ->increment('quantity', $item->quantity);
            }

            // Deduct customer due if uncollected
            if ($sale->customer_id && $sale->due_amount > 0) {
                Customer::where('id', $sale->customer_id)->decrement('current_due', $sale->due_amount);
            }

            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'বিক্রয় চালানটি সফলভাবে বাতিল ও স্টক রিভার্স করা হয়েছে!');
    }
}