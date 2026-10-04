<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\SalaryAdvance;
use App\Models\StaffProfile;
use Illuminate\Database\Seeder;

class Module7Seeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_main', true)->first() ?? Branch::first();

        // 1. Seed Store Departments
        $deptSales = Department::firstOrCreate(
            ['code' => 'DEPT-SALES'],
            ['name' => 'ক্যাশ কাউন্টার ও বিক্রয় (Sales Counter)', 'description' => 'ক্যাশ কাউন্টার ও সেলসম্যান', 'status' => 'active']
        );
        $deptStock = Department::firstOrCreate(
            ['code' => 'DEPT-STOCK'],
            ['name' => 'গোডাউন ও ইনভেন্টরি (Godown / Stock)', 'description' => 'মালামাল আনলোড ও সাজানো', 'status' => 'active']
        );
        $deptDelivery = Department::firstOrCreate(
            ['code' => 'DEPT-DELIV'],
            ['name' => 'হোম ডেলিভারি (Delivery Team)', 'description' => 'কাস্টমারদের বাসায় মালামাল ডেলিভারি', 'status' => 'active']
        );

        // 2. Seed Staff Members
        if ($branch) {
            $staff1 = StaffProfile::firstOrCreate(
                ['phone' => '01811224455'],
                [
                    'branch_id'     => $branch->id,
                    'department_id' => $deptSales->id,
                    'name'          => 'সুমন আহমেদ',
                    'nid_number'    => '1995123456789',
                    'designation'   => 'প্রধান ক্যাশিয়ার (Senior Cashier)',
                    'joining_date'  => now()->subMonths(12),
                    'basic_salary'  => 18000.00,
                    'status'        => 'active',
                ]
            );

            $staff2 = StaffProfile::firstOrCreate(
                ['phone' => '01711224466'],
                [
                    'branch_id'     => $branch->id,
                    'department_id' => $deptStock->id,
                    'name'          => 'মোঃ রফিক মিয়া',
                    'nid_number'    => '1998987654321',
                    'designation'   => 'গোডাউন ইনচার্জ',
                    'joining_date'  => now()->subMonths(6),
                    'basic_salary'  => 14000.00,
                    'status'        => 'active',
                ]
            );

            // 3. Seed Sample Mid-Month Advance Salary (হাওলাত)
            SalaryAdvance::firstOrCreate(
                ['staff_id' => $staff1->id, 'advance_date' => now()->subDays(10)],
                [
                    'branch_id'      => $branch->id,
                    'amount'         => 3000.00,
                    'payment_method' => 'cash',
                    'purpose'        => 'পারিবারিক জরুরি খরচ বাবদ অগ্রিম গ্রহণ',
                    'is_deducted'    => false,
                ]
            );
        }
    }
}