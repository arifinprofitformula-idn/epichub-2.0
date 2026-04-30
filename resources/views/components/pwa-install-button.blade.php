<div
    x-data="{
        deferredPrompt: null,
        canInstall: false,
        isIos: false,
        isStandalone: false,
        init() {
            this.isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            this.deferredPrompt = window.__epicHubDeferredInstallPrompt;
            this.canInstall = !! this.deferredPrompt;

            window.addEventListener('epic-hub:install-ready', () => {
                this.deferredPrompt = window.__epicHubDeferredInstallPrompt;
                this.canInstall = !! this.deferredPrompt;
            });

            window.addEventListener('epic-hub:installed', () => {
                this.deferredPrompt = null;
                this.canInstall = false;
                this.isStandalone = true;
            });
        },
        get visible() {
            return ! this.isStandalone && (this.canInstall || this.isIos);
        },
        async promptInstall() {
            if (! this.deferredPrompt) {
                return;
            }

            this.deferredPrompt.prompt();
            await this.deferredPrompt.userChoice.catch(() => null);
            this.deferredPrompt = null;
            this.canInstall = false;
            window.__epicHubDeferredInstallPrompt = null;
        },
    }"
    x-show="visible"
    style="display: none;"
    {{ $attributes->class([
        'rounded-[1.5rem] border border-emerald-200 bg-white/95 p-4 shadow-[0_18px_42px_rgba(5,150,105,0.10)]',
    ]) }}
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <img
                src="{{ asset('icons/icon-192.png') }}"
                alt="EPIC Hub"
                class="h-14 w-14 rounded-[1rem] border border-emerald-100 bg-emerald-50 object-cover"
                width="56"
                height="56"
                loading="lazy"
            />

            <div class="min-w-0">
                <div class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-600">EPIC Hub PWA</div>
                <div class="mt-1 text-base font-semibold text-slate-900">Install EPIC Hub di perangkat Anda untuk akses lebih cepat.</div>
                <p class="mt-1 text-sm leading-relaxed text-slate-600">
                    Shortcut ini membuka EPIC Hub seperti app ringan tanpa perlu Play Store atau App Store.
                </p>
                <p x-show="isIos && !canInstall" class="mt-2 text-sm font-medium text-emerald-700">
                    Pengguna iPhone: buka Safari → Share → Add to Home Screen.
                </p>
            </div>
        </div>

        <div x-show="canInstall" class="shrink-0">
            <button
                type="button"
                x-on:click="promptInstall()"
                class="inline-flex min-h-11 items-center justify-center rounded-full bg-[linear-gradient(135deg,#10b981,#059669)] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_12px_28px_rgba(5,150,105,0.20)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-105 active:translate-y-0 active:scale-[0.98]"
            >
                Install EPIC Hub
            </button>
        </div>
    </div>
</div>
