<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\ShopSetting;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    private function getShopInfo(): array
    {
        return [
            'name'       => ShopSetting::get('shop_name', 'মেসার্স মদিনা জেনারেল স্টোর'),
            'name_en'    => ShopSetting::get('shop_name_en', 'Madina General Store'),
            'address'    => ShopSetting::get('address', 'দোকান নং ১২, কাওরান বাজার, ঢাকা'),
            'phone'      => ShopSetting::get('phone', '01712345678'),
            'print_time' => now()->format('d M, Y h:i A'),
        ];
    }

    public function index()
    {
        $closings = CashRegister::with(['user', 'branch'])->latest('closing_date')->paginate(15);
        return view('reports.closings.index', compact('closings'));
    }

    public function create()
    {
        $today = now()->toDateString();
        $shop = $this->getShopInfo();

        // 1. Opening Cash from Cash Drawer Account
        $cashAccount = Account::where('type', 'cash')->first();
        $openingCash = $cashAccount ? (float) $cashAccount->opening_balance : 0.00;

        // 2. Today's Cash Inflows
        $cashSales = (float) Sale::where('sale_date', $today)
            ->whereIn('payment_method', ['cash', 'mixed'])
            ->sum('paid_amount');

        $cashDueCollected = (float) CustomerPayment::where('payment_date', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cashOtherIncome = (float) OtherIncome::where('income_date', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        // 3. Today's Cash Outflows
        $cashExpenses = (float) Expense::where('expense_date', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cashSupplierPaid = (float) SupplierPayment::where('payment_date', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cashRefunds = (float) SaleReturn::where('return_date', $today)
            ->where('refund_method', 'cash')
            ->sum('total_refund');

        // Total Receipts & Payments
        $totalCashIn  = $openingCash + $cashSales + $cashDueCollected + $cashOtherIncome;
        $totalCashOut = $cashExpenses + $cashSupplierPaid + $cashRefunds;
        $expectedCash = $totalCashIn - $totalCashOut;

        $totalInvoices = Sale::where('sale_date', $today)->count();

        return view('reports.closings.create', compact(
            'today', 'shop', 'openingCash', 'cashSales', 'cashDueCollected', 'cashOtherIncome',
            'cashExpenses', 'cashSupplierPaid', 'cashRefunds', 'totalCashIn', 'totalCashOut',
            'expectedCash', 'totalInvoices'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'closing_date'       => 'required|date',
            'opening_cash'       => 'required|numeric|min:0',
            'cash_sales'         => 'required|numeric|min:0',
            'cash_due_collected' => 'required|numeric|min:0',
            'cash_other_income'  => 'required|numeric|min:0',
            'cash_expenses'      => 'required|numeric|min:0',
            'cash_supplier_paid' => 'required|numeric|min:0',
            'cash_refunds'       => 'required|numeric|min:0',
            'expected_cash'      => 'required|numeric',
            'counted_cash'       => 'required|numeric|min:0',
            'total_invoices'     => 'required|integer|min:0',
            'notes'              => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        $counted = (float) $validated['counted_cash'];
        $expected = (float) $validated['expected_cash'];
        $difference = $counted - $expected;

        $closing = CashRegister::create([
            'branch_id'          => $activeBranchId,
            'user_id'            => auth()->id(),
            'closing_date'       => $validated['closing_date'],
            'opening_time'       => '08:00:00',
            'closing_time'       => now()->toTimeString(),
            'opening_cash'       => $validated['opening_cash'],
            'cash_sales'         => $validated['cash_sales'],
            'cash_due_collected' => $validated['cash_due_collected'],
            'cash_other_income'  => $validated['cash_other_income'],
            'cash_expenses'      => $validated['cash_expenses'],
            'cash_supplier_paid' => $validated['cash_supplier_paid'],
            'cash_refunds'       => $validated['cash_refunds'],
            'expected_cash'      => $expected,
            'counted_cash'       => $counted,
            'difference'         => $difference,
            'total_invoices'     => $validated['total_invoices'],
            'notes'              => $validated['notes'],
            'status'             => 'closed',
        ]);

        return redirect()->route('closings.show', $closing)
            ->with('success', 'কাউন্টার ক্যাশ ক্লোজিং ও ডে-এন্ড Z-Report সফলভাবে সম্পন্ন হয়েছে!');
    }

    public function show(CashRegister $closing)
    {
        $closing->load(['user', 'branch']);
        $shop = $this->getShopInfo();
        return view('reports.closings.show', compact('closing', 'shop'));
    }
}