<!-- Replace resources/views/components/global-loader.blade.php -->
<div id="global-top-progress" style="position: fixed; top: 0; left: 0; height: 3.5px; width: 0%; background: linear-gradient(90deg, #0284c7, #10b981, #38bdf8); z-index: 999999; transition: width 0.2s ease, opacity 0.3s ease; opacity: 0; pointer-events: none; box-shadow: 0 0 12px rgba(56, 189, 248, 0.9);"></div>

<div id="global-loader-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); z-index: 999998; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s ease;">
    <div style="background: var(--card, #1e293b); border: 1px solid var(--border, rgba(255,255,255,0.12)); padding: 1.5rem 2.25rem; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); display: flex; flex-direction: column; align-items: center; gap: 0.85rem; max-width: 320px; text-align: center;">
        <div class="global-spinner-ring"></div>
        <div>
            <h4 id="global-loader-title" style="font-size: 0.95rem; font-weight: 800; color: var(--foreground, #fff); margin: 0; line-height: 1.3;">
                প্রসেসিং হচ্ছে...
            </h4>
            <p id="global-loader-subtitle" style="font-size: 0.75rem; color: var(--muted-foreground, #94a3b8); margin: 4px 0 0 0;">
                দয়া করে কিছুক্ষণ অপেক্ষা করুন
            </p>
        </div>
    </div>
</div>

<div id="global-toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000000; display: flex; flex-direction: column; gap: 8px; pointer-events: none; max-width: 350px; width: 100%;"></div>

<style>
    .global-spinner-ring {
        width: 44px;
        height: 44px;
        border: 3.5px solid rgba(2, 132, 199, 0.2);
        border-top-color: var(--primary, #0284c7);
        border-right-color: #10b981;
        border-radius: 50%;
        animation: globalSpinnerRotate 0.75s linear infinite;
    }
    @keyframes globalSpinnerRotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .pos-toast {
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        background: var(--card, #1e293b);
        border: 1px solid var(--border, #334155);
        color: var(--foreground, #fff);
        font-size: 12.5px;
        font-weight: 600;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.4);
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .pos-toast.show { transform: translateX(0); opacity: 1; }
    .pos-toast.toast-warning { border-left: 4px solid #f59e0b; }
    .pos-toast.toast-error { border-left: 4px solid #ef4444; }
    .pos-toast.toast-success { border-left: 4px solid #10b981; }
</style>

<script>
    window.Loader = {
        progressBar: null,
        overlay: null,
        titleEl: null,
        subTitleEl: null,
        progressTimer: null,

        init() {
            this.progressBar = document.getElementById('global-top-progress');
            this.overlay     = document.getElementById('global-loader-overlay');
            this.titleEl     = document.getElementById('global-loader-title');
            this.subTitleEl  = document.getElementById('global-loader-subtitle');

            // 1. Intercept Navigation Links
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');

                if (
                    href &&
                    !href.startsWith('#') &&
                    !href.startsWith('javascript:') &&
                    !href.startsWith('mailto:') &&
                    !href.startsWith('tel:') &&
                    target !== '_blank' &&
                    !link.hasAttribute('download') &&
                    !link.hasAttribute('data-no-loader') &&
                    (href.startsWith('/') || href.startsWith(window.location.origin))
                ) {
                    this.show('পেজ লোড হচ্ছে...', 'দয়া করে অপেক্ষা করুন...');
                }
            });

            // 2. Intercept Form Submissions (SAFELY check if Cancel was clicked!)
            document.addEventListener('submit', (e) => {
                // 🛑 If confirm() returned false or validation failed, DO NOT SHOW LOADER!
                if (e.defaultPrevented) return;

                const form = e.target;
                if (!form.hasAttribute('data-no-loader') && form.getAttribute('target') !== '_blank') {
                    if (form.checkValidity && !form.checkValidity()) return;
                    this.show('সংরক্ষণ হচ্ছে...', 'ডেটা প্রসেসিং চলছে...');
                }
            });
        },

        show(title = 'পেজ লোড হচ্ছে...', subtitle = 'দয়া করে কিছুক্ষণ অপেক্ষা করুন...') {
            if (!this.overlay) this.init();
            if (this.titleEl) this.titleEl.textContent = title;
            if (this.subTitleEl) this.subTitleEl.textContent = subtitle;

            if (this.overlay) {
                this.overlay.style.display = 'flex';
                setTimeout(() => this.overlay.style.opacity = '1', 10);
            }
            this.startProgress();
        },

        hide() {
            if (this.overlay) {
                this.overlay.style.opacity = '0';
                setTimeout(() => {
                    this.overlay.style.display = 'none';
                }, 200);
            }
            this.stopProgress();
        },

        startProgress() {
            if (!this.progressBar) this.init();
            if (!this.progressBar) return;

            clearInterval(this.progressTimer);
            this.progressBar.style.opacity = '1';
            this.progressBar.style.width = '30%';

            let width = 30;
            this.progressTimer = setInterval(() => {
                if (width < 90) {
                    width += Math.random() * 8;
                    this.progressBar.style.width = `${width}%`;
                }
            }, 180);
        },

        stopProgress() {
            if (!this.progressBar) return;
            clearInterval(this.progressTimer);
            this.progressBar.style.width = '100%';
            setTimeout(() => {
                this.progressBar.style.opacity = '0';
                setTimeout(() => {
                    if (this.progressBar) this.progressBar.style.width = '0%';
                }, 300);
            }, 250);
        }
    };

    window.Toast = {
        show(message, type = 'warning', icon = '⚠️', duration = 3500) {
            const container = document.getElementById('global-toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pos-toast toast-${type}`;
            toast.innerHTML = `
                <span style="font-size: 1.1rem; flex-shrink: 0;">${icon}</span>
                <span style="flex: 1; line-height: 1.3;">${message}</span>
                <button type="button" style="border: none; background: transparent; cursor: pointer; color: var(--muted-foreground, #94a3b8); font-size: 13px;" onclick="this.parentElement.remove()">✕</button>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },
        success(msg) { this.show(msg, 'success', '✅'); },
        error(msg) { this.show(msg, 'error', '❌', 4000); },
        warning(msg) { this.show(msg, 'warning', '⚠️'); }
    };

    document.addEventListener('DOMContentLoaded', () => window.Loader.init());
    window.addEventListener('pageshow', () => window.Loader.hide());
</script>