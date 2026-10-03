<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index()
    {
        $returns = SaleReturn::with(['sale', 'customer', 'cashier'])->latest()->paginate(15);
        return view('returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $sale = null;
        if ($request->filled('invoice_no')) {
            $sale = Sale::with(['items.product.unit', 'customer'])->where('invoice_no', $request->invoice_no)->first();
        }

        return view('returns.create', compact('sale'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id'       => 'required|exists:sales,id',
            'refund_method' => 'required|in:cash,balance',
            'items'         => 'required|array|min:1',
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        DB::transaction(function () use ($request, $sale, $activeBranchId) {
            $totalRefund = 0;
            $returnItems = [];

            foreach ($request->items as $productId => $itemData) {
                if (!empty($itemData['return_qty']) && $itemData['return_qty'] > 0) {
                    $qty = (float) $itemData['return_qty'];
                    $price = (float) $itemData['price'];
                    $condition = $itemData['condition'] ?? 'restock';

                    $totalRefund += ($qty * $price);
                    $returnItems[] = [
                        'product_id'     => $productId,
                        'unit_id'        => $itemData['unit_id'],
                        'quantity'       => $qty,
                        'refund_price'   => $price,
                        'item_condition' => $condition,
                    ];

                    // Restock inventory if item condition is good
                    if ($condition === 'restock') {
                        ProductStock::where('product_id', $productId)
                            ->where('branch_id', $activeBranchId)
                            ->increment('quantity', $qty);
                    }
                }
            }

            if ($totalRefund <= 0) {
                throw new \Exception('ফেরত দেওয়ার মতো কোনো পণ্য সিলেক্ট করা হয়নি!');
            }

            // 1. Create Return Record
            $saleReturn = SaleReturn::create([
                'branch_id'     => $activeBranchId,
                'sale_id'       => $sale->id,
                'customer_id'   => $sale->customer_id,
                'user_id'       => auth()->id(),
                'return_no'     => 'RET-' . date('ymd') . '-' . rand(100, 999),
                'return_date'   => now()->toDateString(),
                'total_refund'  => $totalRefund,
                'refund_method' => $request->refund_method,
                'notes'         => $request->notes,
            ]);

            // 2. Attach Return Items
            foreach ($returnItems as $rItem) {
                $rItem['sale_return_id'] = $saleReturn->id;
                SaleReturnItem::create($rItem);
            }

            // 3. Adjust customer due if refund_method is balance/due deduction
            if ($sale->customer_id && $request->refund_method === 'balance') {
                $sale->customer->decrement('current_due', min($sale->customer->current_due, $totalRefund));
            }
        });

        return redirect()->route('returns.index')->with('success', 'পণ্য সফলভাবে ফেরত ও রিফান্ড করা হয়েছে!');
    }
}