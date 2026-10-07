<div class="card pos-col-right" style="border: 1px solid var(--border); display: flex; flex-direction: column; min-height: 0;">
    
    <!-- Cart Header Bar -->
    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.05)); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.1rem;">🛒</span>
            <h3 style="font-size: 0.95rem; font-weight: 800; margin: 0; color: var(--foreground);">Your Cart Items (আপনার কার্ট)</h3>
            <span id="cartCountBadge" class="badge badge-primary" style="font-size: 10px; font-weight: 800; padding: 2px 6px;">0 Items</span>
        </div>
        <button type="button" onclick="clearCart()" class="btn btn-ghost" style="padding: 2px 8px; font-size: 11px; color: var(--danger, #ef4444); font-weight: 600;">
            🗑️ Clear All
        </button>
    </div>

    <!-- Scrollable Items List -->
    <div class="pos-cart-items-wrapper" id="cartItemsContainer">
        <div id="emptyCartMessage" style="text-align: center; color: var(--muted-foreground); padding: 4rem 1.5rem; font-size: 13px;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.4;">🛍️</div>
            <strong style="color: var(--foreground); display: block; font-size: 14px;">কার্ট বর্তমানে খালি</strong>
            <span>বারকোড স্ক্যান করুন অথবা বামের পণ্যে ক্লিক করুন।</span>
        </div>

        <table class="table" id="cartTable" style="display: none; width: 100%; font-size: 12px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); background: rgba(148, 163, 184, 0.02);">
                    <th style="padding: 8px 6px;">পণ্য (Item)</th>
                    <th style="text-align: center; width: 105px; padding: 8px 4px;">পরিমাণ (Qty)</th>
                    <th style="text-align: right; width: 85px; padding: 8px 6px;">মোট (Total)</th>
                    <th style="width: 30px; text-align: center; padding: 8px 2px;"></th>
                </tr>
            </thead>
            <tbody id="cartTableBody"></tbody>
        </table>
    </div>

    <!-- Payment & Totals Summary Footer -->
    <div style="padding: 0.85rem 1rem; border-top: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.04));">
        
        <!-- Totals Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.75rem; align-items: center; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed var(--border);">
            <div style="font-size: 11.5px; color: var(--muted-foreground);">
                Subtotal: <strong id="cartSubtotal" style="color: var(--foreground); font-family: monospace;">৳0.00</strong>
                @if($enableVat)
                    <span style="margin-left: 6px;">VAT ({{ $vatPercent }}%): <strong id="cartVat" style="color: var(--foreground); font-family: monospace;">৳0.00</strong></span>
                @endif
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 11.5px;">
                <span>ছাড় (Disc):</span>
                <input type="number" step="0.5" id="cartDiscount" value="0.00" class="form-input" style="width: 75px; height: 26px; padding: 2px 6px; font-size: 11.5px; text-align: right; font-weight: 700; border-radius: 6px;" oninput="renderCartTotals()">
            </div>

            <div style="text-align: right;">
                <span style="font-size: 10px; color: var(--muted-foreground); text-transform: uppercase; display: block;">সর্বমোট প্রদেয়</span>
                <span id="cartGrandTotal" style="font-size: 1.35rem; font-weight: 900; color: var(--primary); font-family: monospace;">৳0.00</span>
            </div>
        </div>

        <!-- Quick Cash Buttons -->
        <div style="display: flex; gap: 4px; align-items: center; margin-bottom: 8px;">
            <button type="button" class="btn btn-secondary" onclick="setQuickCash(50)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700;">৳50</button>
            <button type="button" class="btn btn-secondary" onclick="setQuickCash(100)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700;">৳100</button>
            <button type="button" class="btn btn-secondary" onclick="setQuickCash(500)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700;">৳500</button>
            <button type="button" class="btn btn-secondary" onclick="setQuickCash(1000)" style="flex: 1; padding: 3px 0; font-size: 11px; font-weight: 700;">৳1000</button>
            <button type="button" class="btn btn-secondary" onclick="setExactCash()" style="flex: 1.2; padding: 3px 0; font-size: 11px; font-weight: 700; color: var(--success, #10b981);">Exact</button>
            <button type="button" onclick="toggleSplitPayment()" class="btn btn-ghost" style="padding: 3px 8px; font-size: 11px; text-decoration: underline; color: var(--primary); font-weight: 700;">
                ⚡ Split Pay
            </button>
        </div>

        <!-- Split Payment Panel -->
        <div id="splitPaymentBox" style="display: none; background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: 8px; margin-bottom: 8px;">
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

        <!-- Paid / Due / Change Inputs -->
        <div style="display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 8px; align-items: center; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 800;">পরিশোধ:</span>
                <input type="number" step="0.5" id="cartPaidAmount" class="form-input" placeholder="0.00" style="height: 32px; padding: 2px 8px; font-size: 13px; font-weight: 900; color: var(--success, #10b981); font-family: monospace;" oninput="onMainPaidInput(this.value)">
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

        <button type="button" class="btn btn-primary" onclick="submitPosSale()" style="width: 100%; padding: 0.75rem 1rem; font-weight: 900; font-size: 14px; border-radius: 8px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
            ✓ Complete Sale & Print Slip [F2]
        </button>

    </div>
</div>