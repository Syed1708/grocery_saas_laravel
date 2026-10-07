@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Sale Invoice #' . $sale->invoice_no)

@section('content')
<div class="pos-wrapper">

    <!-- ========================================================= -->
    <!-- 1. FULL-WIDTH HEADER: Invoice Details & Customer Select   -->
    <!-- ========================================================= -->
    <div class="card pos-top-bar" style="margin-bottom: 0.75rem; border: 1px solid var(--border); background: var(--card);">
        <div class="card-body" style="padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.65rem;">
            
            <!-- Row 1: Invoice Title & Action Badge -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, #f59e0b, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; color: #fff; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);">
                        ✏️
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <strong style="font-size: 0.95rem; color: var(--foreground);">Editing Invoice: #{{ $sale->invoice_no }}</strong>
                            <span class="badge badge-warning" style="font-size: 9px; padding: 2px 6px;">CORRECTION MODE</span>
                        </div>
                        <div style="font-size: 11px; color: var(--muted-foreground);">
                            Original Date: <strong style="color: var(--foreground);">{{ $sale->sale_date->format('d M, Y') }}</strong> | Branch: {{ $sale->branch?->name ?? 'Main' }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary" data-no-loader style="font-size: 11px; padding: 4px 10px;">
                    ✕ Cancel & Exit
                </a>
            </div>

            <!-- Row 2: Customer Selection & Date -->
            <div style="display: flex; align-items: center; gap: 0.65rem; width: 100%;">
                <div style="flex: 1.5;">
                    <select id="cart_customer_id" class="form-select" style="font-size: 12px; height: 38px; width: 100%; border-radius: 8px;">
                        <option value="" data-due="0">Walk-in Customer (খুচরা কাস্টমার)</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $sale->customer_id == $c->id ? 'selected' : '' }} data-due="{{ $c->current_due }}">
                                {{ $c->name }} ({{ $c->phone }}) - Due: ৳{{ number_format($c->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin: 0; flex: 1;">
                    <input type="date" id="sale_date_input" value="{{ $sale->sale_date->format('Y-m-d') }}" class="form-input" style="height: 38px; font-size: 12px;">
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 2. MAIN 60% / 40% SPLIT SCREEN CONTAINER                  -->
    <!-- ========================================================= -->
    <div class="pos-main-container">

        <!-- ===================================================== -->
        <!-- LEFT COLUMN (60%): Product Search & 3-Column Grid     -->
        <!-- ===================================================== -->
        <div class="pos-col-left">
            
            <!-- Barcode Scanner Input -->
            <div class="card" style="margin-bottom: 0.65rem; border: 1px solid var(--border);">
                <div class="card-body" style="padding: 0.5rem 0.85rem;">
                    <div style="display: flex; gap: 0.65rem; align-items: center;">
                        <span style="font-size: 1.35rem;">📷</span>
                        <input type="text" id="pos_barcode_input" class="form-input" placeholder="পণ্য খুঁজুন বা বারকোড স্ক্যান করুন..." autofocus style="font-size: 13px; height: 38px; padding: 4px 12px; border-radius: 8px;" onkeydown="handlePosBarcode(event)" oninput="filterPosProducts(this.value)">
                    </div>
                </div>
            </div>

            <!-- Category Filter Chips -->
            <div class="pos-category-tabs hide-scrollbar" style="display: flex; gap: 0.45rem; overflow-x: auto; padding-bottom: 6px; margin-bottom: 0.65rem;">
                <button type="button" class="btn btn-primary pos-cat-btn" onclick="filterByCat('all', this)" style="padding: 5px 12px; font-size: 11.5px; border-radius: 8px; white-space: nowrap; font-weight: 600;">
                    🌟 সকল পণ্য (All Items)
                </button>
                @foreach(\App\Models\Category::active()->get() as $cat)
                    <button type="button" class="btn btn-secondary pos-cat-btn" onclick="filterByCat('{{ $cat->id }}', this)" style="padding: 5px 12px; font-size: 11.5px; border-radius: 8px; white-space: nowrap; font-weight: 600;">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- 3-Column Products Touch Grid -->
            <div class="card pos-products-scroll-area" style="flex: 1; min-height: 0; overflow-y: auto; border: 1px solid var(--border);">
                <div class="card-body" style="padding: 0.75rem;">
                    <div class="pos-grid-3-col" id="posProductGrid">
                        @foreach($products as $p)
                            <div class="pos-prod-card" data-id="{{ $p->id }}" data-barcode="{{ $p->barcode }}" data-name="{{ strtolower($p->name . ' ' . $p->name_en) }}" data-cat="{{ $p->category_id }}" onclick="addToCart({{ $p->id }})">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px; margin-bottom: 4px;">
                                    <span class="pos-prod-cat-pill">{{ $p->category?->name ?? 'Grocery' }}</span>
                                    <span class="badge {{ $p->currentStock() <= 0 ? 'badge-danger' : 'badge-success' }}" style="font-size: 9.5px; padding: 2px 5px; font-weight: 700;">
                                        {{ $p->currentStock() }} {{ $p->unit?->short_code }}
                                    </span>
                                </div>
                                <div class="pos-prod-title" title="{{ $p->name }}">{{ $p->name }}</div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 6px;">
                                    <span class="pos-prod-price">৳{{ number_format($p->selling_price, 2) }}</span>
                                    <div class="pos-add-badge">+ Add</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <!-- ===================================================== -->
        <!-- RIGHT COLUMN (40%): POS-Style Cart & Complete Payment -->
        <!-- ===================================================== -->
        <div class="card pos-col-right" style="border: 1px solid var(--border); display: flex; flex-direction: column; min-height: 0;">
            
            <!-- Cart Header -->
            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.05)); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.1rem;">🛒</span>
                    <h3 style="font-size: 0.95rem; font-weight: 800; margin: 0; color: var(--foreground);">Your Cart Items (আপনার কার্ট)</h3>
                    <span id="cartCountBadge" class="badge badge-primary" style="font-size: 10px; font-weight: 800;">0 Items</span>
                </div>
                <button type="button" onclick="clearCart()" class="btn btn-ghost" style="padding: 2px 8px; font-size: 11px; color: var(--danger, #ef4444); font-weight: 600;">
                    🗑️ Clear
                </button>
            </div>

            <!-- Cart Items Table with Quantity Steppers -->
            <div class="pos-cart-items-wrapper" id="cartItemsContainer">
                <div id="emptyCartMessage" style="text-align: center; color: var(--muted-foreground); padding: 4rem 1.5rem; font-size: 13px; display: none;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.4;">🛍️</div>
                    <strong style="color: var(--foreground); display: block;">কার্ট বর্তমানে খালি</strong>
                </div>

                <table class="table" id="cartTable" style="width: 100%; font-size: 12px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); background: rgba(148, 163, 184, 0.02);">
                            <th style="padding: 8px 6px;">পণ্য (Item)</th>
                            <th style="text-align: center; width: 105px; padding: 8px 4px;">পরিমাণ (Qty)</th>
                            <th style="text-align: right; width: 85px; padding: 8px 6px;">মোট (Total)</th>
                            <th style="width: 30px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody"></tbody>
                </table>
            </div>

            <!-- Payment & Totals Summary Footer (100% Matching POS) -->
            <div style="padding: 0.85rem 1rem; border-top: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.04));">
                
                <!-- Totals Row: Subtotal | Discount | VAT | Grand Total -->
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.75rem; align-items: center; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed var(--border);">
                    <div style="font-size: 11.5px; color: var(--muted-foreground);">
                        Subtotal: <strong id="cartSubtotal" style="color: var(--foreground); font-family: monospace;">৳0.00</strong>
                        @if($sale->vat_percent > 0)
                            <span style="margin-left: 6px;">VAT ({{ $sale->vat_percent }}%): <strong id="cartVat" style="color: var(--foreground); font-family: monospace;">৳0.00</strong></span>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 11.5px;">
                        <span>ছাড় (Disc):</span>
                        <input type="number" step="0.5" id="cartDiscount" value="{{ $sale->discount }}" class="form-input" style="width: 75px; height: 26px; padding: 2px 6px; font-size: 11.5px; text-align: right; font-weight: 700; border-radius: 6px;" oninput="renderCartTotals()">
                    </div>

                    <div style="text-align: right;">
                        <span style="font-size: 10px; color: var(--muted-foreground); text-transform: uppercase; display: block;">সংশোধিত মোট</span>
                        <span id="cartGrandTotal" style="font-size: 1.35rem; font-weight: 900; color: var(--primary); font-family: monospace;">৳0.00</span>
                    </div>
                </div>

                <!-- Quick Cash Presets Row (৳50, ৳100, ৳500, ৳1000, Exact, Split Pay) -->
                <div style="display: flex; gap: 4px; align-items: center; margin-bottom: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="setQuickCash(50)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700; border-radius: 6px;">৳50</button>
                    <button type="button" class="btn btn-secondary" onclick="setQuickCash(100)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700; border-radius: 6px;">৳100</button>
                    <button type="button" class="btn btn-secondary" onclick="setQuickCash(500)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700; border-radius: 6px;">৳500</button>
                    <button type="button" class="btn btn-secondary" onclick="setQuickCash(1000)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700; border-radius: 6px;">৳1000</button>
                    <button type="button" class="btn btn-secondary" onclick="setExactCash()" style="flex: 1.2; padding: 3px 0; font-size: 11px; font-weight: 700; border-radius: 6px; color: var(--success, #10b981);">Exact (সমান)</button>
                    <button type="button" onclick="toggleSplitPayment()" class="btn btn-ghost" style="padding: 3px 8px; font-size: 11px; text-decoration: underline; color: var(--primary); font-weight: 700;">
                        ⚡ Split Pay
                    </button>
                </div>

                <!-- Expandable Split Payment Panel -->
                <div id="splitPaymentBox" style="display: none; background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: 8px; margin-bottom: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="font-size: 10px; font-weight: 700; color: var(--muted-foreground); text-transform: uppercase; margin-bottom: 4px;">স্প্লিট পেমেন্ট বণ্টন (Mixed Payment)</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                        <div>
                            <label style="font-size: 10px; color: var(--muted-foreground); display: block;">নগদ (Cash)</label>
                            <input type="number" id="pay_cash" class="form-input" placeholder="0.00" style="height: 28px; padding: 2px 6px; font-size: 11.5px; font-weight: 800;" oninput="renderCartTotals()">
                        </div>
                        <div>
                            <label style="font-size: 10px; color: var(--muted-foreground); display: block;">bKash/Nagad</label>
                            <input type="number" id="pay_bkash" class="form-input" placeholder="0.00" style="height: 28px; padding: 2px 6px; font-size: 11.5px; font-weight: 800; color: #d946ef;" oninput="renderCartTotals()">
                        </div>
                        <div>
                            <label style="font-size: 10px; color: var(--muted-foreground); display: block;">Bank / Card</label>
                            <input type="number" id="pay_bank" class="form-input" placeholder="0.00" style="height: 28px; padding: 2px 6px; font-size: 11.5px; font-weight: 800; color: #6366f1;" oninput="renderCartTotals()">
                        </div>
                    </div>
                </div>

                <!-- Paid Input & Real-time Due / Change Indicators -->
                <div style="display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 8px; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 12px; font-weight: 800;">পরিশোধ:</span>
                        <input type="number" step="0.5" id="cartPaidAmount" value="{{ $sale->paid_amount }}" class="form-input" placeholder="0.00" style="height: 32px; padding: 2px 8px; font-size: 13px; font-weight: 900; color: var(--success, #10b981); border-radius: 6px; font-family: monospace;" oninput="onMainPaidInput(this.value)">
                    </div>
                    <div style="display: flex; flex-direction: column; text-align: right; background: rgba(16, 185, 129, 0.08); padding: 3px 8px; border-radius: 6px;">
                        <span style="font-size: 9.5px; color: var(--muted-foreground);">ফেরত (Change):</span>
                        <strong id="displayChangeText" style="color: var(--success, #10b981); font-size: 12px; font-family: monospace;">৳0.00</strong>
                    </div>
                    <div style="display: flex; flex-direction: column; text-align: right; background: rgba(239, 68, 68, 0.08); padding: 3px 8px; border-radius: 6px;">
                        <span style="font-size: 9.5px; color: var(--danger, #ef4444);">বাকি (Due):</span>
                        <strong id="displayDueText" style="color: var(--danger, #ef4444); font-size: 12px; font-family: monospace;">৳0.00</strong>
                    </div>
                </div>

                <!-- Correction Notes -->
                <div style="margin-bottom: 8px;">
                    <input type="text" id="correction_notes" value="{{ $sale->notes }}" placeholder="সংশোধনের কারণ বা মন্তব্য..." class="form-input" style="height: 28px; font-size: 11.5px; border-radius: 6px;">
                </div>

                <!-- Submit Button -->
                <button type="button" class="btn btn-primary" onclick="submitEditSale()" style="width: 100%; padding: 0.75rem 1rem; font-weight: 900; font-size: 14px; border-radius: 8px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                    ✓ Update Invoice & Reconcile Stock
                </button>

            </div>
        </div>

    </div>
</div>

<!-- ========================================================= -->
<!-- STYLES                                                    -->
<!-- ========================================================= -->
<style>
    .pos-wrapper { display: flex; flex-direction: column; height: calc(100vh - 85px); }
    .pos-main-container { display: grid; grid-template-columns: 6fr 4fr; gap: 0.85rem; flex: 1; min-height: 0; align-items: stretch; }
    .pos-col-left { display: flex; flex-direction: column; min-height: 0; min-width: 0; }
    .pos-col-right { min-width: 0; overflow: hidden; }
    .pos-grid-3-col { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.65rem; }
    .pos-prod-card { border: 1px solid var(--border); border-radius: 10px; padding: 0.65rem; cursor: pointer; background: var(--card); transition: all 0.18s ease; display: flex; flex-direction: column; justify-content: space-between; height: 105px; user-select: none; }
    .pos-prod-card:hover { transform: translateY(-2px); border-color: var(--primary, #0284c7); box-shadow: 0 8px 16px -4px rgba(2, 132, 199, 0.2); }
    .pos-prod-cat-pill { font-size: 8.5px; font-weight: 700; color: var(--muted-foreground); text-transform: uppercase; background: rgba(148, 163, 184, 0.1); padding: 1px 5px; border-radius: 4px; }
    .pos-prod-title { font-size: 12px; font-weight: 700; color: var(--foreground); line-height: 1.25; height: 30px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .pos-prod-price { font-size: 14px; font-weight: 900; color: var(--primary, #0284c7); font-family: monospace; }
    .pos-add-badge { font-size: 9.5px; font-weight: 700; background: rgba(2, 132, 199, 0.1); color: var(--primary, #0284c7); padding: 2px 6px; border-radius: 4px; }
    .pos-cart-items-wrapper { flex: 1; min-height: 160px; overflow-y: auto; padding: 0.25rem 0.5rem; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<!-- ========================================================= -->
<!-- JAVASCRIPT: Full POS Engine on Edit                       -->
<!-- ========================================================= -->
<script>
    const posProducts = {!! json_encode($products) !!};
    const vatRate = {{ $sale->vat_percent ?? 0 }};
    
    // 🚀 Pre-populate cart with this invoice's items
    let cart = {!! json_encode($saleItems) !!};

    document.addEventListener('DOMContentLoaded', () => {
        // Pre-fill initial payment method inputs if existing
        @if($sale->payment_method === 'cash')
            document.getElementById('pay_cash').value = '{{ $sale->paid_amount }}';
        @elseif($sale->payment_method === 'bkash')
            document.getElementById('pay_bkash').value = '{{ $sale->paid_amount }}';
        @elseif($sale->payment_method === 'bank')
            document.getElementById('pay_bank').value = '{{ $sale->paid_amount }}';
        @endif

        renderCart();
    });

    function toggleSplitPayment() {
        const box = document.getElementById('splitPaymentBox');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }

    function addToCart(productId) {
        const product = posProducts.find(p => p.id === productId);
        if (!product) return;

        const existing = cart.find(item => item.product_id === productId);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                product_id: product.id,
                name: product.name,
                unit: product.unit ? product.unit.short_code : 'pc',
                price: parseFloat(product.selling_price),
                quantity: 1,
            });
        }
        renderCart();
        window.Toast.success(`যোগ করা হয়েছে: ${product.name}`);
    }

    function changeQtyStep(idx, delta) {
        cart[idx].quantity = Math.max(0.1, parseFloat((cart[idx].quantity + delta).toFixed(2)));
        renderCart();
    }

    function updateQty(idx, newQty) {
        const qty = parseFloat(newQty);
        if (isNaN(qty) || qty <= 0) {
            cart.splice(idx, 1);
        } else {
            cart[idx].quantity = qty;
        }
        renderCart();
    }

    function removeFromCart(idx) {
        cart.splice(idx, 1);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const emptyMsg = document.getElementById('emptyCartMessage');
        const cartTable = document.getElementById('cartTable');
        const tbody = document.getElementById('cartTableBody');
        const countBadge = document.getElementById('cartCountBadge');

        countBadge.textContent = `${cart.length} Items`;

        if (cart.length === 0) {
            emptyMsg.style.display = 'block';
            cartTable.style.display = 'none';
            tbody.innerHTML = '';
        } else {
            emptyMsg.style.display = 'none';
            cartTable.style.display = 'table';
            tbody.innerHTML = '';

            cart.forEach((item, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 6px 4px;">
                        <strong style="color: var(--foreground); font-size: 11.5px; display: block; line-height: 1.2;">${item.name}</strong>
                        <span style="font-size: 10px; color: var(--muted-foreground); font-family: monospace;">৳${item.price.toFixed(2)} / ${item.unit}</span>
                    </td>
                    <td style="padding: 6px 4px; text-align: center;">
                        <div style="display: inline-flex; align-items: center; border: 1px solid var(--border); border-radius: 6px; overflow: hidden; background: var(--card);">
                            <button type="button" onclick="changeQtyStep(${idx}, -1)" style="border: none; background: transparent; padding: 2px 6px; cursor: pointer; font-weight: bold; font-size: 11px; color: var(--foreground);">-</button>
                            <input type="number" step="0.1" value="${item.quantity}" min="0.1" style="height: 22px; padding: 0; font-size: 11px; text-align: center; width: 44px; border: none; font-weight: 700; background: transparent; color: var(--foreground);" onchange="updateQty(${idx}, this.value)">
                            <button type="button" onclick="changeQtyStep(${idx}, 1)" style="border: none; background: transparent; padding: 2px 6px; cursor: pointer; font-weight: bold; font-size: 11px; color: var(--foreground);">+</button>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 800; padding: 6px 4px; font-family: monospace; font-size: 12px; color: var(--foreground);">
                        ৳${(item.quantity * item.price).toFixed(2)}
                    </td>
                    <td style="text-align: center; padding: 6px 2px;">
                        <button type="button" onclick="removeFromCart(${idx})" style="border: none; background: none; color: var(--danger, #ef4444); cursor: pointer; font-size: 13px; padding: 0 4px;" title="Remove">✕</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        renderCartTotals();
    }

    function onMainPaidInput(val) {
        document.getElementById('pay_cash').value = val;
        document.getElementById('pay_bkash').value = '';
        document.getElementById('pay_bank').value = '';
        renderCartTotals();
    }

    function renderCartTotals() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
        
        const base = Math.max(0, subtotal - discount);
        const vat = (base * vatRate) / 100;
        const grandTotal = base + vat;

        document.getElementById('cartSubtotal').textContent = `৳${subtotal.toFixed(2)}`;
        const vatEl = document.getElementById('cartVat');
        if (vatEl) vatEl.textContent = `৳${vat.toFixed(2)}`;
        document.getElementById('cartGrandTotal').textContent = `৳${grandTotal.toFixed(2)}`;

        const cash = parseFloat(document.getElementById('pay_cash').value) || 0;
        const bkash = parseFloat(document.getElementById('pay_bkash').value) || 0;
        const bank = parseFloat(document.getElementById('pay_bank').value) || 0;
        
        let totalPaid = cash + bkash + bank;
        if (totalPaid === 0 && document.getElementById('cartPaidAmount').value) {
            totalPaid = parseFloat(document.getElementById('cartPaidAmount').value) || 0;
        }

        const change = Math.max(0, totalPaid - grandTotal);
        const due = Math.max(0, grandTotal - totalPaid);

        document.getElementById('displayChangeText').textContent = `৳${change.toFixed(2)}`;
        document.getElementById('displayDueText').textContent = `৳${due.toFixed(2)}`;
    }

    function setQuickCash(amount) {
        document.getElementById('cartPaidAmount').value = amount;
        document.getElementById('pay_cash').value = amount;
        document.getElementById('pay_bkash').value = '';
        document.getElementById('pay_bank').value = '';
        renderCartTotals();
    }

    function setExactCash() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
        const base = Math.max(0, subtotal - discount);
        const grandTotal = base + ((base * vatRate) / 100);

        setQuickCash(grandTotal);
    }

    function filterByCat(catId, btn) {
        document.querySelectorAll('.pos-cat-btn').forEach(b => b.className = 'btn btn-secondary pos-cat-btn');
        btn.className = 'btn btn-primary pos-cat-btn';

        document.querySelectorAll('.pos-prod-card').forEach(card => {
            if (catId === 'all' || card.getAttribute('data-cat') === catId) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function filterPosProducts(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.pos-prod-card').forEach(card => {
            const name = card.getAttribute('data-name');
            const barcode = card.getAttribute('data-barcode');
            if (name.includes(q) || barcode.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function handlePosBarcode(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = e.target.value.trim();
            if (!code) return;

            const product = posProducts.find(p => p.barcode === code);
            if (product) {
                addToCart(product.id);
                e.target.value = '';
            } else {
                window.Toast.error(`বারকোড '${code}' পণ্য পাওয়া যায়নি!`);
            }
        }
    }

    function submitEditSale() {
        if (cart.length === 0) {
            window.Toast.warning('ইনভয়েসে অন্তত একটি পণ্য থাকতে হবে!');
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
        const base = Math.max(0, subtotal - discount);
        const grandTotal = base + ((base * vatRate) / 100);

        const cash = parseFloat(document.getElementById('pay_cash').value) || 0;
        const bkash = parseFloat(document.getElementById('pay_bkash').value) || 0;
        const bank = parseFloat(document.getElementById('pay_bank').value) || 0;
        
        let totalPaid = cash + bkash + bank;
        if (totalPaid === 0 && document.getElementById('cartPaidAmount').value) {
            totalPaid = parseFloat(document.getElementById('cartPaidAmount').value) || 0;
        }

        const due = Math.max(0, grandTotal - totalPaid);

        // Determine Payment Method label
        let method = 'cash';
        const paidMethods = array_filter_obj({ cash: cash > 0, bkash: bkash > 0, bank: bank > 0 });
        if (Object.keys(paidMethods).length > 1) {
            method = 'mixed';
        } else if (Object.keys(paidMethods).length === 1) {
            method = Object.keys(paidMethods)[0];
        } else if (due > 0) {
            method = 'due';
        }

        window.Loader.show('ইনভয়েস আপডেট হচ্ছে...', 'স্টক ও বকেয়া পুনর্গণনা চলছে');

        const payload = {
            customer_id: document.getElementById('cart_customer_id').value,
            sale_date: document.getElementById('sale_date_input').value,
            discount: discount,
            vat_percent: vatRate,
            cash_paid: cash > 0 ? cash : (totalPaid === 0 ? grandTotal : totalPaid),
            bkash_paid: bkash,
            bank_paid: bank,
            paid_amount: totalPaid > 0 ? totalPaid : grandTotal,
            payment_method: method,
            notes: document.getElementById('correction_notes').value,
            items: cart.map(it => ({
                product_id: it.product_id,
                quantity: it.quantity,
                unit_price: it.price
            }))
        };

        fetch(`{{ route('sales.update', $sale) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-HTTP-Method-Override': 'PUT',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => {
            window.Loader.hide();
            if (res.redirected) {
                window.location.href = res.url;
            } else {
                window.location.href = `{{ route('sales.show', $sale) }}`;
            }
        })
        .catch(err => {
            window.Loader.hide();
            window.Toast.error('সার্ভারের সাথে যোগাযোগ করা যায়নি।');
        });
    }

    function array_filter_obj(obj) {
        const result = {};
        for (const key in obj) {
            if (obj[key]) result[key] = obj[key];
        }
        return result;
    }
</script>
@endsection