<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\License;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use HasinHayder\Tyro\Models\Privilege;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Module1Seeder extends Seeder
{
    public function run(): void
    {
        // ========================================================
        // 1. ROLES DEFINITION (4 Core Roles)
        // ========================================================
        $roles = [
            'super-admin' => 'Platform Super Admin (সফটওয়্যার ভেন্ডর / ফুল অ্যাক্সেস)',
            'admin'       => 'Business Admin / Store Owner (দোকানের মালিক)',
            'manager'     => 'Branch Manager (শাখা ম্যানেজার)',
            'cashier'     => 'POS Cashier (কাউন্টার ক্যাশিয়ার)',
        ];

        $roleModels = [];
        foreach ($roles as $slug => $name) {
            $roleModels[$slug] = Role::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        // ========================================================
        // 2. PRIVILEGES DEFINITION
        // ========================================================
               $privileges = [
            // Branches
            'branches.manage'  => 'Manage Store Branches & Outlets',
            'branches.switch'  => 'Switch Between Active Branches',

            // POS & Sales
            'pos.access'       => 'Access POS Counter Screen',
            'pos.sell'         => 'Complete Sales & Issue Invoices',
            'pos.discount'     => 'Apply Manual Discount at POS',
            'pos.due'          => 'Collect Due from Customers (বাকি আদায়)',
            'sales.view'       => 'View Sales List & Invoices',
            'sales.return'     => 'Process Return of Sold Items',

            // Products & Stock
            'products.view'    => 'View Products List',
            'products.manage'  => 'Create and Manage Products',

            // Purchases & Suppliers
            'purchases.manage' => 'Manage Supplier Stock and Purchases',

            // Accounts & Expenses
            'expenses.manage'  => 'Record and Review Expenses',
            'accounts.manage'  => 'Manage Cash, Bank and MFS Accounts',

            // Staff & Payroll
            'staff.manage'     => 'Manage Staff Profiles & Departments',
            'payroll.manage'   => 'Manage Monthly Payroll & Advances',

            // Reports & Settings
            'reports.view'     => 'View Financial and Sales Reports',
            'settings.manage'  => 'Configure Store Settings and Units',
            'users.manage'     => 'Manage Staff Accounts and Roles',
        ];


        foreach ($privileges as $slug => $name) {
            Privilege::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        // ========================================================
        // 3. ASSIGN PRIVILEGES TO ROLES
        // ========================================================
        $allPrivileges = Privilege::all();

        // Super Admin & Admin get ALL privileges
        $roleModels['super-admin']->privileges()->sync($allPrivileges->pluck('id'));
        $roleModels['admin']->privileges()->sync($allPrivileges->pluck('id'));

        // Manager gets branch operations privileges
        // Manager gets full branch, catalog, purchase, account, staff & report operations
        $managerPrivs = Privilege::whereIn('slug', [
            'branches.manage', 'branches.switch',
            'pos.access', 'pos.sell', 'pos.discount', 'pos.due',
            'sales.view', 'sales.return',
            'products.view', 'products.manage',
            'purchases.manage',
            'expenses.manage',
            'accounts.manage',
            'staff.manage',
            'payroll.manage',
            'reports.view',
        ])->pluck('id');
        $roleModels['manager']->privileges()->sync($managerPrivs);

        // Cashier gets POS counter and due collection privileges only
        $cashierPrivs = Privilege::whereIn('slug', [
            'pos.access', 'pos.sell', 'pos.due', 'sales.view'
        ])->pluck('id');
        $roleModels['cashier']->privileges()->sync($cashierPrivs);

        // ========================================================
        // 4. BRANCHES SETUP
        // ========================================================
        $mainBranch = Branch::firstOrCreate(
            ['code' => 'BR-01'],
            [
                'name'    => 'মেসার্স মদিনা জেনারেল স্টোর (কাওরান বাজার মেইন শাখা)',
                'phone'   => '01711111111',
                'email'   => 'kawran@madina.test',
                'address' => 'দোকান নং ১২, কাওরান বাজার, ঢাকা-১২১৫',
                'is_main' => true,
                'status'  => 'active',
            ]
        );

        $branch2 = Branch::firstOrCreate(
            ['code' => 'BR-02'],
            [
                'name'    => 'মেসার্স মদিনা জেনারেল স্টোর (ধানমন্ডি শাখা)',
                'phone'   => '01722222222',
                'email'   => 'dhanmondi@madina.test',
                'address' => 'রোড ৪/এ, সাত মসজিদ রোড, ধানমন্ডি, ঢাকা',
                'is_main' => false,
                'status'  => 'active',
            ]
        );

        // ========================================================
        // 5. TEST USERS FOR ALL 4 ROLES
        // ========================================================

        // User 1: Super Admin (Platform / Software Vendor Admin)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@grocery.test'],
            [
                'name'      => 'Super Admin',
                'phone'     => '01700000000',
                'password'  => Hash::make('password123'),
                'branch_id' => null, // Oversees all
            ]
        );
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole($roleModels['super-admin']);
        }

        // User 2: Admin (Store Owner / Business Admin)
        $admin = User::firstOrCreate(
            ['email' => 'admin@madina.test'],
            [
                'name'      => 'মোঃ রফিকুল ইসলাম (মালিক)',
                'phone'     => '01711111111',
                'password'  => Hash::make('password123'),
                'branch_id' => null, // Business Owner oversees all branches
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($roleModels['admin']);
        }

        // User 3: Branch Manager (Assigned to Main Branch)
        $manager = User::firstOrCreate(
            ['email' => 'manager@madina.test'],
            [
                'name'      => 'তারেক মাহমুদ (ম্যানেজার)',
                'phone'     => '01733333333',
                'password'  => Hash::make('password123'),
                'branch_id' => $mainBranch->id,
            ]
        );
        if (!$manager->hasRole('manager')) {
            $manager->assignRole($roleModels['manager']);
        }

        // User 4: Cashier (Assigned strictly to Main Branch)
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@madina.test'],
            [
                'name'      => 'সুমন আহমেদ (ক্যাশিয়ার)',
                'phone'     => '01811111111',
                'password'  => Hash::make('password123'),
                'branch_id' => $mainBranch->id, // Counter 1
            ]
        );
        if (!$cashier->hasRole('cashier')) {
            $cashier->assignRole($roleModels['cashier']);
        }

        // ========================================================
        // 6. ACTIVE MONTHLY LICENSE (৳1,500/Month)
        // ========================================================
        License::firstOrCreate(
            ['license_key' => 'POS-BD-MADINA-2026'],
            [
                'client_name'       => 'মেসার্স মদিনা জেনারেল স্টোর',
                'client_phone'      => '01711111111',
                'monthly_fee'       => 1500.00,
                'expires_at'        => now()->addDays(30),
                'grace_period_days' => 2,
                'bkash_number'      => '01711223344',
                'nagad_number'      => '01811223344',
                'support_phone'     => '01911223344',
                'support_whatsapp'  => '8801911223344',
                'status'            => 'active',
            ]
        );
    }
}