<div
    x-data="{
        deferredPrompt: null,
        canInstall: false,
        isIos: false,
        isStandalone: false,
        isVisible: false,
        pageLoaded: false,
        delayElapsed: false,
        storageKey: 'epic-hub:pwa-install-dismissed',
        init() {
            this.isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            this.deferredPrompt = window.__epicHubDeferredInstallPrompt;
            this.canInstall = !! this.deferredPrompt;

            const onLoad = () => {
                this.pageLoaded = true;

                window.setTimeout(() => {
                    this.delayElapsed = true;
                    this.evaluateVisibility();
                }, 5000);
            };

            if (document.readyState === 'complete') {
                onLoad();
            } else {
                window.addEventListener('load', onLoad, { once: true });
            }

            window.addEventListener('epic-hub:install-ready', () => {
                this.deferredPrompt = window.__epicHubDeferredInstallPrompt;
                this.canInstall = !! this.deferredPrompt;
                this.evaluateVisibility();
            });

            window.addEventListener('epic-hub:installed', () => {
                this.deferredPrompt = null;
                this.canInstall = false;
                this.isStandalone = true;
                this.isVisible = false;
                window.localStorage.removeItem(this.storageKey);
            });
        },
        evaluateVisibility() {
            if (! this.pageLoaded || ! this.delayElapsed || this.isStandalone || this.isDismissed()) {
                return;
            }

            if (this.canInstall || this.isIos) {
                this.isVisible = true;
            }
        },
        isDismissed() {
            return window.sessionStorage.getItem(this.storageKey) === '1';
        },
        dismiss() {
            this.isVisible = false;
            window.sessionStorage.setItem(this.storageKey, '1');
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
            this.dismiss();
        },
    }"
    x-show="isVisible"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-[90] flex items-end justify-center bg-slate-950/45 px-4 py-6 sm:items-center"
>
    <div
        x-on:click.outside="dismiss()"
        {{ $attributes->class([
            'relative w-full max-w-sm overflow-hidden rounded-[2rem] border border-blue-200 bg-white p-6 shadow-[0_28px_80px_rgba(15,23,42,0.24)]',
        ]) }}
    >
        <button
            type="button"
            x-on:click="dismiss()"
            class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 transition-all duration-150 hover:border-blue-200 hover:text-blue-600"
            aria-label="Tutup pop up install EPIC Hub"
        >
            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="flex items-start gap-4 pr-10">
            <img
                src="{{ asset('epic-hub-auth-logo.png') }}"
                alt="EPIC Hub"
                class="h-14 w-auto object-contain"
                width="56"
                height="56"
                loading="lazy"
            />

            <div class="min-w-0 pt-1">
                <div class="text-lg font-semibold leading-tight tracking-tight text-slate-900">
                    Tambahkan EPIC Hub ke perangkat Anda
                </div>
            </div>
        </div>

        <div class="mt-5 rounded-[1.4rem] bg-blue-50/80 p-4 text-sm leading-relaxed text-blue-900">
            <p x-show="canInstall">
                Install sekarang untuk akses lebih cepat.
            </p>
            <p x-show="isIos && !canInstall" style="display: none;">
                iPhone: Safari → Share → Add to Home Screen.
            </p>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button
                type="button"
                x-on:click="dismiss()"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition-all duration-150 hover:border-slate-300 hover:text-slate-900 sm:w-auto"
            >
                Tutup
            </button>

            <button
                type="button"
                x-show="canInstall"
                x-on:click="promptInstall()"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-full border border-blue-700 bg-[linear-gradient(180deg,#4f8bff_0%,#2563eb_62%,#1e40af_100%)] px-5 py-3 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.28),0_12px_28px_rgba(37,99,235,0.22)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-105 active:translate-y-0 active:scale-[0.98] sm:w-auto"
                style="display: none;"
            >
                Install EPIC Hub
            </button>
        </div>
    </div>
</div>
