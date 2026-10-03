<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with('customer')->latest()->paginate(15);
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $products = Product::with('unit')->active()->orderBy('name')->get();
        $activeBranch = Branch::find(session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id);
        $autoNo = 'QUO-' . date('ymd') . '-' . rand(100, 999);

        return view('quotations.create', compact('customers', 'products', 'activeBranch', 'autoNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quotation_no'   => 'required|string|unique:quotations,quotation_no',
            'quotation_date' => 'required|date',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        DB::transaction(function () use ($request, $activeBranchId) {
            $subtotal = 0;

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $comm = (float) ($item['commission'] ?? 0);
                $subtotal += ($qty * $price) - $comm;
            }

            $discount = (float) $request->input('discount', 0);
            $grandTotal = max(0, $subtotal - $discount);

            $quotation = Quotation::create([
                'branch_id'      => $activeBranchId,
                'customer_id'    => $request->customer_id,
                'quotation_no'   => $request->quotation_no,
                'quotation_date' => $request->quotation_date,
                'expiry_date'    => $request->expiry_date,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'grand_total'    => $grandTotal,
                'status'         => 'pending',
                'notes'          => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id'   => $product->id,
                    'unit_id'      => $product->unit_id,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'line_total'   => ($qty * $price),
                ]);
            }
        });

        return redirect()->route('quotations.index')->with('success', 'দরপত্র / মেমো সফলভাবে তৈরি করা হয়েছে!');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product.unit', 'branch']);
        return view('quotations.show', compact('quotation'));
    }

    // ⚡ 1-Click Convert Quotation to Live Sales Invoice!
    public function convertToSale(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'এই দরপত্রটি ইতিমধ্যে বিক্রয় ইনভয়েসে রূপান্তর করা হয়েছে।');
        }

        $activeBranchId = $quotation->branch_id;
        $cashierId = auth()->id();

        $sale = DB::transaction(function () use ($quotation, $activeBranchId, $cashierId) {
            $invoiceNo = 'INV-' . date('ymd') . '-' . rand(1000, 9999);

            $sale = Sale::create([
                'branch_id'      => $activeBranchId,
                'customer_id'    => $quotation->customer_id,
                'user_id'        => $cashierId,
                'invoice_no'     => $invoiceNo,
                'sale_date'      => now()->toDateString(),
                'subtotal'       => $quotation->subtotal,
                'discount'       => $quotation->discount,
                'vat_percent'    => 0,
                'vat_amount'     => 0,
                'grand_total'    => $quotation->grand_total,
                'paid_amount'    => $quotation->grand_total,
                'due_amount'     => 0,
                'change_amount'  => 0,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'offline_uuid'   => Str::uuid(),
                'is_synced'      => true,
                'notes'          => "Converted from Quotation #{$quotation->quotation_no}",
            ]);

            foreach ($quotation->items as $qItem) {
                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $qItem->product_id,
                    'unit_id'       => $qItem->unit_id,
                    'quantity'      => $qItem->quantity,
                    'unit_price'    => $qItem->unit_price,
                    'purchase_cost' => $qItem->product->purchase_price,
                    'discount'      => 0,
                    'line_total'    => $qItem->line_total,
                ]);

                // Deduct stock
                ProductStock::where('product_id', $qItem->product_id)
                    ->where('branch_id', $activeBranchId)
                    ->decrement('quantity', $qItem->quantity);
            }

            $quotation->update(['status' => 'converted']);

            return $sale;
        });

        return redirect()->route('sales.show', ['sale' => $sale->id])
            ->with('success', 'দরপত্রটি সফলভাবে বিক্রয় ইনভয়েসে রূপান্তর করা হয়েছে এবং স্টক কাটা হয়েছে!');
    }
}