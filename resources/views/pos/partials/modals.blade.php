<!-- 1. Quick Add Customer Modal -->
<div id="quickCustomerModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(3px); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="border-radius: 12px; padding: 1.5rem; max-width: 400px; width: 100%; border: 1px solid var(--border); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <h3 style="font-size: 1rem; font-weight: 800; margin: 0; color: var(--foreground);">➕ নতুন কাস্টমার যোগ করুন</h3>
            <button type="button" onclick="closeQuickCustomerModal()" style="border: none; background: none; font-size: 18px; cursor: pointer; color: var(--muted-foreground);">✕</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div>
                <label class="form-label" style="font-size: 11px;">কাস্টমারের নাম <span style="color: var(--danger);">*</span></label>
                <input type="text" id="modal_cust_name" class="form-input" placeholder="e.g. মোঃ শফিকুল ইসলাম">
            </div>
            <div>
                <label class="form-label" style="font-size: 11px;">মোবাইল নম্বর <span style="color: var(--danger);">*</span></label>
                <input type="text" id="modal_cust_phone" class="form-input" placeholder="017XXXXXXXX">
            </div>
            <div>
                <label class="form-label" style="font-size: 11px;">ঠিকানা (Address)</label>
                <input type="text" id="modal_cust_address" class="form-input" placeholder="দোকান নং / এলাকা">
            </div>
            <div>
                <label class="form-label" style="font-size: 11px;">অনুমোদিত বাকির সীমা (Credit Limit ৳)</label>
                <input type="number" id="modal_cust_limit" class="form-input" value="10000.00">
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeQuickCustomerModal()" style="flex: 1;">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitQuickCustomer()" style="flex: 1; font-weight: 700;">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Sale Completed / Print Receipt Modal -->
<div id="receiptModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(3px); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="border-radius: 12px; padding: 1.75rem; max-width: 380px; width: 100%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4);">
        <div style="font-size: 2.5rem; margin-bottom: 0.25rem; animation: pulse 1.5s infinite;">🎉</div>
        <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--foreground);">বিক্রয় সম্পন্ন হয়েছে!</h3>
        <p style="font-size: 0.85rem; color: var(--muted-foreground); margin-top: 6px; font-family: monospace;" id="receiptInvoiceText"></p>
        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <a href="#" id="receiptPrintLink" target="_blank" class="btn btn-primary" style="flex: 1; font-weight: 700;">🖨️ প্রিন্ট মেমো</a>
            <button type="button" class="btn btn-secondary" onclick="closeReceiptModal()" style="flex: 1;">পরবর্তী বিক্রয়</button>
        </div>
    </div>
</div>