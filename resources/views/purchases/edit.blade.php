@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Purchase #' . $purchase->chalan_no)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('purchases.index') }}">Purchases</a>
<span class="breadcrumb-separator">/</span>
<span>Edit #{{ $purchase->chalan_no }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Purchase Invoice (চালান সংশোধন)</h1>
            <p class="page-description">Modify items, rates, quantities, or payments. Inventory will be automatically reconciled.</p>
        </div>
        <a href="{{ route('purchases.show', ['purchase' => $purchase->id]) }}" class="btn btn-secondary">← Back to Invoice</a>
    </div>
</div>

<form action="{{ route('purchases.update', ['purchase' => $purchase->id]) }}" method="POST" id="purchaseForm">
    @csrf
    @method('PUT')

    <!-- 1. Header Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                
                <div class="form-group">
                    <label class="form-label" for="supplier_id">Supplier (মহাজন) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="supplier_id" name="supplier_id" class="form-select" required onchange="handleSupplierChange(this)">
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-due="{{ $s->current_due }}" {{ $purchase->supplier_id == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->company_name ?? 'Dealer' }}) - Due: ৳{{ number_format($s->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="chalan_no">Chalan / Memo No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="chalan_no" name="chalan_no" class="form-input" value="{{ old('chalan_no', $purchase->chalan_no) }}" required style="font-family: monospace;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="purchase_date">Purchase Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Receiving Branch</label>
                    <input type="text" class="form-input" value="{{ $activeBranch?->name ?? 'Main Branch' }}" disabled style="background: var(--muted, rgba(148, 163, 184, 0.08)); font-weight: 600;">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Items Table Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Purchased Items List</h3>
            <button type="button" class="btn btn-primary" onclick="addEmptyRow()">+ Add Product Row</button>
        </div>
        <div class="table-container">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="min-width: 280px;">Product Description</th>
                        <th style="width: 140px;">Quantity</th>
                        <th style="width: 140px;">Unit Cost (৳)</th>
                        <th style="width: 130px;">Commission (৳)</th>
                        <th style="width: 140px; text-align: right;">Line Total</th>
                        <th style="width: 50px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <!-- Populated automatically from existing items -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Financial Summary -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
        
        <div class="card">
            <div class="card-body">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash" {{ $purchase->payment_method === 'cash' ? 'selected' : '' }}>ক্যাশ ড্রয়ার (Cash)</option>
                        <option value="bank" {{ $purchase->payment_method === 'bank' ? 'selected' : '' }}>ব্যাংক একাউন্ট (Bank)</option>
                        <option value="bkash" {{ $purchase->payment_method === 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                        <option value="nagad" {{ $purchase->payment_method === 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-textarea" rows="4">{{ old('notes', $purchase->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 1.25rem;">
                
                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.875rem;">
                    <span>Total Quantity:</span>
                    <strong id="displayTotalQty">0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.875rem;">
                    <span>Subtotal:</span>
                    <strong id="displaySubtotal">৳0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">Discount (-):</span>
                    <input type="number" step="0.01" id="discount" name="discount" value="{{ old('discount', $purchase->discount) }}" class="form-input" style="width: 120px; text-align: right;" oninput="calculateTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">Labor / Freight (+):</span>
                    <input type="number" step="0.01" id="transport_cost" name="transport_cost" value="{{ old('transport_cost', $purchase->transport_cost) }}" class="form-input" style="width: 120px; text-align: right;" oninput="calculateTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem;">VAT (%):</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="number" step="0.1" id="vat_percent" name="vat_percent" value="{{ old('vat_percent', $purchase->vat_percent) }}" class="form-input" style="width: 70px; text-align: right;" oninput="calculateTotals()">
                        <span id="displayVatAmount" style="font-size: 0.8125rem; color: var(--muted-foreground); min-width: 60px; text-align: right;">(৳0.00)</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.65rem 0; border-top: 1px solid var(--border); font-size: 1rem; font-weight: 700;">
                    <span>Invoice Total:</span>
                    <strong id="displayGrandTotal" style="color: var(--primary);">৳0.00</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0;">
                    <span style="font-size: 0.875rem; font-weight: 700;">Paid Amount:</span>
                    <input type="number" step="0.01" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $purchase->paid_amount) }}" class="form-input" style="width: 140px; text-align: right; font-weight: 700; color: var(--success, #10b981);" oninput="calculateTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; padding: 0.65rem 0; font-size: 1.05rem; font-weight: 800; color: var(--danger, #ef4444); border-top: 2px solid var(--border);">
                    <span>Remaining Due (বাকি):</span>
                    <strong id="displayDue">৳0.00</strong>
                </div>

                <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; font-weight: 700;">
                        Update Purchase & Recalculate Stock
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const allProducts = @json($products);
    const existingItems = @json($purchase->items);
    let rowIndex = 0;

    function renderRow(item = null) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        let options = '<option value="">-- Choose Product --</option>';
        allProducts.forEach(p => {
            const isSelected = item && item.product_id == p.id ? 'selected' : '';
            options += `<option value="${p.id}" data-cost="${p.purchase_price}" data-unit="${p.unit ? p.unit.short_code : 'pc'}" ${isSelected}>
                ${p.name} (${p.barcode})
            </option>`;
        });

        const qty = item ? item.quantity : 1;
        const cost = item ? parseFloat(item.unit_cost).toFixed(2) : '0.00';
        const comm = item ? parseFloat(item.commission).toFixed(2) : '0.00';
        const unit = item && item.unit ? item.unit.short_code : 'pc';

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select" required onchange="onProductSelect(${rowIndex}, this)">
                    ${options}
                </select>
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <input type="number" step="0.01" name="items[${rowIndex}][quantity]" id="qty-${rowIndex}" class="form-input row-qty" value="${qty}" min="0.01" required oninput="recalcRow(${rowIndex})">
                    <span id="unit-label-${rowIndex}" style="font-size: 11px; color: var(--muted-foreground);">${unit}</span>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" id="cost-${rowIndex}" class="form-input row-cost" value="${cost}" required oninput="recalcRow(${rowIndex})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][commission]" id="comm-${rowIndex}" class="form-input row-comm" value="${comm}" oninput="recalcRow(${rowIndex})">
            </td>
            <td style="text-align: right; font-weight: 700;">
                <span id="line-total-${rowIndex}" class="row-total">৳0.00</span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="action-btn action-btn-danger" onclick="removeRow(${rowIndex})">✕</button>
            </td>
        `;

        tbody.appendChild(tr);
        recalcRow(rowIndex);
        rowIndex++;
    }

    function addEmptyRow() {
        renderRow(null);
    }

    function onProductSelect(idx, select) {
        const option = select.options[select.selectedIndex];
        const cost = parseFloat(option.getAttribute('data-cost')) || 0;
        const unit = option.getAttribute('data-unit') || 'pc';

        document.getElementById(`cost-${idx}`).value = cost.toFixed(2);
        document.getElementById(`unit-label-${idx}`).textContent = unit;

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

    function calculateTotals() {
        let subtotal = 0;
        let totalQty = 0;

        document.querySelectorAll('#itemsBody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.row-qty')?.value) || 0;
            const cost = parseFloat(tr.querySelector('.row-cost')?.value) || 0;
            const comm = parseFloat(tr.querySelector('.row-comm')?.value) || 0;

            subtotal += Math.max(0, (qty * cost) - comm);
            totalQty += qty;
        });

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

        const paid = parseFloat(document.getElementById('paid_amount')?.value) || 0;
        const currentDue = Math.max(0, grandTotal - paid);
        document.getElementById('displayDue').textContent = `৳${currentDue.toFixed(2)}`;
    }

    // Populate existing purchase items on load
    document.addEventListener('DOMContentLoaded', () => {
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(item => renderRow(item));
        } else {
            renderRow(null);
        }
    });
</script>
@endsection