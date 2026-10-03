@extends('tyro-dashboard::layouts.admin')

@section('title', 'Create Quotation / Estimate')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('quotations.index') }}">Quotations</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create Quotation / Estimate (দরপত্র / মেমো তৈরি)</h1>
            <p class="page-description">Search products by name/barcode or pick from dropdown to prepare price quotations.</p>
        </div>
        <a href="{{ route('quotations.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<form action="{{ route('quotations.store') }}" method="POST">
    @csrf

    <!-- 1. Header Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                
                <div class="form-group">
                    <label class="form-label" for="quo_cust_id">Customer (ক্রেতা)</label>
                    <select id="quo_cust_id" name="customer_id" class="form-select">
                        <option value="">-- General Client (সাধারণ ক্রেতা) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="quotation_no">Quotation No <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="quotation_no" class="form-input" value="{{ $autoNo }}" required style="font-family: monospace;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="quotation_date">Date <span style="color: var(--danger);">*</span></label>
                    <input type="date" name="quotation_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Barcode & Fast Product Live Search -->
    <div class="card" style="margin-bottom: 1.5rem; border: 2px dashed var(--primary); background: rgba(14, 165, 233, 0.03);">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">📷</span>
                <div style="flex: 1; position: relative;">
                    <input type="text" id="quo_barcode_input" class="form-input" placeholder="Type product name, SKU or scan barcode and press Enter..." style="font-size: 0.95rem; padding: 0.65rem 1rem;" onkeydown="handleQuoBarcode(event)" oninput="liveSearchQuoProduct(this.value)">
                    
                    <!-- Search Suggestions Box -->
                    <div id="quo_product_suggestions" style="display: none; position: absolute; top: calc(100% + 2px); left: 0; right: 0; background: var(--card, #ffffff); border: 1px solid var(--border, #e2e8f0); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 100; max-height: 220px; overflow-y: auto;"></div>
                </div>
                <button type="button" class="btn btn-primary" onclick="addQuoRow()">+ Add Product Row</button>
            </div>
        </div>
    </div>

    <!-- 3. Items Table -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
            <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Quotation Items</h3>
            <span class="badge badge-primary" id="quo_items_badge">0 Products</span>
        </div>
        <div class="table-container">
            <table class="table" id="quoTable">
                <thead>
                    <tr>
                        <th style="min-width: 280px;">Product Name</th>
                        <th style="width: 140px;">Quantity</th>
                        <th style="width: 150px;">Unit Price (৳)</th>
                        <th style="width: 130px;">Discount / Comm (৳)</th>
                        <th style="width: 150px; text-align: right;">Line Total</th>
                        <th style="width: 50px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="quoBody"></tbody>
            </table>
        </div>
    </div>

    <!-- 4. Summary & Save -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="notes">Notes / Terms</label>
                    <textarea id="notes" name="notes" class="form-textarea" rows="4" placeholder="দরপত্রের শর্তাবলী..."></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.875rem;">
                    <span>Subtotal:</span>
                    <strong id="displayQuoSubtotal">৳0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">Overall Discount (-):</span>
                    <input type="number" step="0.01" id="quo_discount" name="discount" value="0.00" class="form-input" style="width: 120px; text-align: right;" oninput="recalcQuoTotals()">
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-top: 2px solid var(--border); font-size: 1.15rem; font-weight: 800;">
                    <span>Estimated Total:</span>
                    <strong style="color: var(--primary);" id="displayQuoGrandTotal">৳0.00</strong>
                </div>
                <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 800;">
                        Save Quotation / Estimate
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const quoProductsList = @json($products);
    let qRowIdx = 0;

    // 🔍 Live Product Typing Search (Search by Name, Barcode, or SKU)
    let quoSearchTimer;
    function liveSearchQuoProduct(query) {
        clearTimeout(quoSearchTimer);
        const box = document.getElementById('quo_product_suggestions');

        if (!query.trim()) {
            box.style.display = 'none';
            return;
        }

        quoSearchTimer = setTimeout(() => {
            const q = query.toLowerCase().trim();
            const matches = quoProductsList.filter(p => 
                (p.name && p.name.toLowerCase().includes(q)) ||
                (p.name_en && p.name_en.toLowerCase().includes(q)) ||
                (p.barcode && p.barcode.includes(q)) ||
                (p.sku && p.sku.toLowerCase().includes(q))
            );

            box.innerHTML = '';
            if (matches.length > 0) {
                matches.slice(0, 10).forEach(p => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 13px; display: flex; justify-content: space-between; align-items: center;';
                    item.onmouseover = () => item.style.background = 'var(--muted, #f1f5f9)';
                    item.onmouseout = () => item.style.background = 'transparent';
                    item.innerHTML = `
                        <div>
                            <strong>${p.name}</strong>
                            <span style="font-size: 11px; color: var(--muted-foreground); font-family: monospace;">(${p.barcode})</span>
                        </div>
                        <span style="font-weight: 700; color: var(--primary);">৳${p.selling_price}</span>
                    `;
                    item.onclick = () => {
                        addQuoRow(p);
                        document.getElementById('quo_barcode_input').value = '';
                        box.style.display = 'none';
                    };
                    box.appendChild(item);
                });
                box.style.display = 'block';
            } else {
                box.innerHTML = '<div style="padding: 8px 12px; font-size: 12px; color: var(--muted-foreground);">No product found.</div>';
                box.style.display = 'block';
            }
        }, 150);
    }

    function handleQuoBarcode(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = e.target.value.trim().toLowerCase();
            if (!code) return;

            const product = quoProductsList.find(p => 
                p.barcode === code || 
                (p.name && p.name.toLowerCase().includes(code)) ||
                (p.name_en && p.name_en.toLowerCase().includes(code))
            );

            if (product) {
                addQuoRow(product);
                e.target.value = '';
                document.getElementById('quo_product_suggestions').style.display = 'none';
            } else {
                alert('পণ্যটি পাওয়া যায়নি!');
            }
        }
    }

    function addQuoRow(preselected = null) {
        const tbody = document.getElementById('quoBody');
        const tr = document.createElement('tr');
        tr.id = `qrow-${qRowIdx}`;

        let options = '<option value="">-- Choose Product from List --</option>';
        quoProductsList.forEach(p => {
            const isSel = preselected && preselected.id == p.id ? 'selected' : '';
            options += `<option value="${p.id}" data-price="${p.selling_price}" data-unit="${p.unit ? p.unit.short_code : 'pc'}" ${isSel}>${p.name} (${p.barcode})</option>`;
        });

        const initialPrice = preselected ? parseFloat(preselected.selling_price).toFixed(2) : '0.00';
        const initialUnit = preselected && preselected.unit ? preselected.unit.short_code : 'pc';

        tr.innerHTML = `
            <td>
                <select name="items[${qRowIdx}][product_id]" class="form-select" required onchange="onQuoSelectChanged(${qRowIdx}, this)">
                    ${options}
                </select>
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <input type="number" step="0.1" name="items[${qRowIdx}][quantity]" id="qqty-${qRowIdx}" value="1" min="0.1" class="form-input" required oninput="recalcQuoRow(${qRowIdx})">
                    <span id="qunit-${qRowIdx}" style="font-size: 11px; color: var(--muted-foreground);">${initialUnit}</span>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${qRowIdx}][unit_price]" id="qprice-${qRowIdx}" value="${initialPrice}" class="form-input" required oninput="recalcQuoRow(${qRowIdx})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${qRowIdx}][commission]" id="qcomm-${qRowIdx}" value="0.00" class="form-input" oninput="recalcQuoRow(${qRowIdx})">
            </td>
            <td style="text-align: right; font-weight: 700;">
                <span id="qtotal-${qRowIdx}">৳${initialPrice}</span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="action-btn action-btn-danger" onclick="document.getElementById('qrow-${qRowIdx}').remove(); recalcQuoTotals();">✕</button>
            </td>
        `;
        tbody.appendChild(tr);
        qRowIdx++;
        recalcQuoTotals();
    }

    function onQuoSelectChanged(idx, select) {
        const opt = select.options[select.selectedIndex];
        const price = parseFloat(opt.getAttribute('data-price')) || 0;
        const unit = opt.getAttribute('data-unit') || 'pc';

        document.getElementById(`qprice-${idx}`).value = price.toFixed(2);
        document.getElementById(`qunit-${idx}`).textContent = unit;
        recalcQuoRow(idx);
    }

    function recalcQuoRow(idx) {
        const qty = parseFloat(document.getElementById(`qqty-${idx}`)?.value) || 0;
        const price = parseFloat(document.getElementById(`qprice-${idx}`)?.value) || 0;
        const comm = parseFloat(document.getElementById(`qcomm-${idx}`)?.value) || 0;
        const total = Math.max(0, (qty * price) - comm);
        document.getElementById(`qtotal-${idx}`).textContent = `৳${total.toFixed(2)}`;
        recalcQuoTotals();
    }

    function recalcQuoTotals() {
        let subtotal = 0;
        let count = 0;
        document.querySelectorAll('#quoBody tr').forEach(tr => {
            const idx = tr.id.replace('qrow-', '');
            const qty = parseFloat(document.getElementById(`qqty-${idx}`)?.value) || 0;
            const price = parseFloat(document.getElementById(`qprice-${idx}`)?.value) || 0;
            const comm = parseFloat(document.getElementById(`qcomm-${idx}`)?.value) || 0;
            subtotal += Math.max(0, (qty * price) - comm);
            count++;
        });

        document.getElementById('quo_items_badge').textContent = `${count} Products`;
        document.getElementById('displayQuoSubtotal').textContent = `৳${subtotal.toFixed(2)}`;
        const discount = parseFloat(document.getElementById('quo_discount')?.value) || 0;
        const grand = Math.max(0, subtotal - discount);
        document.getElementById('displayQuoGrandTotal').textContent = `৳${grand.toFixed(2)}`;
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#quo_barcode_input') && !e.target.closest('#quo_product_suggestions')) {
            document.getElementById('quo_product_suggestions').style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', () => addQuoRow());
</script>
@endsection