<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class Module5Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Regular Local Grocery Customers with Due balances
        Customer::firstOrCreate(
            ['phone' => '01712001122'],
            [
                'name'         => 'হাজী মোঃ সামসুল আলম',
                'email'        => 'samsul@customer.test',
                'address'      => 'বাড়ি ২৫, লেন ৪, কাওরান বাজার, ঢাকা',
                'credit_limit' => 15000.00,
                'opening_due'  => 3200.00,
                'current_due'  => 3200.00,
                'status'       => 'active',
            ]
        );

        Customer::firstOrCreate(
            ['phone' => '01812003344'],
            [
                'name'         => 'মাস্টার মোফাজ্জল হোসেন',
                'email'        => 'mofazzal@customer.test',
                'address'      => 'ফ্ল্যাট ৩বি, ধানমন্ডি আ/এ, ঢাকা',
                'credit_limit' => 20000.00,
                'opening_due'  => 0.00,
                'current_due'  => 0.00,
                'status'       => 'active',
            ]
        );

        Customer::firstOrCreate(
            ['phone' => '01912005566'],
            [
                'name'         => 'ডাঃ ফাহমিদা আক্তার',
                'email'        => 'fahmina@customer.test',
                'address'      => 'গ্রিন রোড, ঢাকা',
                'credit_limit' => 12000.00,
                'opening_due'  => 1850.00,
                'current_due'  => 1850.00,
                'status'       => 'active',
            ]
        );
    }
}