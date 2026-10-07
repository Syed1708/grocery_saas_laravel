@extends('tyro-dashboard::layouts.admin')

@section('title', 'Web POS Terminal')

@section('content')
<div class="pos-wrapper">

    {{-- 1. Top Full-Width Header (Branch & Customer Bar) --}}
    @include('pos.partials.header')

    {{-- 2. Main 60% / 40% Split Layout --}}
    <div class="pos-main-container">
        {{-- Left 60%: Product Catalog & Barcode Scanner --}}
        @include('pos.partials.catalog')

        {{-- Right 40%: Cart & Checkout Summary --}}
        @include('pos.partials.cart')
    </div>

</div>

{{-- 3. Dialog Modals (Quick Customer & Receipt Popup) --}}
@include('pos.partials.modals')

{{-- 4. Scoped POS CSS & JavaScript Engine --}}
@include('pos.partials.styles')
@include('pos.partials.scripts')

    {{-- 🚀 Global Loader & Toast Notifications for ALL Pages --}}
    <x-global-loader />
@endsection