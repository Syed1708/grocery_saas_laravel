<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class Module8Seeder extends Seeder
{
    public function run(): void
    {
        $mainBranch = Branch::where('is_main', true)->first() ?? Branch::first();
        $branchId = $mainBranch?->id;

        // 1. Counter Cash Drawer
        Account::firstOrCreate(
            ['name' => 'কাউন্টার ক্যাশ ড্রয়ার (Counter Cash)', 'branch_id' => $branchId],
            [
                'type'            => 'cash',
                'opening_balance' => 5000.00,
                'current_balance' => 5000.00,
                'status'          => 'active',
                'notes'           => 'প্রধান কাউন্টার ক্যাশ ড্রয়ার',
            ]
        );

        // 2. Islami Bank Account
        Account::firstOrCreate(
            ['account_number' => '205012345678901', 'branch_id' => $branchId],
            [
                'name'            => 'ইসলামী ব্যাংক বাংলাদেশ (চলতি হিসাব)',
                'type'            => 'bank',
                'bank_name'       => 'Islami Bank Bangladesh Ltd',
                'branch_name'     => 'কাওরান বাজার শাখা',
                'opening_balance' => 50000.00,
                'current_balance' => 50000.00,
                'status'          => 'active',
            ]
        );

        // 3. bKash Merchant Wallet
        Account::firstOrCreate(
            ['account_number' => '01700000000', 'branch_id' => $branchId],
            [
                'name'            => 'বিকাশ মার্চেন্ট ওয়ালেট (bKash Merchant)',
                'type'            => 'bkash',
                'opening_balance' => 2000.00,
                'current_balance' => 2000.00,
                'status'          => 'active',
            ]
        );
    }
}