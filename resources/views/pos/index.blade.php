@extends('tyro-dashboard::layouts.admin')

@section('title', 'POS Counter')

@section('content')
<!-- POS 50/50 Responsive Grid -->
<div class="pos-main-container">

    <!-- ========================================== -->
    <!-- LEFT COLUMN (50%): Products & Barcode Search -->
    <!-- ========================================== -->
    <div class="pos-col-left">
        
        <!-- Barcode & Product Live Search -->
        <div class="card" style="margin-bottom: 0.5rem;">
            <div class="card-body" style="padding: 0.5rem 0.75rem;">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span style="font-size: 1.25rem;">📷</span>
                    <input type="text" id="pos_barcode_input" class="form-input" placeholder="Scan Barcode gun or type product name/SKU..." autofocus style="font-size: 0.95rem; padding: 0.4rem 0.75rem;" onkeydown="handlePosBarcode(event)" oninput="filterPosProducts(this.value)">
                </div>
            </div>
        </div>

        <!-- Category Tabs Filter -->
        <div style="display: flex; gap: 0.35rem; overflow-x: auto; padding-bottom: 4px; margin-bottom: 0.5rem;" class="hide-scrollbar">
            <button type="button" class="btn btn-primary pos-cat-btn" onclick="filterByCat('all', this)" style="padding: 3px 8px; font-size: 11px; white-space: nowrap;">All Items</button>
            @foreach($categories as $cat)
                <button type="button" class="btn btn-secondary pos-cat-btn" onclick="filterByCat('{{ $cat->id }}', this)" style="padding: 3px 8px; font-size: 11px; white-space: nowrap;">{{ $cat->name }}</button>
            @endforeach
        </div>

        <!-- Products Touch Grid -->
        <div class="card pos-products-card" style="flex: 1; min-height: 0; overflow-y: auto;">
            <div class="card-body" style="padding: 0.65rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.5rem;" id="posProductGrid">
                @foreach($products as $p)
                    <div class="pos-prod-card" data-id="{{ $p->id }}" data-barcode="{{ $p->barcode }}" data-name="{{ strtolower($p->name . ' ' . $p->name_en) }}" data-cat="{{ $p->category_id }}" onclick="addToCart({{ $p->id }})" style="border: 1px solid var(--border); border-radius: 8px; padding: 0.5rem; cursor: pointer; background: var(--card); transition: all 0.15s ease; user-select: none;">
                        <div style="font-size: 9px; color: var(--muted-foreground); text-transform: uppercase;">{{ $p->category?->name }}</div>
                        <div style="font-size: 11.5px; font-weight: 700; color: var(--foreground); margin: 2px 0; line-height: 1.2; height: 28px; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                            <span style="font-size: 13px; font-weight: 800; color: var(--primary);">৳{{ number_format($p->selling_price, 2) }}</span>
                            <span class="badge {{ $p->currentStock() <= 0 ? 'badge-danger' : 'badge-secondary' }}" style="font-size: 9px; padding: 1px 4px;">{{ $p->currentStock() }} {{ $p->unit?->short_code }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- RIGHT COLUMN (50%): Max-Height Cart & Checkout -->
    <!-- ========================================== -->
    <div class="card pos-col-right" style="display: flex; flex-direction: column; min-height: 0;">
        
        <!-- 1. Compact Customer Header Bar (Space-Saving) -->
        <div style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.04)); display: flex; flex-direction: column; gap: 0.35rem;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px;">
                <span style="color: var(--muted-foreground);">Branch: <strong style="color: var(--foreground);">{{ $activeBranch?->name ?? 'Main' }}</strong></span>
                
                <!-- Customer Previous Due Badge -->
                <span id="pos_cust_due_badge" style="display: none; align-items: center; gap: 4px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 11px;">
                    <span>Previous Due:</span>
                    <strong id="pos_display_cust_due">৳0.00</strong>
                </span>
            </div>

            <!-- Customer Search & Dropdown in One Compact Row -->
            <div style="display: flex; gap: 4px; position: relative;">
                <input type="text" id="pos_cust_search" class="form-input" placeholder="🔍 Search Customer..." style="font-size: 11px; height: 30px; padding: 2px 6px; width: 45%;" autocomplete="off" oninput="ajaxSearchPosCustomer(this.value)">
                
                <select id="cart_customer_id" class="form-select" style="font-size: 11px; height: 30px; padding: 2px 4px; flex: 1;" onchange="onPosCustomerSelectChange(this)">
                    <option value="" data-due="0" data-limit="0">Walk-in Customer (খুচরা)</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-due="{{ $c->current_due }}" data-limit="{{ $c->credit_limit }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">
                            {{ $c->name }} ({{ $c->phone }})
                        </option>
                    @endforeach
                </select>

                <button type="button" class="btn btn-primary" onclick="openQuickCustomerModal()" style="padding: 0 8px; height: 30px; font-weight: bold; font-size: 12px;" title="Quick Add Customer">+</button>

                <!-- Suggestions Dropdown -->
                <div id="pos_cust_suggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--card, #fff); border: 1px solid var(--border); border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 100; max-height: 180px; overflow-y: auto;"></div>
            </div>
        </div>

        <!-- 2. MAXIMIZED CART ITEMS TABLE (Takes 60%+ of height so 6-10 items are visible!) -->
        <div style="flex: 1; min-height: 220px; overflow-y: auto; padding: 0.25rem 0.5rem;" id="cartItemsContainer">
            <div id="emptyCartMessage" style="text-align: center; color: var(--muted-foreground); padding: 3.5rem 1rem; font-size: 13px;">
                🛒 Cart is empty.<br>Scan barcode or click product tiles.
            </div>
            <table class="table" id="cartTable" style="display: none; font-size: 12px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <th style="padding: 6px 4px;">Item</th>
                        <th style="width: 80px; text-align: center; padding: 6px 4px;">Qty</th>
                        <th style="text-align: right; width: 75px; padding: 6px 4px;">Total</th>
                        <th style="width: 25px; padding: 6px 2px;"></th>
                    </tr>
                </thead>
                <tbody id="cartTableBody"></tbody>
            </table>
        </div>

        <!-- 3. COMPACT PAYMENT & TOTALS FOOTER -->
        <div style="padding: 0.65rem 0.85rem; border-top: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.04));">
            
            <!-- Summary Row: Subtotal | Discount | VAT | Grand Total -->
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; align-items: center; margin-bottom: 4px;">
                <div style="font-size: 11px; color: var(--muted-foreground);">
                    Subtotal: <strong id="cartSubtotal" style="color: var(--foreground);">৳0.00</strong>
                    @if($enableVat)
                        <span style="margin-left: 4px;">VAT: <strong id="cartVat">৳0.00</strong></span>
                    @endif
                </div>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 11px;">
                    <span>Disc:</span>
                    <input type="number" id="cartDiscount" value="0.00" class="form-input" style="width: 65px; height: 22px; padding: 1px 4px; font-size: 11px; text-align: right;" oninput="renderCartTotals()">
                </div>

                <div style="text-align: right; font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                    <span id="cartGrandTotal">৳0.00</span>
                </div>
            </div>

            <!-- Quick Cash Buttons Row -->
            <div style="display: flex; gap: 4px; align-items: center; margin-bottom: 6px;">
                <button type="button" class="btn btn-secondary" onclick="setQuickCash(50)" style="flex: 1; padding: 2px; font-size: 10.5px; font-weight: 700;">৳50</button>
                <button type="button" class="btn btn-secondary" onclick="setQuickCash(100)" style="flex: 1; padding: 2px; font-size: 10.5px; font-weight: 700;">৳100</button>
                <button type="button" class="btn btn-secondary" onclick="setQuickCash(500)" style="flex: 1; padding: 2px; font-size: 10.5px; font-weight: 700;">৳500</button>
                <button type="button" class="btn btn-secondary" onclick="setQuickCash(1000)" style="flex: 1; padding: 2px; font-size: 10.5px; font-weight: 700;">৳1000</button>
                <button type="button" onclick="toggleSplitPayment()" class="btn btn-ghost" style="padding: 2px 6px; font-size: 10px; text-decoration: underline; color: var(--primary);">
                    ⚡ Split Pay
                </button>
            </div>

            <!-- Expandable Split / Mixed Payments Box -->
            <div id="splitPaymentBox" style="display: none; background: var(--card); border: 1px solid var(--border); border-radius: 6px; padding: 6px; margin-bottom: 6px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px;">
                    <div>
                        <label style="font-size: 9px; color: var(--muted-foreground); display: block;">Cash (নগদ)</label>
                        <input type="number" id="pay_cash" class="form-input" placeholder="0.00" style="height: 24px; padding: 1px 4px; font-size: 11px; font-weight: 700;" oninput="renderCartTotals()">
                    </div>
                    <div>
                        <label style="font-size: 9px; color: var(--muted-foreground); display: block;">bKash/Nagad</label>
                        <input type="number" id="pay_bkash" class="form-input" placeholder="0.00" style="height: 24px; padding: 1px 4px; font-size: 11px; font-weight: 700;" oninput="renderCartTotals()">
                    </div>
                    <div>
                        <label style="font-size: 9px; color: var(--muted-foreground); display: block;">Bank / Card</label>
                        <input type="number" id="pay_bank" class="form-input" placeholder="0.00" style="height: 24px; padding: 1px 4px; font-size: 11px; font-weight: 700;" oninput="renderCartTotals()">
                    </div>
                </div>
            </div>

            <!-- Paid / Change / Due Indicators -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 6px; align-items: center; margin-bottom: 6px;">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <span style="font-size: 11px; font-weight: 700;">Paid:</span>
                    <input type="number" id="cartPaidAmount" class="form-input" placeholder="0.00" style="height: 28px; padding: 2px 6px; font-size: 12px; font-weight: 800; color: var(--success, #10b981);" oninput="onMainPaidInput(this.value)">
                </div>
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px;">
                    <span style="color: var(--muted-foreground);">Change:</span>
                    <strong id="displayChangeText" style="color: var(--foreground);">৳0.00</strong>
                </div>
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px;">
                    <span style="color: var(--danger, #ef4444);">Due:</span>
                    <strong id="displayDueText" style="color: var(--danger, #ef4444);">৳0.00</strong>
                </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="submitPosSale()" style="width: 100%; padding: 0.6rem; font-weight: 800; font-size: 13.5px; border-radius: 6px;">
                ✓ Complete Sale & Print Slip
            </button>

        </div>
    </div>
