<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CompanyAsset;
use App\Models\OwnerTransaction;
use App\Models\ShopSetting;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Module2Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Base & Fractional Bangladeshi Grocery Units
        $kg = Unit::firstOrCreate(
            ['short_code' => 'kg'],
            ['name' => 'কেজি (Kilogram)', 'allow_decimal' => true, 'status' => 'active']
        );

        $ltr = Unit::firstOrCreate(
            ['short_code' => 'ltr'],
            ['name' => 'লিটার (Liter)', 'allow_decimal' => true, 'status' => 'active']
        );

        $pc = Unit::firstOrCreate(
            ['short_code' => 'pc'],
            ['name' => 'পিস (Piece)', 'allow_decimal' => false, 'status' => 'active']
        );

        // Sub-units with conversion rates
        Unit::firstOrCreate(
            ['short_code' => 'gm'],
            [
                'name'            => 'গ্রাম (Gram)',
                'allow_decimal'   => true,
                'base_unit_id'    => $kg->id,
                'conversion_rate' => 0.001, // 1 gm = 0.001 kg
                'status'          => 'active'
            ]
        );

        Unit::firstOrCreate(
            ['short_code' => 'sack'],
            [
                'name'            => 'বস্তা ৫০ কেজি (Sack 50kg)',
                'allow_decimal'   => false,
                'base_unit_id'    => $kg->id,
                'conversion_rate' => 50.00, // 1 sack = 50 kg
                'status'          => 'active'
            ]
        );

        Unit::firstOrCreate(
            ['short_code' => 'ml'],
            [
                'name'            => 'মিলি (Milliliter)',
                'allow_decimal'   => true,
                'base_unit_id'    => $ltr->id,
                'conversion_rate' => 0.001, // 1 ml = 0.001 ltr
                'status'          => 'active'
            ]
        );

        Unit::firstOrCreate(
            ['short_code' => 'dozen'],
            [
                'name'            => 'ডজন (Dozen)',
                'allow_decimal'   => false,
                'base_unit_id'    => $pc->id,
                'conversion_rate' => 12.00, // 1 dozen = 12 pcs
                'status'          => 'active'
            ]
        );

        // 2. Seed Sample Assets for Main Branch
        $mainBranch = Branch::where('is_main', true)->first();

        CompanyAsset::firstOrCreate(
            ['asset_code' => 'AST-001'],
            [
                'branch_id'     => $mainBranch?->id,
                'name'          => 'ওয়ালটন ৩০০ লিটার ডিপ ফ্রিজ (আইসক্রিম ও ফ্রোজেন)',
                'serial_number' => 'WDF-300L-2026',
                'purchase_date' => now()->subMonths(6),
                'purchase_cost' => 52000.00,
                'current_value' => 47000.00,
                'condition'     => 'good',
                'notes'         => '১০ বছরের কম্প্রেসার ওয়ারেন্টি বিদ্যমান।',
            ]
        );

        CompanyAsset::firstOrCreate(
            ['asset_code' => 'AST-002'],
            [
                'branch_id'     => $mainBranch?->id,
                'name'          => 'হামকো ১০০০VA আইপিএস ও ব্যাটারি (ব্যাকআপ পাওয়ার)',
                'serial_number' => 'HAM-1000VA-2025',
                'purchase_date' => now()->subMonths(4),
                'purchase_cost' => 38000.00,
                'current_value' => 32000.00,
                'condition'     => 'good',
                'notes'         => 'লোডশেডিং ব্যাকআপের জন্য ক্যাশ কাউন্টারে কানেক্টেড।',
            ]
        );

        CompanyAsset::firstOrCreate(
            ['asset_code' => 'AST-003'],
            [
                'branch_id'     => $mainBranch?->id,
                'name'          => 'ডিজিটাল কম্পিউটারাইজড ওজন স্কেল ৪০ কেজি',
                'serial_number' => 'SCALE-40KG-01',
                'purchase_date' => now()->subMonths(3),
                'purchase_cost' => 6500.00,
                'current_value' => 5500.00,
                'condition'     => 'good',
                'notes'         => 'কাঁচা বাজারের ওজন পরিমাপের জন্য কাউন্টারে ব্যবহৃত।',
            ]
        );

        // 3. Seed Initial Owner Capital
        OwnerTransaction::firstOrCreate(
            ['reference' => 'INITIAL-CAPITAL'],
            [
                'branch_id'        => $mainBranch?->id,
                'type'             => 'capital',
                'transaction_date' => now()->subMonths(6),
                'amount'           => 500000.00, // 5 Lakh Taka Initial Capital
                'payment_method'   => 'bank',
                'purpose'          => 'ব্যবসার প্রারম্ভিক মূলধন বিনিয়োগ',
                'notes'            => 'সঞ্চয়ী ব্যাংক হিসাব থেকে ব্যবসার চলতি হিসাবে স্থানান্তর।',
            ]
        );

        // 4. Seed Default Shop Settings & Thermal Format
        $defaultSettings = [
            'shop_name'       => 'মেসার্স মদিনা জেনারেল স্টোর',
            'shop_name_en'    => 'Madina General Store',
            'phone'           => '01712345678',
            'email'           => 'info@madinastore.test',
            'address'         => 'দোকান নং ১২, কাওরান বাজার কাঁচাবাজার, ঢাকা-১২১৫',
            'trade_license'   => 'TRAD/DNCC/2026/0491',
            'bin_vat_number'  => '000123456-0101',
            'currency_symbol' => '৳',
            'vat_percent'     => '0',
            'enable_vat'      => '0',
            'thermal_width'   => '80mm',
            'invoice_header'  => 'বিসমিল্লাহির রাহমানির রাহিম',
            'invoice_footer'  => 'ধন্যবাদ আবার আসবেন! পণ্য ক্রয়ের ৭ দিনের মধ্যে মেমোসহ ফেরত গ্রহণযোগ্য।',
            'bangla_receipt'  => '1',
        ];

        foreach ($defaultSettings as $k => $v) {
            ShopSetting::set($k, $v);
        }
    }
}