@extends('tyro-dashboard::layouts.admin')

@section('title', 'New Purchase Invoice')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('purchases.index') }}">Purchases</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">New Purchase / Stock-In (পণ্য ক্রয় ও চালান এন্ট্রি)</h1>
            <p class="page-description">Search supplier via AJAX or choose from dropdown, scan barcodes, and enter purchase items.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">← Back to List</a>
    </div>
</div>

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf

    <!-- 1. Header Card: Dual Supplier Selector (Search Left + Dropdown Right) -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            
            <!-- Supplier Row: Side-by-Side Dual Selectors -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                
                <!-- LEFT: AJAX Live Search -->
                <div class="form-group" style="position: relative;">
                    <label class="form-label" for="supplier_ajax_input">
                        🔍 Option 1: Search Supplier (নাম বা ফোন দিয়ে সার্চ)
                    </label>
                    <input type="text" id="supplier_ajax_input" class="form-input" placeholder="Type name or phone number..." autocomplete="off" oninput="ajaxSearchSupplier(this.value)">
                    
                    <!-- Search Results Popup -->
                    <div id="supplier_ajax_results" style="display: none; position: absolute; top: calc(100% + 2px); left: 0; right: 0; background: var(--card, #ffffff); border: 1px solid var(--border, #e2e8f0); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 100; max-height: 220px; overflow-y: auto;">
                    </div>
                </div>

                <!-- RIGHT: Manual Dropdown Selector -->
                <div class="form-group">
                    <label class="form-label" for="supplier_id">
                        📋 Option 2: Or Select Supplier from Dropdown <span style="color: var(--danger, #ef4444);">*</span>
                    </label>
                    <select id="supplier_id" name="supplier_id" class="form-select" required onchange="onSupplierDropdownChanged(this)">
                        <option value="" data-due="0">-- Choose Supplier from List --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-due="{{ $s->current_due }}" data-phone="{{ $s->phone }}" data-company="{{ $s->company_name }}" data-name="{{ $s->name }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->company_name ?? 'Dealer' }}) - Due: ৳{{ number_format($s->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- Chalan Meta Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="chalan_no">Chalan / Memo No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="chalan_no" name="chalan_no" class="form-input" value="{{ old('chalan_no', $autoChalan) }}" required style="font-family: monospace;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="purchase_date">Purchase Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Receiving Branch</label>
                    <input type="text" class="form-input" value="{{ $activeBranch?->name ?? 'Main Branch' }}" disabled style="background: var(--muted, rgba(148, 163, 184, 0.08)); font-weight: 600;">
                </div>
            </div>

        </div>
    </div>

    <!-- 2. Barcode Scanner & Search Box -->
    <div class="card" style="margin-bottom: 1.5rem; border: 2px dashed var(--primary, #0ea5e9); background: rgba(14, 165, 233, 0.03);">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">📷</span>
                <div style="flex: 1; position: relative;">
                    <input type="text" id="barcode_input" class="form-input" placeholder="Scan Barcode with Gun or type product name/SKU and press Enter..." autofocus style="font-size: 0.95rem; padding: 0.65rem 1rem;" onkeydown="handleBarcodeEnter(event)" oninput="liveSearchProduct(this.value)">
                    <div id="product_suggestions" style="display: none; position: absolute; top: calc(100% + 2px); left: 0; right: 0; background: var(--card, #ffffff); border: 1px solid var(--border, #e2e8f0); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 100; max-height: 240px; overflow-y: auto;"></div>
                </div>
                <button type="button" class="btn btn-primary" onclick="addEmptyRowWithDropdown()">
                    + Add Product Row
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Purchased Items Table -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Purchased Items List</h3>
            <span class="badge badge-primary" id="badge_total_items">0 Products</span>
        </div>
        <div class="table-container">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="min-width: 280px;">Select Product</th>
                        <th style="width: 120px; text-align: center;">Stock</th>
                        <th style="width: 140px;">Quantity</th>
                        <th style="width: 140px;">Unit Cost (৳)</th>
                        <th style="width: 130px;">Commission (৳)</th>
                        <th style="width: 140px; text-align: right;">Line Total</th>
                        <th style="width: 50px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Pricing, Taxes & Dues Calculations -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
        
        <!-- Left: Payment details -->
        <div class="card">
            <div class="card-body">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">ক্যাশ ড্রয়ার (Cash Drawer)</option>
                        <option value="bank">ব্যাংক একাউন্ট (Bank Account)</option>
                        <option value="bkash">বিকাশ মার্চেন্ট (bKash)</option>
                        <option value="nagad">নগদ (Nagad)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="notes">Notes / Special Instructions</label>
                    <textarea id="notes" name="notes" class="form-textarea" rows="4" placeholder="চালানের বিশেষ মন্তব্য..."></textarea>
                </div>
            </div>
        </div>

        <!-- Right: Summary Calculator -->
        <div class="card">
            <div class="card-body" style="padding: 1.25rem;">
                
                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.875rem;">
                    <span>Total Quantity (মোট পরিমাণ):</span>
                    <strong id="displayTotalQty">0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.875rem;">
                    <span>Subtotal:</span>
                    <strong id="displaySubtotal">৳0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">Discount (-):</span>
                    <input type="number" step="0.01" id="discount" name="discount" value="0.00" class="form-input" style="width: 120px; text-align: right;" oninput="calculateTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">Labor / Freight (+):</span>
                    <input type="number" step="0.01" id="transport_cost" name="transport_cost" value="0.00" class="form-input" style="width: 120px; text-align: right;" oninput="calculateTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">VAT / Tax Rate (%):</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="number" step="0.1" id="vat_percent" name="vat_percent" value="0.0" class="form-input" style="width: 70px; text-align: right;" oninput="calculateTotals()">
                        <span id="displayVatAmount" style="font-size: 0.8125rem; color: var(--muted-foreground); min-width: 60px; text-align: right;">(৳0.00)</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.65rem 0; border-top: 1px solid var(--border); font-size: 1rem; font-weight: 700;">
                    <span>Invoice Total (চালান মূল্য):</span>
                    <strong id="displayGrandTotal" style="color: var(--primary);">৳0.00</strong>
                </div>

                <!-- Previous Due Row -->
                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.875rem; color: var(--danger, #ef4444); background: rgba(239, 68, 68, 0.05); padding: 4px 6px; border-radius: 4px;">
                    <span>Previous Due (পূর্বের বাকি):</span>
                    <strong id="displayPreviousDue">৳0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.65rem 0; font-size: 1.125rem; font-weight: 800; border-top: 2px solid var(--border);">
                    <span>Net Total Payable (সর্বমোট দেয়):</span>
                    <strong id="displayNetPayable">৳0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem; font-weight: 700;">Paid Amount:</span>
                    <input type="number" step="0.01" id="paid_amount" name="paid_amount" value="0.00" class="form-input" style="width: 140px; text-align: right; font-weight: 700; color: var(--success, #10b981);" oninput="calculateTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.65rem 0; font-size: 1.05rem; font-weight: 800; color: var(--danger, #ef4444);">
                    <span>Current Due (বর্তমান বাকি):</span>
                    <strong id="displayDue">৳0.00</strong>
                </div>

                <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 700;">
                        Complete Purchase & Stock-In
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const allProducts = @json($products);
    let rowIndex = 0;
    let supplierPreviousDue = 0;

    // ========================================================
    // 1. DUAL SUPPLIER SELECTOR (Search Left <-> Dropdown Right)
    // ========================================================
    let supSearchTimer;
    function ajaxSearchSupplier(query) {
        clearTimeout(supSearchTimer);
        const box = document.getElementById('supplier_ajax_results');

        if (!query.trim()) {
            box.style.display = 'none';
            return;
        }

        supSearchTimer = setTimeout(() => {
            fetch(`{{ route('purchases.api.suppliers') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    box.innerHTML = '';
                    if (!data || data.length === 0) {
                        box.innerHTML = '<div style="padding: 8px 12px; font-size: 12px; color: var(--muted-foreground);">No supplier found.</div>';
                    } else {
                        data.forEach(s => {
                            const item = document.createElement('div');
                            item.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 13px; display: flex; justify-content: space-between; align-items: center;';
                            item.onmouseover = () => item.style.background = 'var(--muted, #f1f5f9)';
                            item.onmouseout = () => item.style.background = 'transparent';
                            item.innerHTML = `
                                <div>
                                    <strong>${s.name}</strong> (${s.company_name || 'Dealer'})
                                    <div style="font-size: 11px; color: var(--muted-foreground); font-family: monospace;">${s.phone}</div>
                                </div>
                                <span class="badge ${s.current_due > 0 ? 'badge-danger' : 'badge-success'}">Due: ৳${parseFloat(s.current_due).toFixed(2)}</span>
                            `;
                            item.onclick = () => selectFromAjax(s);
                            box.appendChild(item);
                        });
                    }
                    box.style.display = 'block';
                });
        }, 200);
    }

    // When clicked from AJAX search -> automatically populates the right dropdown!
    function selectFromAjax(s) {
        const select = document.getElementById('supplier_id');
        select.value = s.id;
        document.getElementById('supplier_ajax_input').value = `${s.name} (${s.phone})`;
        document.getElementById('supplier_ajax_results').style.display = 'none';

        onSupplierDropdownChanged(select);
    }

    // When picked directly from the right-hand dropdown!
    function onSupplierDropdownChanged(select) {
        const option = select.options[select.selectedIndex];
        supplierPreviousDue = parseFloat(option.getAttribute('data-due')) || 0;
        
        const name = option.getAttribute('data-name');
        const phone = option.getAttribute('data-phone');
        if (name && phone) {
            document.getElementById('supplier_ajax_input').value = `${name} (${phone})`;
        } else if (!select.value) {
            document.getElementById('supplier_ajax_input').value = '';
        }

        document.getElementById('displayPreviousDue').textContent = `৳${supplierPreviousDue.toFixed(2)}`;
        calculateTotals();
    }

    // ========================================================
    // 2. PRODUCT SCANNER & FAST AJAX SEARCH
    // ========================================================
    let prodSearchTimer;
    function liveSearchProduct(query) {
        clearTimeout(prodSearchTimer);
        const box = document.getElementById('product_suggestions');

        if (!query.trim()) {
            box.style.display = 'none';
            return;
        }

        prodSearchTimer = setTimeout(() => {
            fetch(`{{ route('purchases.api.products') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    box.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(p => {
                            const item = document.createElement('div');
                            item.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 13px; display: flex; justify-content: space-between; align-items: center;';
                            item.onmouseover = () => item.style.background = 'var(--muted, #f1f5f9)';
                            item.onmouseout = () => item.style.background = 'transparent';
                            item.innerHTML = `
                                <div>
                                    <strong>${p.name}</strong>
                                    <span style="font-size: 11px; color: var(--muted-foreground); font-family: monospace;">(${p.barcode})</span>
                                </div>
                                <div>
                                    <span style="font-weight: 700; color: var(--primary);">৳${p.purchase_price}</span>
                                    <span class="badge badge-secondary" style="margin-left: 6px;">Stock: ${p.current_stock}</span>
                                </div>
                            `;
                            item.onclick = () => {
                                addOrIncrementProduct(p);
                                document.getElementById('barcode_input').value = '';
                                box.style.display = 'none';
                            };
                            box.appendChild(item);
                        });
                        box.style.display = 'block';
                    } else {
                        box.style.display = 'none';
                    }
                });
        }, 200);
    }

    function handleBarcodeEnter(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = e.target.value.trim();
            if (!barcode) return;

            fetch(`{{ route('purchases.api.products') }}?q=${encodeURIComponent(barcode)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        addOrIncrementProduct(data[0]);
                        e.target.value = '';
                        document.getElementById('product_suggestions').style.display = 'none';
                    } else {
                        alert('পণ্যটি ক্যাটালগে পাওয়া যায়নি।');
                    }
                });
        }
    }

    function addOrIncrementProduct(p) {
        // If product already exists in table -> increment quantity by 1
        const existingRow = document.querySelector(`tr[data-product-id="${p.id}"]`);
        if (existingRow) {
            const qtyInput = existingRow.querySelector('.row-qty');
            qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
            
            existingRow.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
            setTimeout(() => existingRow.style.backgroundColor = 'transparent', 400);

            const rowId = existingRow.id.replace('row-', '');
            recalcRow(rowId);
            return;
        }

        // Add new row with this product pre-filled
        appendRow(p);
    }

    // ========================================================
    // 3. ROW PICKER (Manual Row Add with Dropdown)
    // ========================================================
    function addEmptyRowWithDropdown() {
        appendRow(null); // Adds fresh blank row ready for selection
    }

    function appendRow(preselected = null) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        let options = '<option value="">-- Choose Product from List --</option>';
        allProducts.forEach(p => {
            const isSelected = preselected && preselected.id == p.id ? 'selected' : '';
            options += `<option value="${p.id}" data-cost="${p.purchase_price}" data-unit="${p.unit ? p.unit.short_code : 'pc'}" data-stock="${p.current_stock ?? 0}" ${isSelected}>
                ${p.name} (${p.barcode})
            </option>`;
        });

        const initialCost = preselected ? parseFloat(preselected.purchase_price).toFixed(2) : '0.00';
        const initialUnit = preselected && preselected.unit ? preselected.unit.short_code : (preselected ? preselected.unit_name : 'pc');
        const initialStock = preselected ? (preselected.current_stock ?? 0) : '0';

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select row-product-select" required onchange="onRowProductSelected(${rowIndex}, this)">
                    ${options}
                </select>
            </td>
            <td style="text-align: center;">
                <span class="badge badge-secondary" id="stock-badge-${rowIndex}">${initialStock} ${initialUnit}</span>
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <input type="number" step="0.01" name="items[${rowIndex}][quantity]" id="qty-${rowIndex}" class="form-input row-qty" value="1" min="0.01" required oninput="recalcRow(${rowIndex})">
                    <span id="unit-label-${rowIndex}" style="font-size: 11px; color: var(--muted-foreground);">${initialUnit}</span>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" id="cost-${rowIndex}" class="form-input row-cost" value="${initialCost}" required oninput="recalcRow(${rowIndex})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][commission]" id="comm-${rowIndex}" class="form-input row-comm" value="0.00" oninput="recalcRow(${rowIndex})">
            </td>
            <td style="text-align: right; font-weight: 700;">
                <span id="line-total-${rowIndex}" class="row-total">৳${initialCost}</span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="action-btn action-btn-danger" onclick="removeRow(${rowIndex})">✕</button>
            </td>
        `;

        tbody.appendChild(tr);

        if (preselected) {
            tr.setAttribute('data-product-id', preselected.id);
        }

        rowIndex++;
        calculateTotals();
    }

    function onRowProductSelected(idx, select) {
        const option = select.options[select.selectedIndex];
        const cost = parseFloat(option.getAttribute('data-cost')) || 0;
        const unit = option.getAttribute('data-unit') || 'pc';
        const stock = option.getAttribute('data-stock') || '0';

        document.getElementById(`cost-${idx}`).value = cost.toFixed(2);
        document.getElementById(`unit-label-${idx}`).textContent = unit;
        document.getElementById(`stock-badge-${idx}`).textContent = `${stock} ${unit}`;

        const tr = document.getElementById(`row-${idx}`);
        if (tr) tr.setAttribute('data-product-id', select.value);

        recalcRow(idx);
    }

    function recalcRow(idx) {
        const qty = parseFloat(document.getElementById(`qty-${idx}`)?.value) || 0;
        const cost = parseFloat(document.getElementById(`cost-${idx}`)?.value) || 0;
        const comm = parseFloat(document.getElementById(`comm-${idx}`)?.value) || 0;

        const total = Math.max(0, (qty * cost) - comm);
        const lineTotal = document.getElementById(`line-total-${idx}`);
        if (lineTotal) lineTotal.textContent = `৳${total.toFixed(2)}`;

        calculateTotals();
    }

    function removeRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (row) row.remove();
        calculateTotals();
    }

    // ========================================================
    // 4. TOTALS & FINANCIAL SUMMARY
    // ========================================================
    function calculateTotals() {
        let subtotal = 0;
        let totalQty = 0;
        let count = 0;

        document.querySelectorAll('#itemsBody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.row-qty')?.value) || 0;
            const cost = parseFloat(tr.querySelector('.row-cost')?.value) || 0;
            const comm = parseFloat(tr.querySelector('.row-comm')?.value) || 0;

            subtotal += Math.max(0, (qty * cost) - comm);
            totalQty += qty;
            count++;
        });

        document.getElementById('badge_total_items').textContent = `${count} Products`;
        document.getElementById('displayTotalQty').textContent = totalQty.toFixed(2);
        document.getElementById('displaySubtotal').textContent = `৳${subtotal.toFixed(2)}`;

        const discount = parseFloat(document.getElementById('discount')?.value) || 0;
        const transport = parseFloat(document.getElementById('transport_cost')?.value) || 0;
        const vatPercent = parseFloat(document.getElementById('vat_percent')?.value) || 0;

        const baseAmount = Math.max(0, subtotal - discount);
        const vatAmount = (baseAmount * vatPercent) / 100;
        document.getElementById('displayVatAmount').textContent = `(৳${vatAmount.toFixed(2)})`;

        const grandTotal = baseAmount + transport + vatAmount;
        document.getElementById('displayGrandTotal').textContent = `৳${grandTotal.toFixed(2)}`;

        const netPayable = grandTotal + supplierPreviousDue;
        document.getElementById('displayNetPayable').textContent = `৳${netPayable.toFixed(2)}`;

        const paid = parseFloat(document.getElementById('paid_amount')?.value) || 0;
        const currentDue = Math.max(0, netPayable - paid);
        document.getElementById('displayDue').textContent = `৳${currentDue.toFixed(2)}`;
    }

    // Close popups on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#supplier_ajax_input') && !e.target.closest('#supplier_ajax_results')) {
            document.getElementById('supplier_ajax_results').style.display = 'none';
        }
        if (!e.target.closest('#barcode_input') && !e.target.closest('#product_suggestions')) {
            document.getElementById('product_suggestions').style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        addEmptyRowWithDropdown();
    });
</script>
@endsection