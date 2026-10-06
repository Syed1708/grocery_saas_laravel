<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\OtherIncome;
use App\Models\Product;
use App\Models\ProductSection;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class RealTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_main', true)->first() ?? Branch::first();
        $admin = User::first();
        $branchId = $branch?->id;
        $adminId = $admin?->id ?? 1;

        // 1. Units
        $kg   = Unit::firstOrCreate(['short_code' => 'kg'], ['name' => 'কেজি (Kg)', 'allow_decimal' => true, 'status' => 'active']);
        $ltr  = Unit::firstOrCreate(['short_code' => 'ltr'], ['name' => 'লিটার (Liter)', 'allow_decimal' => true, 'status' => 'active']);
        $pcs  = Unit::firstOrCreate(['short_code' => 'pcs'], ['name' => 'পিস (Pcs)', 'allow_decimal' => false, 'status' => 'active']);
        $pack = Unit::firstOrCreate(['short_code' => 'pkt'], ['name' => 'প্যাকেট (Packet)', 'allow_decimal' => false, 'status' => 'active']);

        // 2. Sections & Categories & Brands
        $sec1 = ProductSection::firstOrCreate(['code' => 'SEC-01'], ['name' => 'র্যাক ১ - তেল ও চাল', 'status' => 'active']);
        $sec2 = ProductSection::firstOrCreate(['code' => 'SEC-02'], ['name' => 'র্যাক ২ - মশলা ও টয়লেট্রিজ', 'status' => 'active']);

        $catOil  = Category::firstOrCreate(['slug' => 'edible-oil'], ['name' => 'ভোজ্যতেল', 'status' => 'active']);
        $catRice = Category::firstOrCreate(['slug' => 'rice-flour'], ['name' => 'চাল ও আটা', 'status' => 'active']);
        $catSoap = Category::firstOrCreate(['slug' => 'toiletries'], ['name' => 'টয়লেট্রিজ ও সাবান', 'status' => 'active']);

        $brandRup  = Brand::firstOrCreate(['name' => 'রূপচাঁদা (Rupchanda)'], ['company_name' => 'বাংলাদেশ এডিবল অয়েল লিঃ', 'phone' => '01711223344', 'status' => 'active']);
        $brandTeer = Brand::firstOrCreate(['name' => 'তীর (Teer)'], ['company_name' => 'সিটি গ্রুপ', 'phone' => '01722334455', 'status' => 'active']);
        $brandPran = Brand::firstOrCreate(['name' => 'প্রাণ (PRAN)'], ['company_name' => 'প্রাণ-আরএফএল গ্রুপ', 'phone' => '01733445566', 'status' => 'active']);

        // 3. Products
        $p1 = Product::firstOrCreate(['barcode' => '89411001001'], [
            'name' => 'রূপচাঁদা সয়াবিন তেল ৫ লিটার',
            'name_en' => 'Rupchanda Soyabean Oil 5L',
            'sku' => 'PRD-RUP-5L',
            'section_id' => $sec1->id,
            'category_id' => $catOil->id,
            'brand_id' => $brandRup->id,
            'unit_id' => $ltr->id,
            'purchase_price' => 890.00,
            'selling_price' => 950.00,
            'alert_quantity' => 5,
            'status' => 'active',
        ]);

        $p2 = Product::firstOrCreate(['barcode' => '89411001002'], [
            'name' => 'তীর প্রিমিয়াম আটা ২ কেজি',
            'name_en' => 'Teer Premium Atta 2kg',
            'sku' => 'PRD-TEER-2KG',
            'section_id' => $sec1->id,
            'category_id' => $catRice->id,
            'brand_id' => $brandTeer->id,
            'unit_id' => $pack->id,
            'purchase_price' => 115.00,
            'selling_price' => 130.00,
            'alert_quantity' => 10,
            'status' => 'active',
        ]);

        $p3 = Product::firstOrCreate(['barcode' => '89411001003'], [
            'name' => 'প্রাণ চিনিগুঁড়া চাল ১ কেজি',
            'name_en' => 'PRAN Chinigura Rice 1kg',
            'sku' => 'PRD-PRAN-1KG',
            'section_id' => $sec1->id,
            'category_id' => $catRice->id,
            'brand_id' => $brandPran->id,
            'unit_id' => $pack->id,
            'purchase_price' => 140.00,
            'selling_price' => 160.00,
            'alert_quantity' => 8,
            'status' => 'active',
        ]);

        // Stock allocations
        ProductStock::updateOrCreate(['product_id' => $p1->id, 'branch_id' => $branchId], ['quantity' => 30]);
        ProductStock::updateOrCreate(['product_id' => $p2->id, 'branch_id' => $branchId], ['quantity' => 50]);
        ProductStock::updateOrCreate(['product_id' => $p3->id, 'branch_id' => $branchId], ['quantity' => 40]);

        // 4. Suppliers & Purchase Invoices
        $sup1 = Supplier::firstOrCreate(['phone' => '01819001122'], [
            'name' => 'মেসার্স হাজী এন্টারপ্রাইজ (সিটি গ্রুপ ডিলার)',
            'company_name' => 'সিটি গ্রুপ ডিস্ট্রিবিউশন',
            'address' => 'কাওরান বাজার আড়ৎ, ঢাকা',
            'opening_due' => 15000.00,
            'current_due' => 25000.00,
            'status' => 'active',
        ]);

        Purchase::firstOrCreate(['chalan_no' => 'CHAL-261001-01'], [
            'branch_id' => $branchId,
            'supplier_id' => $sup1->id,
            'purchase_date' => now()->toDateString(),
            'subtotal' => 20000.00,
            'total_quantity' => 100,
            'discount' => 500.00,
            'transport_cost' => 300.00,
            'grand_total' => 19800.00,
            'paid_amount' => 10000.00,
            'due_amount' => 9800.00,
            'payment_method' => 'cash',
            'payment_status' => 'partial',
        ]);

        // 5. Customers & Sales
        $cust1 = Customer::firstOrCreate(['phone' => '01712998877'], [
            'name' => 'জনাব রফিকুল ইসলাম',
            'address' => 'বাসা নং ১২, ধানমন্ডি ৪/এ, ঢাকা',
            'credit_limit' => 20000.00,
            'opening_due' => 2500.00,
            'current_due' => 4500.00,
            'status' => 'active',
        ]);

        $sale1 = Sale::firstOrCreate(['invoice_no' => 'INV-261005-001'], [
            'branch_id' => $branchId,
            'customer_id' => $cust1->id,
            'user_id' => $adminId,
            'sale_date' => now()->toDateString(),
            'subtotal' => 2900.00,
            'discount' => 100.00,
            'grand_total' => 2800.00,
            'paid_amount' => 1500.00,
            'due_amount' => 1300.00,
            'payment_method' => 'mixed',
            'payment_status' => 'partial',
            'notes' => 'Cash 1000 + bKash 500',
        ]);

        SaleItem::firstOrCreate(['sale_id' => $sale1->id, 'product_id' => $p1->id], [
            'unit_id' => $ltr->id,
            'quantity' => 2,
            'unit_price' => 950.00,
            'purchase_cost' => 890.00,
            'line_total' => 1900.00,
        ]);

        SaleItem::firstOrCreate(['sale_id' => $sale1->id, 'product_id' => $p2->id], [
            'unit_id' => $pack->id,
            'quantity' => 5,
            'unit_price' => 130.00,
            'purchase_cost' => 115.00,
            'line_total' => 650.00,
        ]);

        // 6. Due Collection & Paid
        CustomerPayment::firstOrCreate(['receipt_no' => 'REC-261005-01'], [
            'branch_id' => $branchId,
            'customer_id' => $cust1->id,
            'payment_date' => now()->toDateString(),
            'amount' => 1000.00,
            'payment_method' => 'cash',
            'reference' => 'Cash Counter Pay',
        ]);

        SupplierPayment::firstOrCreate(['voucher_no' => 'PAY-261005-01'], [
            'branch_id' => $branchId,
            'supplier_id' => $sup1->id,
            'payment_date' => now()->toDateString(),
            'amount' => 5000.00,
            'payment_method' => 'cash',
            'reference' => 'Haji Enterprise Payment',
        ]);

        // 7. Expenses & Other Income
        $expCat1 = ExpenseCategory::firstOrCreate(['code' => 'EXP-RENT'], ['name' => 'দোকান ভাড়া', 'status' => 'active']);
        $expCat2 = ExpenseCategory::firstOrCreate(['code' => 'EXP-ELEC'], ['name' => 'বিদ্যুৎ বিল', 'status' => 'active']);
        $expCat3 = ExpenseCategory::firstOrCreate(['code' => 'EXP-TEA'], ['name' => 'স্টাফ নাস্তা ও চা', 'status' => 'active']);

        Expense::firstOrCreate(['voucher_no' => 'EXP-261005-01'], [
            'branch_id' => $branchId,
            'category_id' => $expCat3->id,
            'user_id' => $adminId,
            'expense_date' => now()->toDateString(),
            'amount' => 350.00,
            'payment_method' => 'cash',
            'note' => 'কাউন্টার স্টাফ দুপুরের নাস্তা',
        ]);

        OtherIncome::firstOrCreate(['receipt_no' => 'INC-261005-01'], [
            'branch_id' => $branchId,
            'user_id' => $adminId,
            'income_source' => 'খালি তেলের ড্রাম ও কার্টুন বিক্রি',
            'receipt_no' => 'INC-261005-01',
            'income_date' => now()->toDateString(),
            'amount' => 600.00,
            'payment_method' => 'cash',
            'note' => '১০টি খালি কার্টুন বিক্রি',
        ]);
    }
}