<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShopSetting;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    private function getShopInfo(): array
    {
        return [
            'name'       => ShopSetting::get('shop_name', 'মেসার্স মদিনা জেনারেল স্টোর'),
            'name_en'    => ShopSetting::get('shop_name_en', 'Madina General Store'),
            'address'    => ShopSetting::get('address', 'দোকান নং ১২, কাওরান বাজার, ঢাকা'),
            'phone'      => ShopSetting::get('phone', '01712345678'),
            'email'      => ShopSetting::get('email', 'info@madinastore.test'),
            'print_time' => now()->format('d M, Y h:i A'),
        ];
    }

    // 1. Balance Report (দৈনিক ক্যাশ ও সার্বিক ব্যালেন্স রিপোর্ট)
    public function balanceReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());
        $shop     = $this->getShopInfo();

        // 1. Sales Breakdown
        $salesQuery = Sale::whereBetween('sale_date', [$fromDate, $toDate]);
        $cashSales   = (float) (clone $salesQuery)->where('payment_method', 'cash')->sum('paid_amount');
        $bkashSales  = (float) (clone $salesQuery)->where('payment_method', 'bkash')->sum('paid_amount');
        $nagadSales  = (float) (clone $salesQuery)->whereIn('payment_method', ['nagad', 'rocket'])->sum('paid_amount');
        $bankSales   = (float) (clone $salesQuery)->where('payment_method', 'bank')->sum('paid_amount');
        $mixedSales  = (float) (clone $salesQuery)->where('payment_method', 'mixed')->sum('paid_amount');
        $dueSales    = (float) (clone $salesQuery)->sum('due_amount');
        $totalSales  = (float) (clone $salesQuery)->sum('grand_total');

        // Total Cash from sales (cash + direct cash portion)
        $totalSalesCash = $cashSales + $mixedSales;

        // 2. Collection & Incomes
        $dueCollection = (float) CustomerPayment::whereBetween('payment_date', [$fromDate, $toDate])->sum('amount');
        $otherIncome   = (float) OtherIncome::whereBetween('income_date', [$fromDate, $toDate])->sum('amount');
        $totalReceipts = $totalSalesCash + $dueCollection + $otherIncome;

