<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            Module1Seeder::class, // Roles, Privileges, Branches, Users, License
            Module2Seeder::class, // Units, Assets, Equity, Shop Settings
            Module3Seeder::class, // Product Sections, Categories, Brands, Products, Stocks
            Module4Seeder::class, // Suppliers, Purchases, Supplier Payments
            Module5Seeder::class, // Customers, Sales, Customer Payments, Quotations
            Module6Seeder::class, // Expense Categories, Expenses, Other Incomes
            Module7Seeder::class, // Departments, Staff Profiles, Payroll
            Module8Seeder::class, // Accounts, Cash Drawer, Bank, bKash, Loans
            Module9Seeder::class, // 👈 Added
            RealTestDataSeeder::class, // 👈 Added

        ]);
    }
}
