@php
    $bankName = data_get(config('epichub.payments.manual_bank_transfer'), 'bank_name');
    $accountNumber = data_get(config('epichub.payments.manual_bank_transfer'), 'account_number');
    $accountName = data_get(config('epichub.payments.manual_bank_transfer'), 'account_name');
    $isOnSale = $product->isOnSale ?? ((float)($product->sale_price ?? 0) > 0 && (float)$product->sale_price < (float)$product->price);
    $originalPrice = (float) $product->price;
    $effectivePrice = (float) $product->effective_price;
    $thumbnailSrc = filled($product->thumbnail) ? asset('storage/'.$product->thumbnail) : null;
    $discountPercent = $isOnSale && $originalPrice > 0 ? round((($originalPrice - $effectivePrice) / $originalPrice) * 100) : 0;
@endphp

<style>
    .btn-3d-checkout {
        position: relative;
        transform: translateY(0);
        box-shadow: 0 7px 0 0 #059669, 0 10px 20px rgba(16,185,129,0.35);
        transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    .btn-3d-checkout:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 9px 0 0 #059669, 0 14px 24px rgba(16,185,129,0.4);
    }
    .btn-3d-checkout:active:not(:disabled) {
        transform: translateY(5px);
        box-shadow: 0 2px 0 0 #059669, 0 4px 8px rgba(16,185,129,0.2);
    }
    .btn-3d-checkout:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        box-shadow: 0 4px 0 0 #a1a1aa;
        background: #a1a1aa;
    }
    .btn-3d-nav {
        position: relative;
        box-shadow: 0 4px 0 0 #d4d4d8, 0 6px 12px rgba(0,0,0,0.07);
        transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    .btn-3d-nav:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 0 0 #d4d4d8, 0 10px 16px rgba(0,0,0,0.1);
    }
    .btn-3d-nav:active {
        transform: translateY(3px);
        box-shadow: 0 1px 0 0 #d4d4d8;
    }
    .checkout-input {
        width: 100%;
        border-radius: 0.875rem;
        border: 1.5px solid #e4e4e7;
        background: #fff;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        font-size: 0.875rem;
        color: #18181b;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .checkout-input:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52,211,153,0.15);
    }
    .checkout-input::placeholder { color: #a1a1aa; }
    .copy-btn:active { transform: scale(0.95); }
    .step-dot {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
    }
</style>

<form
    method="POST"
    action="{{ route('checkout.store', $product->slug) }}"
    class="grid gap-5 lg:grid-cols-5"
    id="checkout-form"
>
    @csrf

    {{-- Mobile: order summary appears first --}}
    <div class="lg:hidden">
        @include('checkout.partials.order-summary')
    </div>

    {{-- Left column: Account info + Payment --}}
    <div class="space-y-5 lg:col-span-3">

        {{-- Account card --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.06)]">

            {{-- Card header --}}
            <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-400 to-sky-500 shadow-[0_3px_8px_rgba(56,189,248,0.3)]">
                        <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-zinc-900">
                            {{ auth()->guest() ? 'Informasi Akun' : 'Akun yang Digunakan' }}
                        </div>
                        <div class="text-xs text-zinc-500">
                            @guest Buat akun baru atau gunakan akun yang sudah ada @else Checkout memakai data akun aktif @endguest
                        </div>
                    </div>
                </div>
                @auth
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        Login
                    </span>
                @endauth
            </div>

            <div class="p-5">
                {{-- Error alerts --}}
                @if ($errors->has('checkout'))
                    <div class="mb-4">
                        <x-ui.alert variant="danger" title="Checkout gagal">
                            {{ $errors->first('checkout') }}
                        </x-ui.alert>
                    </div>
                @endif

                @if (! $isEligible)
                    <div class="mb-4">
                        <x-ui.alert variant="warning" title="Belum tersedia">
                            {{ $eligibilityMessage ?? 'Produk ini belum tersedia untuk checkout saat ini.' }}
                        </x-ui.alert>
                    </div>
                @endif

                @guest
                    {{-- Guest form --}}
                    <div class="grid gap-4">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold text-zinc-700">
                                <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                                Nama Lengkap
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                                </span>
                                <input
                                    id="name" name="name" type="text"
                                    value="{{ old('name') }}"
                                    autocomplete="name"
                                    class="checkout-input"
                                    placeholder="Nama lengkap Anda"
                                    required
                                />
                            </div>
                            @error('name')
                                <div class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold text-zinc-700">
                                <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                                Email
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                                </span>
                                <input
                                    id="email" name="email" type="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    class="checkout-input"
                                    placeholder="email@anda.com"
                                    required
                                />
                            </div>
                            @error('email')
                                <div class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- WhatsApp --}}
                        <div>
                            <label for="whatsapp_number" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold text-zinc-700">
                                <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 16.352V17.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                                WhatsApp
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 16.352V17.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input
                                    id="whatsapp_number" name="whatsapp_number" type="text"
                                    value="{{ old('whatsapp_number') }}"
                                    autocomplete="tel"
                                    class="checkout-input"
                                    placeholder="628123456789"
                                    required
                                />
                            </div>
                            <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-zinc-500">
                                <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                                Format internasional. Contoh: 628123456789
                            </div>
                            @error('whatsapp_number')
                                <div class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Passwords --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold text-zinc-700">
                                    <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 7a5 5 0 1 1 3.61 4.804l-1.903 1.903A1 1 0 0 1 9 14H8v1a1 1 0 0 1-1 1H6v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2a1 1 0 0 1 .293-.707L7.196 10.39A5.002 5.002 0 0 1 8 7Zm5-3a.75.75 0 0 0 0 1.5A1.5 1.5 0 0 1 14.5 7 .75.75 0 0 0 16 7a3 3 0 0 0-3-3Z" clip-rule="evenodd"/></svg>
                                    Password Baru
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                                    </span>
                                    <input
                                        id="password" name="password" type="password"
                                        autocomplete="new-password"
                                        class="checkout-input"
                                        placeholder="Buat password baru"
                                        required
                                    />
                                </div>
                                @error('password')
                                    <div class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold text-zinc-700">
                                    <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                                    Konfirmasi
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                                    </span>
                                    <input
                                        id="password_confirmation" name="password_confirmation" type="password"
                                        autocomplete="new-password"
                                        class="checkout-input"
                                        placeholder="Ulangi password"
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Already have account hint --}}
                        @if ($errors->has('email') || $errors->has('whatsapp_number'))
                            <div class="flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 p-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-100">
                                    <svg class="h-4 w-4 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-sky-900">Sudah punya akun?</div>
                                    <div class="mt-0.5 text-xs text-sky-800">Login terlebih dahulu untuk checkout dengan data yang sudah terdaftar.</div>
                                    <a href="{{ route('login') }}" class="btn-3d-nav mt-3 inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-white px-3 py-1.5 text-xs font-bold text-sky-700">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                                        Login ke Akun Saya
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Logged-in user info --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-200">
                                <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-400">Nama</div>
                                <div class="truncate text-sm font-bold text-zinc-900">{{ auth()->user()->name }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-200">
                                <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-400">Email</div>
                                <div class="truncate text-sm font-bold text-zinc-900">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-zinc-100 bg-zinc-50 px-4 py-3 sm:col-span-2">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-200">
                                <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 16.352V17.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-400">WhatsApp</div>
                                <div class="text-sm font-bold text-zinc-900">{{ auth()->user()->whatsapp_number ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Payment method card --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.06)]">
            <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-400 to-violet-500 shadow-[0_3px_8px_rgba(167,139,250,0.3)]">
                    <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5V6h18v-.5A1.5 1.5 0 0 0 17.5 4h-15ZM19 8.5H1v6A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-6ZM3 13.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm4.75-.75a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-zinc-900">Metode Pembayaran</div>
                    <div class="text-xs text-zinc-500">Transfer bank manual</div>
                </div>
            </div>

            <div class="p-5">
                <div class="overflow-hidden rounded-xl border border-violet-100 bg-gradient-to-br from-violet-50 to-indigo-50">
                    <div class="border-b border-violet-100/60 px-4 py-3">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.12em] text-violet-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5V6h18v-.5A1.5 1.5 0 0 0 17.5 4h-15ZM19 8.5H1v6A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-6ZM3 13.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm4.75-.75a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd"/></svg>
                            Detail Rekening Tujuan
                        </div>
                    </div>
                    <div class="space-y-3 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs text-violet-600/70">Bank</div>
                            <div class="text-sm font-bold text-zinc-900">{{ $bankName }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs text-violet-600/70">Atas Nama</div>
                            <div class="text-sm font-bold text-zinc-900">{{ $accountName }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs text-violet-600/70">Nomor Rekening</div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base font-bold tracking-widest text-zinc-950">{{ $accountNumber }}</span>
                                <button
                                    type="button"
                                    onclick="navigator.clipboard.writeText('{{ $accountNumber }}').then(() => { this.innerHTML = '<svg class=\'h-3.5 w-3.5\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path fill-rule=\'evenodd\' d=\'M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z\' clip-rule=\'evenodd\'/></svg>'; setTimeout(() => { this.innerHTML = '<svg class=\'h-3.5 w-3.5\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path d=\'M7 3.5A1.5 1.5 0 0 1 8.5 2h3.879a1.5 1.5 0 0 1 1.06.44l3.122 3.12A1.5 1.5 0 0 1 17 6.622V12.5a1.5 1.5 0 0 1-1.5 1.5h-1v-3.379a3 3 0 0 0-.879-2.121L10.5 5.379A3 3 0 0 0 8.379 4.5H7v-1Z\'/><path d=\'M4.5 6A1.5 1.5 0 0 0 3 7.5v9A1.5 1.5 0 0 0 4.5 18h7a1.5 1.5 0 0 0 1.5-1.5v-5.879a1.5 1.5 0 0 0-.44-1.06L9.44 6.439A1.5 1.5 0 0 0 8.378 6H4.5Z\'/></svg>'; }, 2000); })"
                                    class="copy-btn flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-violet-200 bg-white text-violet-500 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700"
                                    title="Salin nomor rekening"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 3.5A1.5 1.5 0 0 1 8.5 2h3.879a1.5 1.5 0 0 1 1.06.44l3.122 3.12A1.5 1.5 0 0 1 17 6.622V12.5a1.5 1.5 0 0 1-1.5 1.5h-1v-3.379a3 3 0 0 0-.879-2.121L10.5 5.379A3 3 0 0 0 8.379 4.5H7v-1Z"/>
                                        <path d="M4.5 6A1.5 1.5 0 0 0 3 7.5v9A1.5 1.5 0 0 0 4.5 18h7a1.5 1.5 0 0 0 1.5-1.5v-5.879a1.5 1.5 0 0 0-.44-1.06L9.44 6.439A1.5 1.5 0 0 0 8.378 6H4.5Z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Steps after checkout --}}
                <div class="mt-4 space-y-2">
                    <div class="text-[11px] font-bold uppercase tracking-[0.12em] text-zinc-400">Langkah setelah checkout</div>
                    @foreach([
                        ['icon' => 'M2.5 4A1.5 1.5 0 0 0 1 5.5V6h18v-.5A1.5 1.5 0 0 0 17.5 4h-15ZM19 8.5H1v6A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-6ZM3 13.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm4.75-.75a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z', 'color' => 'violet', 'text' => 'Transfer sejumlah total ke rekening di atas'],
                        ['icon' => 'M1 12.5A4.5 4.5 0 0 0 5.5 17H15a3 3 0 0 0 1.5-5.605V11a3 3 0 0 0-2.663-2.978 3.5 3.5 0 0 0-6.674 0A3 3 0 0 0 5 11v.395A4.5 4.5 0 0 0 1 12.5Zm9.25.75a.75.75 0 0 0-1.5 0v2.59l-.72-.72a.75.75 0 0 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l2-2a.75.75 0 1 0-1.06-1.06l-.72.72v-2.59Z', 'color' => 'sky', 'text' => 'Upload bukti transfer di halaman instruksi pembayaran'],
                        ['icon' => 'M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z', 'color' => 'amber', 'text' => 'Tunggu verifikasi admin (biasanya 1×24 jam)'],
                        ['icon' => 'M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z', 'color' => 'emerald', 'text' => 'Akses kelas otomatis terbuka setelah verifikasi'],
                    ] as $i => $step)
                        <div class="flex items-center gap-3">
                            <div class="step-dot bg-{{ $step['color'] }}-100 text-{{ $step['color'] }}-600">{{ $i + 1 }}</div>
                            <div class="flex-1 text-xs text-zinc-600">{{ $step['text'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Right column: Order summary (desktop) --}}
    <div class="hidden lg:col-span-2 lg:block">
        <div class="lg:sticky lg:top-6">
            @include('checkout.partials.order-summary')
        </div>
    </div>
</form>
