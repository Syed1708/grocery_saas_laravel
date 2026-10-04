<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\OtherIncome;
use App\Models\User;
use Illuminate\Database\Seeder;

class Module6Seeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_main', true)->first() ?? Branch::first();
        $admin = User::first();

        // 1. Seed Common Bangladeshi Grocery Expense Categories
        $catRent = ExpenseCategory::firstOrCreate(
            ['code' => 'EXP-RENT'],
            ['name' => 'দোকান ভাড়া (Shop Rent)', 'description' => 'মাসিক দোকান ভাড়া', 'status' => 'active']
        );

        $catElec = ExpenseCategory::firstOrCreate(
            ['code' => 'EXP-ELEC'],
            ['name' => 'বিদ্যুৎ বিল (DESCO / DPDC Bill)', 'description' => 'মাসিক বিদ্যুৎ বিল', 'status' => 'active']
        );

        $catFood = ExpenseCategory::firstOrCreate(
            ['code' => 'EXP-FOOD'],
            ['name' => 'স্টাফ খাবার ও নাস্তা (Staff Refreshment)', 'description' => 'দৈনিক দুপুরের খাবার ও চা-নাস্তা', 'status' => 'active']
        );

        $catTrans = ExpenseCategory::firstOrCreate(
            ['code' => 'EXP-TRANS'],
            ['name' => 'গাড়ি ভাড়া ও কুলি খরচ (Freight & Transport)', 'description' => 'মালামাল আনা-নেওয়ার ভ্যান ও কুলি খরচ', 'status' => 'active']
        );

        $catComm = ExpenseCategory::firstOrCreate(
            ['code' => 'EXP-COMM'],
            ['name' => 'বাজার সমিতি ও অন্যান্য চাঁদা (Market Association)', 'description' => 'বাজার পরিচ্ছন্নতা ও নিরাপত্তা চাঁদা', 'status' => 'active']
        );

        // 2. Seed Sample Daily Expenses
        if ($branch && $admin) {
            Expense::firstOrCreate(
                ['voucher_no' => 'EXP-2026-001'],
                [
                    'branch_id'      => $branch->id,
                    'category_id'    => $catFood->id,
                    'user_id'        => $admin->id,
                    'expense_date'   => now()->subDays(1),
                    'amount'         => 350.00,
                    'payment_method' => 'cash',
                    'reference'      => 'VOUCHER-01',
                    'note'           => 'স্টাফদের দুপুরের খাবার ও বিকালের চা-বিস্কুট',
                ]
            );

            Expense::firstOrCreate(
                ['voucher_no' => 'EXP-2026-002'],
                [
                    'branch_id'      => $branch->id,
                    'category_id'    => $catTrans->id,
                    'user_id'        => $admin->id,
                    'expense_date'   => now()->subDays(2),
                    'amount'         => 600.00,
                    'payment_method' => 'cash',
                    'reference'      => 'VAN-FARE',
                    'note'           => 'বাদামতলী ঘাট থেকে মালামাল আনার ভ্যান ও কুলি মজুরি',
                ]
            );

            // 3. Seed Sample Other / Non-Sale Incomes
            OtherIncome::firstOrCreate(
                ['receipt_no' => 'INC-2026-001'],
                [
                    'branch_id'      => $branch->id,
                    'user_id'        => $admin->id,
                    'income_source'  => 'খালি তেলের ড্রাম ও কার্টুন বিক্রি',
                    'income_date'    => now()->subDays(1),
                    'amount'         => 1200.00,
                    'payment_method' => 'cash',
                    'note'           => '৪০টি তেলের কার্টুন ও ৩টি ড্রাম ভাঙারি বিক্রি',
                ]
            );

            OtherIncome::firstOrCreate(
                ['receipt_no' => 'INC-2026-002'],
                [
                    'branch_id'      => $branch->id,
                    'user_id'        => $admin->id,
                    'income_source'  => 'বিকাশ ও নগদ ক্যাশ-আউট কমিশন',
                    'income_date'    => now()->subDays(3),
                    'amount'         => 850.00,
                    'payment_method' => 'bkash',
                    'note'           => 'সাপ্তাহিক এমএফএস মার্চেন্ট কমিশন লাভ',
                ]
            );
        }
    }
}