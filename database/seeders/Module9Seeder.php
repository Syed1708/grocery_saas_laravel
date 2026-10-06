<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Seeder;

class Module9Seeder extends Seeder
{
    public function run(): void
    {
        $mainBranch = Branch::where('is_main', true)->first() ?? Branch::first();
        $adminUser = User::first();

        CashRegister::firstOrCreate(
            ['closing_date' => now()->subDay()->toDateString(), 'branch_id' => $mainBranch?->id],
            [
                'user_id'            => $adminUser?->id ?? 1,
                'opening_time'       => '08:00:00',
                'closing_time'       => '22:30:00',
                'opening_cash'       => 5000.00,
                'cash_sales'         => 18500.00,
                'cash_due_collected' => 2000.00,
                'cash_other_income'  => 300.00,
                'cash_expenses'      => 1200.00,
                'cash_supplier_paid' => 5000.00,
                'cash_refunds'       => 0.00,
                'expected_cash'      => 19600.00,
                'counted_cash'       => 19600.00,
                'difference'         => 0.00,
                'total_invoices'     => 42,
                'notes'              => 'First demo day-end closing successful with zero variance.',
                'status'             => 'closed',
            ]
        );
    }
}