<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Http\Request;

class ShopSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'shop_name'        => ShopSetting::get('shop_name', 'মেসার্স মদিনা জেনারেল স্টোর'),
            'shop_name_en'     => ShopSetting::get('shop_name_en', 'Madina General Store'),
            'phone'            => ShopSetting::get('phone', '01712345678'),
            'email'            => ShopSetting::get('email', 'info@madinastore.test'),
            'address'          => ShopSetting::get('address', 'দোকান নং ১২, কাওরান বাজার, ঢাকা'),
            'trade_license'    => ShopSetting::get('trade_license', 'TRAD/DNCC/2026/0491'),
            'bin_vat_number'   => ShopSetting::get('bin_vat_number', '000123456-0101'),
            'currency_symbol'  => ShopSetting::get('currency_symbol', '৳'),
            'vat_percent'      => ShopSetting::get('vat_percent', '0'),
            'enable_vat'       => ShopSetting::get('enable_vat', '0'),
            'thermal_width'    => ShopSetting::get('thermal_width', '80mm'), // 58mm or 80mm
            'invoice_header'   => ShopSetting::get('invoice_header', 'বিসমিল্লাহির রাহমানির রাহিম'),
            'invoice_footer'   => ShopSetting::get('invoice_footer', 'ধন্যবাদ আবার আসবেন! পণ্য ক্রয়ের ৭ দিনের মধ্যে মেমোসহ ফেরত দিতে হবে।'),
            'bangla_receipt'   => ShopSetting::get('bangla_receipt', '1'),
        ];

        return view('settings.shop', compact('settings'));
    }

    public function shopsubscription()
    {

        $license = \App\Models\License::first();
        
        return view('settings.shopsubcription', compact('license'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name'       => 'required|string|max:191',
            'shop_name_en'    => 'nullable|string|max:191',
            'phone'           => 'required|string|max:20',
            'email'           => 'nullable|email|max:100',
            'address'         => 'nullable|string',
            'trade_license'   => 'nullable|string|max:100',
            'bin_vat_number'  => 'nullable|string|max:100',
            'currency_symbol' => 'required|string|max:10',
            'vat_percent'     => 'nullable|numeric|min:0|max:100',
            'thermal_width'   => 'required|in:58mm,80mm',
            'invoice_header'  => 'nullable|string',
            'invoice_footer'  => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            ShopSetting::set($key, $value);
        }

        ShopSetting::set('enable_vat', $request->has('enable_vat') ? '1' : '0');
        ShopSetting::set('bangla_receipt', $request->has('bangla_receipt') ? '1' : '0');

        return back()->with('success', 'দোকানের সেটিংস ও প্রিন্ট ফরম্যাট সফলভাবে সংরক্ষণ করা হয়েছে!');
    }
}