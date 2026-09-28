<?php

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
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


// Group 1: General Protected Area (Any logged-in user with active tenant)
Route::middleware(['auth', \App\Http\Middleware\IdentifyTenant::class])->group(function () {

    // Test 1: Info Screen showing active user, shop, roles and privileges
    Route::get('/test/whoami', function () {
        $user = Auth::user();
        return response()->json([
            'user_id'         => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'shop_id'         => $user->tenant_id,
            'shop_name'       => $user->tenant?->name ?? 'Platform Super Admin',
            'roles'           => $user->roles->pluck('slug'),
            'all_privileges'  => $user->allPrivileges()->pluck('slug'),
        ]);
    });

    // Test 2: Cashier Allowed Route (Privilege: pos.sell)
    Route::middleware('privilege:pos.sell')->get('/test/pos-screen', function () {
        return "✅ ACCESS GRANTED: You have the 'pos.sell' privilege. Cashier and Owner can access this!";
    });

    // Test 3: Owner-Only Route (Role: owner)
    Route::middleware('role:owner')->get('/test/owner-reports', function () {
        return "✅ ACCESS GRANTED: You are a Shop Owner! Cashiers are blocked from here.";
    });

    // Test 4: Create a New Cashier Dynamically on the current shop
    Route::get('/test/create-cashier', function () {
        $currentUser = Auth::user();

        if (!$currentUser->hasRole('owner')) {
            abort(403, 'Only store owners can create new staff.');
        }

        $cashierRole = Role::where('slug', 'cashier')->first();

        // Create new staff linked directly to THIS shop
        $newCashier = User::firstOrCreate(
            ['email' => 'staff2@madina.test'],
            [
                'tenant_id' => $currentUser->tenant_id,
                'name'      => 'কামাল হোসেন (নতুন ক্যাশিয়ার)',
                'phone'     => '01999999999',
                'password'  => bcrypt('password123'),
            ]
        );

        if (!$newCashier->hasRole('cashier')) {
            $newCashier->assignRole($cashierRole);
        }

        return response()->json([
            'message' => 'New Cashier Created Successfully!',
            'cashier' => [
                'name'    => $newCashier->name,
                'email'   => $newCashier->email,
                'shop'    => $newCashier->tenant->name,
                'roles'   => $newCashier->roles->pluck('slug'),
            ]
        ]);
    });
});