// 3. Purchases & Payments
        $purchasesQuery = Purchase::whereBetween('purchase_date', [$fromDate, $toDate]);
        $paidPurchase   = (float) (clone $purchasesQuery)->sum('paid_amount');
        $duePurchase    = (float) (clone $purchasesQuery)->sum('due_amount');
        $duePaid        = (float) SupplierPayment::whereBetween('payment_date', [$fromDate, $toDate])->sum('amount');
        $expense        = (float) Expense::whereBetween('expense_date', [$fromDate, $toDate])->sum('amount');

        // Salary / Payroll check (Auto-detects column name)
        $salaryExpense = 0.00;
        if (Schema::hasTable('payrolls')) {
            $payrollColumns = ['net_paid', 'net_salary', 'paid_amount', 'amount', 'total_paid', 'salary_amount', 'net_pay'];
            $payrollCol = null;
            foreach ($payrollColumns as $col) {
                if (Schema::hasColumn('payrolls', $col)) {
                    $payrollCol = $col;
                    break;
                }
            }

            if ($payrollCol) {
                $dateCol = Schema::hasColumn('payrolls', 'payment_date') 
                    ? 'payment_date' 
                    : (Schema::hasColumn('payrolls', 'paid_at') ? 'paid_at' : 'created_at');

                $salaryQuery = DB::table('payrolls');
                if ($dateCol === 'created_at') {
                    $salaryQuery->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
                } else {
                    $salaryQuery->whereBetween($dateCol, [$fromDate, $toDate]);
                }

                $salaryExpense += (float) $salaryQuery->sum($payrollCol);
            }
        }

        // 4. Total In Hand Calculation
        $totalPayments = $paidPurchase + $duePaid + $expense + $salaryExpense;
        $totalInHand   = $totalReceipts - $totalPayments;

        return view('reports.balance', compact(
            'fromDate', 'toDate', 'shop', 'cashSales', 'bkashSales', 'nagadSales', 'bankSales',
            'dueSales', 'totalSales', 'dueCollection', 'otherIncome', 'totalReceipts',
            'paidPurchase', 'duePurchase', 'duePaid', 'expense', 'salaryExpense',
            'totalPayments', 'totalInHand'
        ));
    }

    // 2. Due Collection Report (কাস্টমার বাকি আদায় রিপোর্ট)
    public function dueCollectionReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());
        $shop     = $this->getShopInfo();

        $payments = CustomerPayment::with(['customer', 'branch'])
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->latest('payment_date')
            ->paginate(25)
            ->withQueryString();

        $totalAmount = CustomerPayment::whereBetween('payment_date', [$fromDate, $toDate])->sum('amount');

        return view('reports.due_collection', compact('payments', 'totalAmount', 'fromDate', 'toDate', 'shop'));
    }

    // 3. Due Paid Report (মহাজন বাকি পরিশোধ রিপোর্ট)
    public function duePaidReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());
        $shop     = $this->getShopInfo();

        $payments = SupplierPayment::with(['supplier', 'branch'])
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->latest('payment_date')
            ->paginate(25)
            ->withQueryString();

        $totalAmount = SupplierPayment::whereBetween('payment_date', [$fromDate, $toDate])->sum('amount');

        return view('reports.due_paid', compact('payments', 'totalAmount', 'fromDate', 'toDate', 'shop'));
    }

    // 4. Sale Report (বিক্রয় চালান রিপোর্ট)
    public function saleReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());
        $shop     = $this->getShopInfo();

        $query = Sale::with(['customer', 'cashier'])
            ->whereBetween('sale_date', [$fromDate, $toDate]);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $sales = $query->latest('sale_date')->paginate(25)->withQueryString();
        $totalGrand = (float) (clone $query)->sum('grand_total');
        $totalPaid  = (float) (clone $query)->sum('paid_amount');
        $totalDue   = (float) (clone $query)->sum('due_amount');

        return view('reports.sales', compact('sales', 'totalGrand', 'totalPaid', 'totalDue', 'fromDate', 'toDate', 'shop'));
    }

    // 5. Purchase Report (ক্রয় চালান রিপোর্ট)
    public function purchaseReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());
        $shop     = $this->getShopInfo();

        $purchases = Purchase::with(['supplier', 'branch'])
            ->whereBetween('purchase_date', [$fromDate, $toDate])
            ->latest('purchase_date')
            ->paginate(25)
            ->withQueryString();

        $totalGrand = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])->sum('grand_total');
        $totalPaid  = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])->sum('paid_amount');
        $totalDue   = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])->sum('due_amount');

        return view('reports.purchases', compact('purchases', 'totalGrand', 'totalPaid', 'totalDue', 'fromDate', 'toDate', 'shop'));
    }