</div>

<!-- Modal Dialogs -->
<div id="quickCustomerModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: var(--card, #fff); border-radius: 12px; padding: 1.5rem; max-width: 380px; width: 100%; border: 1px solid var(--border); box-shadow: 0 20px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 800; margin: 0;">Add New Customer</h3>
            <button type="button" onclick="closeQuickCustomerModal()" style="border: none; background: none; font-size: 18px; cursor: pointer;">✕</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
            <div>
                <label class="form-label" style="font-size: 11px;">Customer Name <span style="color: var(--danger);">*</span></label>
                <input type="text" id="modal_cust_name" class="form-input" placeholder="e.g. মোঃ শফিকুল ইসলাম">
            </div>
            <div>
                <label class="form-label" style="font-size: 11px;">Phone Number <span style="color: var(--danger);">*</span></label>
                <input type="text" id="modal_cust_phone" class="form-input" placeholder="017XXXXXXXX">
            </div>
            <div>
                <label class="form-label" style="font-size: 11px;">Address</label>
                <input type="text" id="modal_cust_address" class="form-input" placeholder="House / Area">
            </div>
            <div>
                <label class="form-label" style="font-size: 11px;">Credit Limit (৳)</label>
                <input type="number" id="modal_cust_limit" class="form-input" value="10000.00">
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeQuickCustomerModal()" style="flex: 1;">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitQuickCustomer()" style="flex: 1;">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<div id="receiptModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: var(--card, #fff); border-radius: 12px; padding: 1.5rem; max-width: 380px; width: 100%; text-align: center; box-shadow: 0 20px 25px rgba(0,0,0,0.2);">
        <div style="font-size: 2.25rem; margin-bottom: 0.25rem;">✅</div>
        <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Sale Completed!</h3>
        <p style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 4px;" id="receiptInvoiceText"></p>
        <div style="display: flex; gap: 0.5rem; margin-top: 1.25rem;">
            <a href="#" id="receiptPrintLink" target="_blank" class="btn btn-primary" style="flex: 1;">🖨️ Print Receipt</a>
            <button type="button" class="btn btn-secondary" onclick="closeReceiptModal()" style="flex: 1;">Next Sale</button>
        </div>
    </div>
