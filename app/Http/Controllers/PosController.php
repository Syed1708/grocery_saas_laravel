<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::active()->get();
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::with(['unit', 'category'])->active()->orderBy('name')->get();
        
        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $activeBranch = Branch::find($activeBranchId);
        
        $vatPercent = (float) ShopSetting::get('vat_percent', 0);
        $enableVat = (bool) ShopSetting::get('enable_vat', false);

        return view('pos.index', compact('categories', 'customers', 'products', 'activeBranch', 'vatPercent', 'enableVat'));
    }

    // 🔍 AJAX 1: Search Customers by Name or Phone
    public function searchCustomers(Request $request)
    {
        $query = $request->input('q', '');
        $customers = Customer::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'address', 'current_due', 'credit_limit']);

        return response()->json($customers);
    }

    // ➕ AJAX 2: Quick Add Customer on the Fly from POS
    public function quickAddCustomer(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:191',
            'phone'        => 'required|string|max:20|unique:customers,phone',
            'address'      => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'credit_limit' => $request->credit_limit ?? 10000.00,
            'opening_due'  => 0.00,
            'current_due'  => 0.00,
            'status'       => 'active',
        ]);

        return response()->json([
            'success'  => true,
            'customer' => $customer,
        ]);
    }

    // Process POS Checkout (Supports Split / Mixed Payments)
    public function checkout(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $cashierId = auth()->id();

        $sale = DB::transaction(function () use ($request, $activeBranchId, $cashierId) {
            $subtotal = 0;

            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']);
            }

            $discount = (float) $request->input('discount', 0);
            $vatPercent = (float) $request->input('vat_percent', 0);
            
            $baseAmount = max(0, $subtotal - $discount);
            $vatAmount = ($baseAmount * $vatPercent) / 100;
            $grandTotal = $baseAmount + $vatAmount;

            // Split payments breakdown
            $cashPaid = (float) $request->input('cash_paid', 0);
            $bkashPaid = (float) $request->input('bkash_paid', 0);
            $bankPaid = (float) $request->input('bank_paid', 0);
            $totalPaid = $cashPaid + $bkashPaid + $bankPaid;

            $customerId = $request->filled('customer_id') ? $request->customer_id : null;
            $previousDue = 0;

            if ($customerId) {
                $customer = Customer::findOrFail($customerId);
                $previousDue = (float) $customer->current_due;
            }

            $netPayable = $grandTotal + $previousDue;
            $currentInvoiceDue = max(0, $grandTotal - $totalPaid);
            $change = max(0, $totalPaid - $grandTotal);

            $paymentStatus = 'paid';
            if ($currentInvoiceDue > 0) {
                $paymentStatus = $totalPaid > 0 ? 'partial' : 'due';
            }

            // Determine Payment Method label
            $method = 'cash';
            $paidMethods = array_filter([
                'cash'  => $cashPaid > 0,
                'bkash' => $bkashPaid > 0,
                'bank'  => $bankPaid > 0,
            ]);
            if (count($paidMethods) > 1) {
                $method = 'mixed';
            } elseif (count($paidMethods) === 1) {
                $method = array_key_first($paidMethods);
            } elseif ($currentInvoiceDue > 0) {
                $method = 'due';
            }

            // Credit Limit Check
            if ($currentInvoiceDue > 0) {
                if (!$customerId) {
                    throw new \Exception('বাকি বিক্রয়ের ক্ষেত্রে কাস্টমার নির্বাচন করা বাধ্যতামূলক!');
                }
                if (($customer->current_due + $currentInvoiceDue) > $customer->credit_limit) {
                    throw new \Exception("কাস্টমারের অনুমোদিত বাকির সীমা (৳{$customer->credit_limit}) অতিক্রম করেছে!");
                }
            }

            // 1. Create Sale Record
            $invoiceNo = 'INV-' . date('ymd') . '-' . rand(1000, 9999);
            $sale = Sale::create([
                'branch_id'      => $activeBranchId,
                'customer_id'    => $customerId,
                'user_id'        => $cashierId,
                'invoice_no'     => $invoiceNo,
                'sale_date'      => now()->toDateString(),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'vat_percent'    => $vatPercent,
                'vat_amount'     => $vatAmount,
                'grand_total'    => $grandTotal,
                'paid_amount'    => min($totalPaid, $grandTotal),
                'due_amount'     => $currentInvoiceDue,
                'change_amount'  => $change,
                'payment_method' => $method,
                'payment_status' => $paymentStatus,
                'offline_uuid'   => $request->input('offline_uuid', Str::uuid()),
                'is_synced'      => true,
                'notes'          => "Split: Cash ৳{$cashPaid}, bKash ৳{$bkashPaid}, Bank ৳{$bankPaid}",
            ]);

            // 2. Deduct Branch Stock & Record Item Profit Snapshot
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

                // Decrement stock in active branch
                ProductStock::where('product_id', $product->id)
                    ->where('branch_id', $activeBranchId)
                    ->decrement('quantity', $itemData['quantity']);
            }

            // 3. Update Customer Due Balance
            if ($customerId) {
                if ($currentInvoiceDue > 0) {
                    $customer->increment('current_due', $currentInvoiceDue);
                } elseif ($totalPaid > $grandTotal) {
                    $extraPaid = $totalPaid - $grandTotal;
                    $customer->decrement('current_due', min($customer->current_due, $extraPaid));
                }
            }

            return $sale;
        });

        return response()->json([
            'success'     => true,
            'message'     => 'বিক্রয় সফল হয়েছে!',
            'sale_id'     => $sale->id,
            'invoice_no'  => $sale->invoice_no,
            'grand_total' => $sale->grand_total,
            'change'      => $sale->change_amount,
            'receipt_url' => route('sales.show', ['sale' => $sale->id]),
        ]);
    }
}