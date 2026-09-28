<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use HasinHayder\Tyro\Models\Privilege;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Module1Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            'super-admin' => 'Super Admin',
            'owner'       => 'Shop Owner',
            'manager'     => 'Store Manager',
            'cashier'     => 'Cashier',
        ];

        $roleModels = [];
        foreach ($roles as $slug => $name) {
            $roleModels[$slug] = Role::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        // 2. Privileges
        $privileges = [
            'pos.sell'         => 'Make Sales at POS Terminal',
            'pos.due'          => 'Collect Due from Customers',
            'products.manage'  => 'Create and Manage Products',
            'purchases.manage' => 'Manage Supplier Purchases',
            'expenses.manage'  => 'Record and Review Expenses',
            'accounts.manage'  => 'Manage Cash, Bank and MFS Accounts',
            'reports.view'     => 'View Financial and Sales Reports',
            'settings.manage'  => 'Configure Shop Settings and Units',
        ];

        foreach ($privileges as $slug => $name) {
            Privilege::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        // 3. Attach Privileges to Roles
        $allPrivileges = Privilege::all();
        $roleModels['owner']->privileges()->sync($allPrivileges->pluck('id'));

        $cashierPrivs = Privilege::whereIn('slug', ['pos.sell', 'pos.due'])->pluck('id');
        $roleModels['cashier']->privileges()->sync($cashierPrivs);

        // 4. Create Platform Super Admin (No tenant_id)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@grocery.test'],
            [
                'name'      => 'Platform Admin',
                'phone'     => '01700000000',
                'password'  => Hash::make('password123'),
                'tenant_id' => null,
            ]
        );
        // Pass the Role Model instance
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole($roleModels['super-admin']);
        }

        // 5. Create Sample Bangladeshi Grocery Store
        $shop = Tenant::firstOrCreate(
            ['slug' => 'madina-general-store'],
            [
                'name'            => 'মেসার্স মদিনা জেনারেল স্টোর',
                'owner_name'      => 'মোঃ রফিকুল ইসলাম',
                'phone'           => '01712345678',
                'email'           => 'rafiq@madina.test',
                'address'         => 'দোকান নং ১২, কাওরান বাজার, ঢাকা-১২১৫',
                'trade_license'   => 'TRAD/DNCC/2026/0491',
                'currency'        => 'BDT',
                'currency_symbol' => '৳',
                'status'          => 'active',
                'trial_ends_at'   => now()->addDays(30),
                'settings'        => [
                    'vat_percent'    => 0,
                    'thermal_width'  => '80mm',
                    'bangla_receipt' => true,
                    'invoice_footer' => 'ধন্যবাদ আবার আসবেন! পণ্য ক্রয়ের ৭ দিনের মধ্যে ফেরত গ্রহণযোগ্য।'
                ]
            ]
        );

        // 6. Create Shop Owner & Assign Role Model
        $owner = User::firstOrCreate(
            ['email' => 'owner@madina.test'],
            [
                'tenant_id' => $shop->id,
                'name'      => 'রফিকুল ইসলাম (মালিক)',
                'phone'     => '01712345678',
                'password'  => Hash::make('password123'),
            ]
        );
        if (!$owner->hasRole('owner')) {
            $owner->assignRole($roleModels['owner']);
        }

        // 7. Create Cashier & Assign Role Model
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@madina.test'],
            [
                'tenant_id' => $shop->id,
                'name'      => 'সুমন আহমেদ (ক্যাশিয়ার)',
                'phone'     => '01812345678',
                'password'  => Hash::make('password123'),
            ]
        );
        if (!$cashier->hasRole('cashier')) {
            $cashier->assignRole($roleModels['cashier']);
        }
    }
}
