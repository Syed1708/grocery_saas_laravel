@extends('tyro-dashboard::layouts.admin')

@section('title', 'Shop Settings')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Settings</span>
<span class="breadcrumb-separator">/</span>
<span>Shop</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Shop & Thermal Receipt Settings (দোকান সেটিংস)</h1>
            <p class="page-description">Configure your store profile, BIN/Trade License, and 58mm/80mm thermal receipt formats.</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--success, #10b981); background: rgba(16, 185, 129, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--success, #10b981); font-weight: 500; font-size: 0.875rem;">
        {{ session('success') }}
    </div>
</div>
@endif

<form action="{{ route('settings.shop.update') }}" method="POST" style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
    @csrf

    <!-- Store Profile -->
    <div class="card">
        <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 700;">🏪 Store Profile (দোকানের তথ্য)</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="shop_name">Store Name (Bangla) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="shop_name" name="shop_name" class="form-input" value="{{ old('shop_name', $settings['shop_name']) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="shop_name_en">Store Name (English)</label>
                    <input type="text" id="shop_name_en" name="shop_name_en" class="form-input" value="{{ old('shop_name_en', $settings['shop_name_en']) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $settings['phone']) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Store Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $settings['email']) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="address">Store Address</label>
                <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address', $settings['address']) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="trade_license">Trade License No</label>
                    <input type="text" id="trade_license" name="trade_license" class="form-input" value="{{ old('trade_license', $settings['trade_license']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="bin_vat_number">BIN / VAT Registration</label>
                    <input type="text" id="bin_vat_number" name="bin_vat_number" class="form-input" value="{{ old('bin_vat_number', $settings['bin_vat_number']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="currency_symbol">Currency Symbol</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" class="form-input" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Thermal Printer Settings -->
    <div class="card">
        <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 700;">🖨️ POS Thermal Receipt Config (রসিদ ফরম্যাট)</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="thermal_width">Receipt Paper Width</label>
                    <select id="thermal_width" name="thermal_width" class="form-select">
                        <option value="80mm" {{ $settings['thermal_width'] === '80mm' ? 'selected' : '' }}>80mm (Standard POS Slip)</option>
                        <option value="58mm" {{ $settings['thermal_width'] === '58mm' ? 'selected' : '' }}>58mm (Mini Slip)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="vat_percent">VAT / Tax Rate (%)</label>
                    <input type="number" step="0.1" id="vat_percent" name="vat_percent" class="form-input" value="{{ old('vat_percent', $settings['vat_percent']) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="invoice_header">Receipt Header Message</label>
                <input type="text" id="invoice_header" name="invoice_header" class="form-input" value="{{ old('invoice_header', $settings['invoice_header']) }}">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="invoice_footer">Receipt Footer / Return Policy</label>
                <textarea id="invoice_footer" name="invoice_footer" class="form-textarea" rows="2">{{ old('invoice_footer', $settings['invoice_footer']) }}</textarea>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.5rem; padding-top: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="enable_vat" name="enable_vat" value="1" {{ $settings['enable_vat'] == '1' ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <label for="enable_vat" class="form-label" style="margin: 0; cursor: pointer;">Enable VAT calculation on invoices</label>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="bangla_receipt" name="bangla_receipt" value="1" {{ $settings['bangla_receipt'] == '1' ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <label for="bangla_receipt" class="form-label" style="margin: 0; cursor: pointer;">Print Thermal Slips with Bengali Typography (Kalpurush font)</label>
                </div>
            </div>
        </div>
        <div class="card-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </div>
</form>


@endsection