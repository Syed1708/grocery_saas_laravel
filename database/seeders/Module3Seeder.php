<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSection;
use App\Models\ProductStock;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Module3Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Sections
        $sec1 = ProductSection::firstOrCreate(
            ['code' => 'SEC-01'],
            ['name' => 'র্যাক ১ - চাল ও ডাল কর্নার', 'description' => 'দোকানের ডানপাশের প্রথম বড় র্যাক', 'status' => 'active']
        );
        $sec2 = ProductSection::firstOrCreate(
            ['code' => 'SEC-02'],
            ['name' => 'র্যাক ২ - ভোজ্যতেল ও মসলাপাতি', 'description' => 'দোকানের মাঝখানের আইল', 'status' => 'active']
        );
        $sec3 = ProductSection::firstOrCreate(
            ['code' => 'SEC-03'],
            ['name' => 'ডিপ ফ্রিজ - ডেইরি ও ফ্রোজেন', 'description' => 'সামনের ফ্রন্ট ফ্রিজ', 'status' => 'active']
        );

        // 2. Categories
        $catRice = Category::firstOrCreate(
            ['slug' => 'rice-pulses'],
            ['name' => 'চাল ও ডাল (Rice & Pulses)', 'status' => 'active']
        );
        $catOil = Category::firstOrCreate(
            ['slug' => 'edible-oil'],
            ['name' => 'ভোজ্যতেল ও ঘি (Edible Oil)', 'status' => 'active']
        );
        $catSpices = Category::firstOrCreate(
            ['slug' => 'spices'],
            ['name' => 'গুঁড়া মসলাপাতি (Spices)', 'status' => 'active']
        );
        $catSoap = Category::firstOrCreate(
            ['slug' => 'toiletries'],
            ['name' => 'সাবান ও প্রসাধন (Toiletries)', 'status' => 'active']
        );

        // 3. Brands
        $brandPran = Brand::firstOrCreate(
            ['name' => 'প্রাণ (PRAN)'],
            ['company_name' => 'PRAN-RFL Group', 'contact_person' => 'মোঃ ফারুক হোসেন (SR)', 'phone' => '01711223344', 'status' => 'active']
        );
        $brandSquare = Brand::firstOrCreate(
            ['name' => 'স্কয়ার (Square)'],
            ['company_name' => 'Square Consumer Products Ltd', 'contact_person' => 'আরিফুল ইসলাম', 'phone' => '01811223344', 'status' => 'active']
        );
        $brandCity = Brand::firstOrCreate(
            ['name' => 'তীর (Teer)'],
            ['company_name' => 'City Group', 'contact_person' => 'কামরুল হাসান', 'phone' => '01911223344', 'status' => 'active']
        );
        $brandUnilever = Brand::firstOrCreate(
            ['name' => 'ইউনিলিভার (Unilever)'],
            ['company_name' => 'Unilever Bangladesh', 'contact_person' => 'সাজিদ রহমান', 'phone' => '01755667788', 'status' => 'active']
        );

        // 4. Units lookup
        $kg = Unit::where('short_code', 'kg')->first() ?? Unit::first();
        $ltr = Unit::where('short_code', 'ltr')->first() ?? $kg;
        $pc = Unit::where('short_code', 'pc')->first() ?? $kg;

        // 5. Products List
        $products = [
            [
                'section_id'      => $sec1->id,
                'category_id'     => $catRice->id,
                'brand_id'        => $brandCity->id,
                'unit_id'         => $kg->id,
                'name'            => 'তীর প্রিমিয়াম মিনিকেট চাল',
                'name_en'         => 'Teer Premium Miniket Rice 1kg',
                'barcode'         => '8941100100012',
                'sku'             => 'RICE-MINIKET-01',
                'purchase_price'  => 68.00,
                'selling_price'   => 76.00,
                'wholesale_price' => 71.00,
                'alert_quantity'  => 25.00,
                'initial_stock'   => 120.00,
            ],
            [
                'section_id'      => $sec2->id,
                'category_id'     => $catOil->id,
                'brand_id'        => $brandCity->id,
                'unit_id'         => $ltr->id,
                'name'            => 'তীর ফর্টিফাইড সয়াবিন তেল ৫ লিটার',
                'name_en'         => 'Teer Fortified Soyabean Oil 5L',
                'barcode'         => '8941100200054',
                'sku'             => 'OIL-TEER-5L',
                'purchase_price'  => 810.00,
                'selling_price'   => 850.00,
                'wholesale_price' => 825.00,
                'alert_quantity'  => 6.00,
                'initial_stock'   => 20.00,
            ],
            [
                'section_id'      => $sec2->id,
                'category_id'     => $catSpices->id,
                'brand_id'        => $brandSquare->id,
                'unit_id'         => $pc->id,
                'name'            => 'রাঁধুনী হলুদ গুঁড়া ২০০ গ্রাম',
                'name_en'         => 'Radhuni Turmeric Powder 200g',
                'barcode'         => '8941100300201',
                'sku'             => 'SPICE-RAD-TUR-200',
                'purchase_price'  => 82.00,
                'selling_price'   => 95.00,
                'wholesale_price' => 88.00,
                'alert_quantity'  => 10.00,
                'initial_stock'   => 45.00,
            ],
            [
                'section_id'      => $sec3->id,
                'category_id'     => $catSoap->id,
                'brand_id'        => $brandUnilever->id,
                'unit_id'         => $pc->id,
                'name'            => 'লাক্স বিউটি সোপ ভেলভেট গ্লো ১০০ গ্রাম',
                'name_en'         => 'Lux Beauty Soap Velvet Glow 100g',
                'barcode'         => '8941100400105',
                'sku'             => 'TOIL-LUX-100G',
                'purchase_price'  => 58.00,
                'selling_price'   => 65.00,
                'wholesale_price' => 60.00,
                'alert_quantity'  => 15.00,
                'initial_stock'   => 60.00,
            ],
        ];

        $branch1 = Branch::first();
        $branch2 = Branch::skip(1)->first();

        foreach ($products as $p) {
            $initialStock = $p['initial_stock'];
            unset($p['initial_stock']);

            $product = Product::firstOrCreate(['barcode' => $p['barcode']], $p);

            // Allocate stock to Branch 1
            if ($branch1) {
                ProductStock::updateOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $branch1->id],
                    ['quantity' => $initialStock]
                );
            }

            // Allocate stock to Branch 2
            if ($branch2) {
                ProductStock::updateOrCreate(
                    ['product_id' => $product->id, 'branch_id' => $branch2->id],
                    ['quantity' => round($initialStock / 2)]
                );
            }
        }
    }
}