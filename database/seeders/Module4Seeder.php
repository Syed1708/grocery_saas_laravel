<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class Module4Seeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_main', true)->first() ?? Branch::first();

        // 1. Seed Realistic Bangladeshi Grocery Suppliers
        $sup1 = Supplier::firstOrCreate(
            ['phone' => '01711998877'],
            [
                'name'           => 'মেসার্স হাজী বাবলু এন্টারপ্রাইজ',
                'company_name'   => 'সিটি গ্রুপ ডিলার (তীর তেল ও আটা)',
                'email'          => 'city.dealer@supplier.test',
                'address'        => 'বাদামতলী ঘাট, ঢাকা',
                'contact_person' => 'মোঃ বাবুল হোসেন',
                'opening_due'    => 15000.00,
                'current_due'    => 15000.00,
                'status'         => 'active',
            ]
        );

        $sup2 = Supplier::firstOrCreate(
            ['phone' => '01811887766'],
            [
                'name'           => 'মেসার্স সততা ট্রেডার্স',
                'company_name'   => 'স্কয়ার কনজিউমার প্রোডাক্টস এজেন্সী',
                'email'          => 'square.agency@supplier.test',
                'address'        => 'কাওরান বাজার আড়ৎ, ঢাকা',
                'contact_person' => 'মোঃ জসিম উদ্দিন (SR)',
                'opening_due'    => 0.00,
                'current_due'    => 8500.00,
                'status'         => 'active',
            ]
        );

        // 2. Seed a Sample Procurement Chalan
        $product = Product::first();

        if ($product && $branch) {
            $chalan = Purchase::firstOrCreate(
                ['chalan_no' => 'CHAL-2026-001'],
                [
                    'branch_id'      => $branch->id,
                    'supplier_id'    => $sup2->id,
                    'purchase_date'  => now()->subDays(3),
                    'subtotal'       => 18500.00,
                    'discount'       => 500.00,
                    'transport_cost' => 500.00,
                    'grand_total'    => 18500.00,
                    'paid_amount'    => 10000.00,
                    'due_amount'     => 8500.00,
                    'payment_method' => 'cash',
                    'payment_status' => 'partial',
                    'notes'          => 'সাপ্তাহিক রুটিন চালান ডেলিভারি',
                ]
            );

            PurchaseItem::firstOrCreate(
                ['purchase_id' => $chalan->id, 'product_id' => $product->id],
                [
                    'unit_id'    => $product->unit_id,
                    'quantity'   => 50.00,
                    'unit_cost'  => $product->purchase_price,
                    'line_total' => (50.00 * $product->purchase_price),
                ]
            );
        }
    }
}