<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Auth;
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

