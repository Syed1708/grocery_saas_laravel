<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

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
}