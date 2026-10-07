<div class="pos-col-left">
    <!-- Barcode Scanner Input -->
    <div class="card" style="margin-bottom: 0.65rem; border: 1px solid var(--border);">
        <div class="card-body" style="padding: 0.5rem 0.85rem;">
            <div style="display: flex; gap: 0.65rem; align-items: center;">
                <span style="font-size: 1.35rem;">📷</span>
                <input type="text" id="pos_barcode_input" class="form-input" placeholder="স্ক্যানার গান দিয়ে বারকোড স্ক্যান করুন অথবা পণ্যের নাম লিখুন... (Press '/' to focus)" autofocus style="font-size: 13px; height: 38px; padding: 4px 12px; border-radius: 8px;" onkeydown="handlePosBarcode(event)" oninput="filterPosProducts(this.value)">
            </div>
        </div>
    </div>

    <!-- Category Filter Chips -->
    <div class="pos-category-tabs hide-scrollbar" style="display: flex; gap: 0.45rem; overflow-x: auto; padding-bottom: 6px; margin-bottom: 0.65rem;">
        <button type="button" class="btn btn-primary pos-cat-btn" onclick="filterByCat('all', this)" style="padding: 5px 12px; font-size: 11.5px; border-radius: 8px; white-space: nowrap; font-weight: 600;">
            🌟 সকল পণ্য
        </button>
        @foreach($categories as $cat)
            <button type="button" class="btn btn-secondary pos-cat-btn" onclick="filterByCat('{{ $cat->id }}', this)" style="padding: 5px 12px; font-size: 11.5px; border-radius: 8px; white-space: nowrap; font-weight: 600;">
                {{ $cat->name }}
            </button>
        @endforeach
    </div>

    <!-- 3-Column Product Grid -->
    <div class="card pos-products-scroll-area" style="flex: 1; min-height: 0; overflow-y: auto; border: 1px solid var(--border);">
        <div class="card-body" style="padding: 0.75rem;">
            <div class="pos-grid-3-col" id="posProductGrid">
                @foreach($products as $p)
                    <div class="pos-prod-card" data-id="{{ $p->id }}" data-barcode="{{ $p->barcode }}" data-name="{{ strtolower($p->name . ' ' . $p->name_en) }}" data-cat="{{ $p->category_id }}" onclick="addToCart({{ $p->id }})">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px; margin-bottom: 4px;">
                            <span class="pos-prod-cat-pill">{{ $p->category?->name ?? 'Grocery' }}</span>
                            <span class="badge {{ $p->currentStock() <= 0 ? 'badge-danger' : ($p->currentStock() <= $p->alert_quantity ? 'badge-warning' : 'badge-success') }}" style="font-size: 9.5px; padding: 2px 5px; font-weight: 700;">
                                {{ $p->currentStock() }} {{ $p->unit?->short_code }}
                            </span>
                        </div>

                        <div class="pos-prod-title" title="{{ $p->name }}">{{ $p->name }}</div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 6px;">
                            <div>
                                <div style="font-size: 9px; color: var(--muted-foreground);">MRP / রেট</div>
                                <span class="pos-prod-price">৳{{ number_format($p->selling_price, 2) }}</span>
                            </div>
                            <div class="pos-add-badge">+ Add</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>