</div>

<style>
    .pos-main-container {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 1rem;
        height: calc(100vh - 110px);
        align-items: stretch;
    }
    .pos-col-left {
        display: flex;
        flex-direction: column;
        min-height: 0;
        min-width: 0;
    }
    .pos-col-right {
        min-width: 0;
    }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @media (max-width: 1024px) {
        .pos-main-container {
            grid-template-columns: 1fr;
            height: auto;
        }
        .pos-products-card {
            max-height: 350px;
        }
    }
</style>

<script>
    const posProducts = @json($products);
    const vatRate = {{ $enableVat ? $vatPercent : 0 }};
    let cart = [];
    let customerPreviousDue = 0;

    // Toggle Split Payment box
    function toggleSplitPayment() {
        const box = document.getElementById('splitPaymentBox');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }

    // Customer AJAX search
    let custSearchTimer;
    function ajaxSearchPosCustomer(query) {
        clearTimeout(custSearchTimer);
        const box = document.getElementById('pos_cust_suggestions');

        if (!query.trim()) {
            box.style.display = 'none';
            return;
        }

        custSearchTimer = setTimeout(() => {
            fetch(`{{ route('pos.api.customers') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    box.innerHTML = '';
                    if (!data || data.length === 0) {
                        box.innerHTML = '<div style="padding: 6px 10px; font-size: 11px; color: var(--muted-foreground);">No customer found.</div>';
                    } else {
                        data.forEach(c => {
                            const item = document.createElement('div');
                            item.style.cssText = 'padding: 6px 10px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                            item.onmouseover = () => item.style.background = 'var(--muted, #f1f5f9)';
                            item.onmouseout = () => item.style.background = 'transparent';
                            item.innerHTML = `
                                <div>
                                    <strong>${c.name}</strong>
                                    <div style="font-size: 10px; color: var(--muted-foreground); font-family: monospace;">${c.phone}</div>
                                </div>
                                <span class="badge ${c.current_due > 0 ? 'badge-danger' : 'badge-success'}" style="font-size: 10px;">Due: ৳${parseFloat(c.current_due).toFixed(2)}</span>
                            `;
                            item.onclick = () => selectPosCustomerFromAjax(c);
                            box.appendChild(item);
                        });
                    }
                    box.style.display = 'block';
                });
        }, 200);
    }

    function selectPosCustomerFromAjax(c) {
        const select = document.getElementById('cart_customer_id');
        select.value = c.id;
        document.getElementById('pos_cust_search').value = `${c.name} (${c.phone})`;
        document.getElementById('pos_cust_suggestions').style.display = 'none';
        onPosCustomerSelectChange(select);
    }

    function onPosCustomerSelectChange(select) {
        const option = select.options[select.selectedIndex];
        customerPreviousDue = parseFloat(option.getAttribute('data-due')) || 0;
        
        const badge = document.getElementById('pos_cust_due_badge');
        const dueText = document.getElementById('pos_display_cust_due');
        const searchInput = document.getElementById('pos_cust_search');

        if (select.value) {
            const name = option.getAttribute('data-name');
            const phone = option.getAttribute('data-phone');
            if (name && phone) searchInput.value = `${name} (${phone})`;

            if (customerPreviousDue > 0) {
                dueText.textContent = `৳${customerPreviousDue.toFixed(2)}`;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        } else {
            searchInput.value = '';
            badge.style.display = 'none';
            customerPreviousDue = 0;
        }

        renderCartTotals();
    }

    function openQuickCustomerModal() {
        document.getElementById('quickCustomerModal').style.display = 'flex';
        document.getElementById('modal_cust_name').focus();
    }

    function closeQuickCustomerModal() {
        document.getElementById('quickCustomerModal').style.display = 'none';
    }

    function submitQuickCustomer() {
        const name = document.getElementById('modal_cust_name').value.trim();
        const phone = document.getElementById('modal_cust_phone').value.trim();
        const address = document.getElementById('modal_cust_address').value.trim();
        const limit = document.getElementById('modal_cust_limit').value;

        if (!name || !phone) {
            alert('কাস্টমারের নাম এবং মোবাইল নম্বর আবশ্যক!');
            return;
        }

        fetch(`{{ route('pos.api.quick-customer') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, phone, address, credit_limit: limit })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const c = data.customer;
                const select = document.getElementById('cart_customer_id');
                const newOpt = new Option(`${c.name} (${c.phone})`, c.id, true, true);
                newOpt.setAttribute('data-due', '0');
                newOpt.setAttribute('data-limit', c.credit_limit);
                newOpt.setAttribute('data-name', c.name);
                newOpt.setAttribute('data-phone', c.phone);
                select.add(newOpt);

                select.value = c.id;
                onPosCustomerSelectChange(select);
                closeQuickCustomerModal();
            } else {
                alert('কাস্টমার যোগ করতে সমস্যা হয়েছে।');
            }
        });
    }

    // Barcode & Product Grid
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
                alert('পণ্যটি পাওয়া যায়নি!');
            }
        }
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
    }

    function updateQty(idx, newQty) {
        const qty = parseFloat(newQty);
        if (qty <= 0) {
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
                    <td style="padding: 4px;">
                        <strong style="color: var(--foreground); font-size: 11.5px;">${item.name}</strong>
                        <div style="font-size: 9.5px; color: var(--muted-foreground);">৳${item.price.toFixed(2)} / ${item.unit}</div>
                    </td>
                    <td style="padding: 4px; text-align: center;">
                        <input type="number" step="0.1" value="${item.quantity}" min="0.1" class="form-input" style="height: 22px; padding: 1px 3px; font-size: 11px; text-align: center; width: 65px;" onchange="updateQty(${idx}, this.value)">
                    </td>
                    <td style="text-align: right; font-weight: 700; padding: 4px;">
                        ৳${(item.quantity * item.price).toFixed(2)}
                    </td>
                    <td style="text-align: center; padding: 4px 2px;">
                        <button type="button" onclick="removeFromCart(${idx})" style="border: none; background: none; color: var(--danger, #ef4444); cursor: pointer; font-size: 13px; padding: 0 4px;">✕</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        renderCartTotals();
    }

    // Totals & Paid Calculations
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
        if (document.getElementById('cartVat')) {
            document.getElementById('cartVat').textContent = `৳${vat.toFixed(2)}`;
        }
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

    function filterByCat(catId, btn) {
        document.querySelectorAll('.pos-cat-btn').forEach(b => b.className = 'btn btn-secondary pos-cat-btn');
        btn.className = 'btn btn-primary pos-cat-btn';

        document.querySelectorAll('.pos-prod-card').forEach(card => {
            if (catId === 'all' || card.getAttribute('data-cat') === catId) {
                card.style.display = 'block';
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
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function submitPosSale() {
        if (cart.length === 0) {
            alert('কার্টে কোনো পণ্য যোগ করা হয়নি!');
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

        const payload = {
            customer_id: document.getElementById('cart_customer_id').value,
            discount: discount,
            vat_percent: vatRate,
            cash_paid: cash > 0 ? cash : (totalPaid === 0 ? grandTotal : totalPaid),
            bkash_paid: bkash,
            bank_paid: bank,
            paid_amount: totalPaid > 0 ? totalPaid : grandTotal,
            items: cart.map(it => ({
                product_id: it.product_id,
                quantity: it.quantity,
                unit_price: it.price
            }))
        };

        fetch(`{{ route('pos.checkout') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('receiptInvoiceText').textContent = `Invoice: ${data.invoice_no} • Total: ৳${data.grand_total}`;
                document.getElementById('receiptPrintLink').href = data.receipt_url;
                document.getElementById('receiptModal').style.display = 'flex';
                clearCart();
                document.getElementById('cartPaidAmount').value = '';
                document.getElementById('pay_cash').value = '';
                document.getElementById('pay_bkash').value = '';
                document.getElementById('pay_bank').value = '';
            } else {
                alert(data.message || 'বিক্রয় সম্পন্ন হতে সমস্যা হয়েছে।');
            }
        })
        .catch(err => alert('Error processing sale.'));
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').style.display = 'none';
        document.getElementById('pos_barcode_input').focus();
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#pos_cust_search') && !e.target.closest('#pos_cust_suggestions')) {
            document.getElementById('pos_cust_suggestions').style.display = 'none';
        }
    });
</script>
@endsection