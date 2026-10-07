<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\BankChequeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyAssetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\OtherIncomeController;
use App\Http\Controllers\OwnerTransactionController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSectionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryAdvanceController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ShopSettingController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware(['auth'])->group(function () {
    // POS & Sales
    Route::get('/sales', fn() => 'Sales List (Coming in Module 5)')->name('sales.index');
    Route::get('/dues', fn() => 'Customer Dues (Coming in Module 5)')->name('dues.index');

    // Products
    Route::get('/products', fn() => 'Product List (Coming in Module 3)')->name('products.index');

    // Purchases & Suppliers
    Route::get('/suppliers', fn() => 'Suppliers (Coming in Module 4)')->name('suppliers.index');
    Route::get('/purchases/create', fn() => 'New Purchase (Coming in Module 4)')->name('purchases.create');

    // Expenses & Accounts
    Route::get('/expenses', fn() => 'Daily Expenses (Coming in Module 6)')->name('expenses.index');
    Route::get('/accounts', fn() => 'Bank & Accounts (Coming in Module 8)')->name('accounts.index');
    Route::get('/reports/profit-loss', fn() => 'Profit Loss (Coming in Module 9)')->name('reports.profit-loss');
});



// 1. Subscription & Payment Routes (Publicly accessible when locked)
Route::get('/subscription/expired', [SubscriptionController::class, 'expired'])->name('subscription.expired');
Route::post('/subscription/submit-payment', [SubscriptionController::class, 'submitPayment'])->name('subscription.submit-payment');

// 2. Protected Store Area
Route::middleware(['auth'])->group(function () {
    // Branch CRUD
    Route::resource('branches', BranchController::class);
    Route::post('/branches/switch/{branch}', [BranchController::class, 'switch'])->name('branches.switch');

    // Manual License Renewal (by Admin)
    Route::post('/subscription/renew', [SubscriptionController::class, 'manualRenew'])->name('subscription.renew');
});


Route::middleware(['auth'])->group(function () {
    // Measurement Units CRUD
    Route::resource('units', UnitController::class);

    // Company Assets CRUD
    Route::resource('assets', CompanyAssetController::class);

    // Owner Capital & Drawings
    Route::get('/equity', [OwnerTransactionController::class, 'index'])->name('equity.index');
    Route::post('/equity', [OwnerTransactionController::class, 'store'])->name('equity.store');
    Route::delete('/equity/{transaction}', [OwnerTransactionController::class, 'destroy'])->name('equity.destroy');

    // Shop Profile & Thermal Printer Settings
    Route::get('/settings/shop', [ShopSettingController::class, 'index'])->name('settings.shop');
    Route::post('/settings/shop', [ShopSettingController::class, 'update'])->name('settings.shop.update');
});


Route::middleware(['auth'])->group(function () {
    // Module 3: Products & Catalog
    Route::resource('sections', ProductSectionController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('products', ProductController::class);
    Route::get('/products/{product}/barcodes', [ProductController::class, 'printBarcodes'])->name('products.barcodes');
});


Route::middleware(['auth'])->group(function () {
    // Module 4: Suppliers & Purchases
    Route::resource('suppliers', SupplierController::class);
    Route::get('/suppliers/{supplier}/payment', [SupplierPaymentController::class, 'create'])->name('suppliers.payment.create');
    Route::post('/suppliers/{supplier}/payment', [SupplierPaymentController::class, 'store'])->name('suppliers.payment.store');

    
    Route::get('/purchases/{purchase}/view', [PurchaseController::class, 'show'])->name('purchases.view');


    Route::get('/purchases/api/search-suppliers', [PurchaseController::class, 'searchSuppliers'])->name('purchases.api.suppliers');
    Route::get('/purchases/api/search-products', [PurchaseController::class, 'searchProducts'])->name('purchases.api.products');

    Route::resource('purchases', PurchaseController::class);
    
});

Route::middleware(['auth'])->group(function () {
    // Module 5: Customers & Bakir Khata
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/payment', [CustomerPaymentController::class, 'create'])->name('customers.payment.create');
    Route::post('/customers/{customer}/payment', [CustomerPaymentController::class, 'store'])->name('customers.payment.store');

    // POS Counter
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    // Sales Invoices
    Route::resource('sales', SaleController::class);

    // Sales Returns
    Route::resource('returns', SaleReturnController::class);

    // Quotations
    Route::resource('quotations', QuotationController::class);


    Route::get('/pos/api/search-customers', [PosController::class, 'searchCustomers'])->name('pos.api.customers');
    
    Route::post('/pos/api/quick-customer', [PosController::class, 'quickAddCustomer'])->name('pos.api.quick-customer');
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToSale'])->name('quotations.convert');
});

Route::middleware(['auth'])->group(function () {
    // Module 6: Expenses & Other Incomes
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('incomes', OtherIncomeController::class);
});


Route::middleware(['auth'])->group(function () {
    // Module 7: Staff & Payroll
    Route::resource('departments', DepartmentController::class);
    Route::resource('staff', StaffProfileController::class);
    Route::resource('advances', SalaryAdvanceController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('payrolls', PayrollController::class)->only(['index', 'create', 'store', 'show']);
});

// ==========================================
// MODULE 8: BANKING, CASH & MFS ROUTES
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Accounts Master & Statements
    Route::resource('accounts', AccountController::class);
    
    // Internal Fund Transfer
    Route::get('transfers/create', [AccountTransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [AccountTransferController::class, 'store'])->name('transfers.store');
    
    // Cheques Management
    Route::get('cheques', [BankChequeController::class, 'index'])->name('cheques.index');
    Route::get('cheques/create', [BankChequeController::class, 'create'])->name('cheques.create');
    Route::post('cheques', [BankChequeController::class, 'store'])->name('cheques.store');
    Route::patch('cheques/{cheque}/status', [BankChequeController::class, 'updateStatus'])->name('cheques.status');
    Route::delete('cheques/{cheque}', [BankChequeController::class, 'destroy'])->name('cheques.destroy');
    
    // Loans & Hawlat
    Route::resource('loans', LoanController::class);
    Route::post('loans/{loan}/installments', [LoanController::class, 'addInstallment'])->name('loans.installments');
});

// ==========================================
// MODULE 9: REPORTS, AUDITS & Z-REPORTS
// ==========================================
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('balance', [ReportController::class, 'balanceReport'])->name('balance');
    Route::get('due-collection', [ReportController::class, 'dueCollectionReport'])->name('due-collection');
    Route::get('due-paid', [ReportController::class, 'duePaidReport'])->name('due-paid');
    Route::get('sales', [ReportController::class, 'saleReport'])->name('sales');
    Route::get('purchases', [ReportController::class, 'purchaseReport'])->name('purchases');
    Route::get('stock', [ReportController::class, 'productStockReport'])->name('stock');
    Route::get('supplier-purchases', [ReportController::class, 'supplierPurchaseReport'])->name('supplier-purchases');
    Route::get('customer-ledger', [ReportController::class, 'customerLedgerReport'])->name('customer-ledger');

    Route::get('daily-product-profit', [ReportController::class, 'dailyProductProfitReport'])->name('daily-product-profit');
});

// Day-End Z-Report Closings
Route::resource('closings', CashRegisterController::class)->only(['index', 'create', 'store', 'show']);
