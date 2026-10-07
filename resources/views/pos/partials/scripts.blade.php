<script>
    const posProducts = @json($products);
    const vatRate = {{ $enableVat ? $vatPercent : 0 }};
    let cart = [];
    let customerPreviousDue = 0;

    function toggleSplitPayment() {
        const box = document.getElementById('splitPaymentBox');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }

    // Customer Search
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
                        box.innerHTML = '<div style="padding: 8px 12px; font-size: 11px; color: var(--muted-foreground);">কোনো কাস্টমার পাওয়া যায়নি।</div>';
                    } else {
                        data.forEach(c => {
                            const item = document.createElement('div');
                            item.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                            item.onmouseover = () => item.style.background = 'var(--muted, #334155)';
                            item.onmouseout = () => item.style.background = 'transparent';
                            item.innerHTML = `
                                <div>
                                    <strong style="color: var(--foreground);">${c.name}</strong>
                                    <div style="font-size: 10px; color: var(--muted-foreground); font-family: monospace;">${c.phone}</div>
                                </div>
                                <span class="badge ${c.current_due > 0 ? 'badge-danger' : 'badge-success'}" style="font-size: 10px;">বাকি: ৳${parseFloat(c.current_due).toFixed(2)}</span>
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
        window.Toast.success(`কাস্টমার নির্বাচন: ${c.name}`);
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
            window.Toast.warning('কাস্টমারের নাম এবং মোবাইল নম্বর আবশ্যক!');
            return;
        }

        window.Loader.startProgress();

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
            window.Loader.stopProgress();
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
                window.Toast.success(`নতুন কাস্টমার ${c.name} যোগ হয়েছে!`);
            } else {
                window.Toast.error('কাস্টমার যোগ করতে সমস্যা হয়েছে।');
            }
        })
        .catch(err => {
            window.Loader.stopProgress();
            window.Toast.error('সার্ভারে যোগাযোগ করা যায়নি।');
        });
    }

    // Barcode Gun Scanning
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
                window.Toast.error(`বারকোড '${code}' এর কোনো পণ্য পাওয়া যায়নি!`);
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
        if (cart.length > 0) {
            cart = [];
            renderCart();
            window.Toast.warning('কার্ট খালি করা হয়েছে।');
        }
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
                        <button type="button" onclick="removeFromCart(${idx})" style="border: none; background: none; color: var(--danger, #ef4444); cursor: pointer; font-size: 13px; padding: 0 4px;" title="Remove Item">✕</button>
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

    function submitPosSale() {
        if (cart.length === 0) {
            window.Toast.warning('কার্টে কোনো পণ্য যোগ করা হয়নি!');
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

        // 🟢 Show Backdrop Loader during checkout
        window.Loader.show('বিক্রয় সম্পন্ন হচ্ছে...', 'ইনভয়েস তৈরি ও স্টক আপডেট করা হচ্ছে');

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
            window.Loader.hide();

            if (data.success) {
                document.getElementById('receiptInvoiceText').textContent = `Invoice: ${data.invoice_no} • Total: ৳${data.grand_total}`;
                document.getElementById('receiptPrintLink').href = data.receipt_url;
                document.getElementById('receiptModal').style.display = 'flex';
                clearCart();
                document.getElementById('cartPaidAmount').value = '';
                document.getElementById('pay_cash').value = '';
                document.getElementById('pay_bkash').value = '';
                document.getElementById('pay_bank').value = '';
                window.Toast.success('বিক্রয় সফলভাবে সম্পন্ন হয়েছে!');
            } else {
                window.Toast.error(data.message || 'বিক্রয় সম্পন্ন হতে সমস্যা হয়েছে।');
            }
        })
        .catch(err => {
            window.Loader.hide();
            window.Toast.error('সার্ভারে যোগাযোগ করা যায়নি।');
        });
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').style.display = 'none';
        document.getElementById('pos_barcode_input').focus();
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') {
            e.preventDefault();
            submitPosSale();
        } else if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
            e.preventDefault();
            document.getElementById('pos_barcode_input').focus();
        } else if (e.key === 'Escape') {
            closeQuickCustomerModal();
            closeReceiptModal();
            document.getElementById('pos_cust_suggestions').style.display = 'none';
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#pos_cust_search') && !e.target.closest('#pos_cust_suggestions')) {
            document.getElementById('pos_cust_suggestions').style.display = 'none';
        }
    });
</script>