// 6. Product Stock Report (পণ্য মজুদ ও মূল্যায়ন রিপোর্ট)
    public function productStockReport(Request $request)
    {
        $shop = $this->getShopInfo();
        $categories = \App\Models\Category::active()->get();

        $query = Product::with(['unit', 'category', 'stocks'])->active();

        // 1. Search by Name, SKU or Barcode
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('name_en', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%");
            });
        }

        // 2. Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $allProducts = $query->get();

        // 3. Filter by Stock Status
        $statusFilter = $request->input('stock_status', 'all');
        $products = $allProducts->filter(function ($p) use ($statusFilter) {
            $stock = $p->currentStock();
            $p->current_stock_count = $stock;
            $p->stock_cost_value    = $stock * (float) $p->purchase_price;
            $p->stock_retail_value  = $stock * (float) $p->selling_price;

            if ($statusFilter === 'in_stock') {
                return $stock > $p->alert_quantity;
            } elseif ($statusFilter === 'low_stock') {
                return $stock > 0 && $stock <= $p->alert_quantity;
            } elseif ($statusFilter === 'out_of_stock') {
                return $stock <= 0;
            }
            return true;
        });

        $totalCostValuation   = $products->sum('stock_cost_value');
        $totalRetailValuation = $products->sum('stock_retail_value');

        return view('reports.stock', compact('products', 'categories', 'totalCostValuation', 'totalRetailValuation', 'shop'));
    }
    // 7. Supplier Purchase Report (মহাজনভিত্তিক ক্রয় রিপোর্ট)
    public function supplierPurchaseReport(Request $request)
    {
        $shop = $this->getShopInfo();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $selectedSupplier = null;
        $purchases = collect();
        $totalPurchased = 0;
        $totalPaid = 0;
        $totalDue = 0;

        if ($request->filled('supplier_id')) {
            $selectedSupplier = Supplier::findOrFail($request->supplier_id);
            $query = Purchase::where('supplier_id', $selectedSupplier->id);

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('purchase_date', [$request->from_date, $request->to_date]);
            }

            $purchases = $query->latest('purchase_date')->get();
            $totalPurchased = $purchases->sum('grand_total');
            $totalPaid = $purchases->sum('paid_amount');
            $totalDue = $purchases->sum('due_amount');
        }

        return view('reports.supplier_purchases', compact(
            'suppliers', 'selectedSupplier', 'purchases', 'totalPurchased', 'totalPaid', 'totalDue', 'shop'
        ));
    }

    // 8. Daily Product Profit Report (পণ্যভিত্তিক দৈনিক লাভ রিপোর্ট)
    public function dailyProductProfitReport(Request $request)
    {
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());
        $shop     = $this->getShopInfo();

        $items = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('sale_date', [$fromDate, $toDate]);
        })
            ->with(['product.unit', 'product.category'])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('AVG(unit_price) as avg_sale_price'),
                DB::raw('AVG(purchase_cost) as avg_purchase_cost'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('SUM(quantity * purchase_cost) as total_cost')
            )
            ->groupBy('product_id')
            ->get();

        $totalRevenue = $items->sum('total_revenue');
        $totalCost    = $items->sum('total_cost');
        $totalProfit  = $totalRevenue - $totalCost;

        return view('reports.daily_product_profit', compact(
            'items', 'totalRevenue', 'totalCost', 'totalProfit', 'fromDate', 'toDate', 'shop'
        ));
    }


    // 9. Customer Ledger Report (কাস্টমার খতিয়ান ও বাকি রিপোর্ট)
    public function customerLedgerReport(Request $request)
    {
        $shop = $this->getShopInfo();
        $customers = Customer::active()->orderBy('name')->get();
        $selectedCustomer = null;
        $sales = collect();
        $payments = collect();
        $totalSales = 0;
        $totalPaid = 0;
        $totalDue = 0;
        $totalCollected = 0;

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::findOrFail($request->customer_id);
            $salesQuery = Sale::where('customer_id', $selectedCustomer->id);
            $paymentsQuery = CustomerPayment::where('customer_id', $selectedCustomer->id);

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $salesQuery->whereBetween('sale_date', [$request->from_date, $request->to_date]);
                $paymentsQuery->whereBetween('payment_date', [$request->from_date, $request->to_date]);
            }

            $sales = $salesQuery->latest('sale_date')->get();
            $payments = $paymentsQuery->latest('payment_date')->get();

            $totalSales = $sales->sum('grand_total');
            $totalPaid = $sales->sum('paid_amount');
            $totalDue = $sales->sum('due_amount');
            $totalCollected = $payments->sum('amount');
        }

        return view('reports.customer_ledger', compact(
            'customers', 'selectedCustomer', 'sales', 'payments',
            'totalSales', 'totalPaid', 'totalDue', 'totalCollected', 'shop'
        ));
    }
}