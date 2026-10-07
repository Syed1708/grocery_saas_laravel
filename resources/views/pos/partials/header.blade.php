<div class="card pos-top-bar" style="margin-bottom: 0.75rem; border: 1px solid var(--border); background: var(--card);">
    <div class="card-body" style="padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.65rem;">
        
        <!-- Row 1: Branch Info & Shortcuts -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #10b981); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; color: #fff; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);">
                    🏪
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <strong style="font-size: 0.95rem; color: var(--foreground);">{{ $activeBranch?->name ?? 'Main Branch' }}</strong>
                        <span class="badge badge-success" style="font-size: 9px; padding: 2px 6px;">● ONLINE</span>
                    </div>
                    <div style="font-size: 11px; color: var(--muted-foreground);">
                        Cashier: <strong style="color: var(--foreground);">{{ auth()->user()->name }}</strong>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="badge badge-secondary" style="font-family: monospace; font-size: 10px; padding: 4px 8px;">[F2] Complete Sale</span>
                <span class="badge badge-secondary" style="font-family: monospace; font-size: 10px; padding: 4px 8px;">[/] Scan Barcode</span>
            </div>
        </div>

        <!-- Row 2: Full-Width Customer Selection Bar -->
        <div style="display: flex; align-items: center; gap: 0.65rem; width: 100%; position: relative;">
            <div style="position: relative; flex: 1.5;">
                <input type="text" id="pos_cust_search" class="form-input" placeholder="🔍 Search Customer by Name or Phone (নাম বা মোবাইল নম্বর দিয়ে খুঁজুন)..." style="font-size: 12px; height: 38px; padding: 4px 12px; width: 100%; border-radius: 8px; font-weight: 500;" autocomplete="off" oninput="ajaxSearchPosCustomer(this.value)">
                
                <div id="pos_cust_suggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--card, #1e293b); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 14px 30px rgba(0,0,0,0.35); z-index: 1000; max-height: 240px; overflow-y: auto; margin-top: 4px;"></div>
            </div>

            <div style="flex: 1.2;">
                <select id="cart_customer_id" class="form-select" style="font-size: 12px; height: 38px; width: 100%; border-radius: 8px;" onchange="onPosCustomerSelectChange(this)">
                    <option value="" data-due="0" data-limit="0">Walk-in Customer (খুচরা কাস্টমার)</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-due="{{ $c->current_due }}" data-limit="{{ $c->credit_limit }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">
                            {{ $c->name }} ({{ $c->phone }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="btn btn-primary" onclick="openQuickCustomerModal()" style="height: 38px; padding: 0 16px; border-radius: 8px; font-weight: 700; font-size: 12.5px; white-space: nowrap;">
                + কাস্টমার
            </button>

            <div id="pos_cust_due_badge" style="display: none; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: var(--danger, #ef4444); padding: 0 12px; height: 38px; border-radius: 8px; font-size: 12px; font-weight: 800; white-space: nowrap;">
                <span>পূর্বের বাকি:</span>
                <span id="pos_display_cust_due" style="font-family: monospace; font-size: 13px;">৳0.00</span>
            </div>
        </div>

    </div>
</div>