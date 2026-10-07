<style>
    .pos-wrapper {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 85px);
    }
    .pos-main-container {
        display: grid;
        grid-template-columns: 6fr 4fr; /* 60% Left / 40% Right */
        gap: 0.85rem;
        flex: 1;
        min-height: 0;
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
        overflow: hidden;
    }

    /* 3-Column Touch Grid for Products */
    .pos-grid-3-col {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .pos-prod-card {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0.65rem;
        cursor: pointer;
        background: var(--card);
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 105px;
    }
    .pos-prod-card:hover {
        transform: translateY(-2px);
        border-color: var(--primary, #0284c7);
        box-shadow: 0 8px 16px -4px rgba(2, 132, 199, 0.2);
    }
    .pos-prod-card:active {
        transform: scale(0.98);
    }

    .pos-prod-cat-pill {
        font-size: 8.5px;
        font-weight: 700;
        color: var(--muted-foreground);
        text-transform: uppercase;
        background: rgba(148, 163, 184, 0.1);
        padding: 1px 5px;
        border-radius: 4px;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .pos-prod-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--foreground);
        line-height: 1.25;
        height: 30px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .pos-prod-price {
        font-size: 14px;
        font-weight: 900;
        color: var(--primary, #0284c7);
        font-family: monospace;
    }
    .pos-add-badge {
        font-size: 9.5px;
        font-weight: 700;
        background: rgba(2, 132, 199, 0.1);
        color: var(--primary, #0284c7);
        padding: 2px 6px;
        border-radius: 4px;
    }

    .pos-cart-items-wrapper {
        flex: 1;
        min-height: 160px;
        overflow-y: auto;
        padding: 0.25rem 0.5rem;
    }

    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    @media (max-width: 1200px) {
        .pos-grid-3-col {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .pos-wrapper {
            height: auto;
        }
        .pos-main-container {
            grid-template-columns: 1fr;
        }
        .pos-products-scroll-area {
            max-height: 380px;
        }
    }
</style>