@php
    $statusCards = [
        [
            'label' => 'OMS Integration',
            'value' => $integrationEnabled ? 'Aktif' : 'Nonaktif',
            'hint' => $integrationEnabled ? 'Inbound OMS siap menerima request.' : 'Masih diblokir dari konfigurasi environment.',
            'shell' => 'from-sky-500 via-cyan-500 to-sky-700',
            'panel' => 'border-sky-200/80 bg-sky-50/80',
            'iconBg' => 'bg-white/20',
            'icon' => 'sync',
        ],
        [
            'label' => 'Signature Secret',
            'value' => $signatureConfigured ? 'Configured' : 'Missing',
            'hint' => $signatureConfigured ? 'HMAC verification menjadi jalur auth utama.' : 'Fallback bearer token akan dipakai jika tersedia.',
            'shell' => 'from-violet-500 via-indigo-500 to-violet-700',
            'panel' => 'border-violet-200/80 bg-violet-50/80',
            'iconBg' => 'bg-white/20',
            'icon' => 'shield',
        ],
        [
            'label' => 'Password Key',
            'value' => $passwordKeyConfigured ? 'Configured' : 'Missing',
            'hint' => $passwordKeyConfigured ? 'Cipher inbound siap memproses password terenkripsi.' : 'Decrypt password OMS belum dapat dijalankan.',
            'shell' => 'from-amber-400 via-orange-400 to-amber-600',
            'panel' => 'border-amber-200/80 bg-amber-50/80',
            'iconBg' => 'bg-white/25',
            'icon' => 'key',
        ],
        [
            'label' => 'Latest Logs',
            'value' => (string) $latestLogs->count(),
            'hint' => $latestLogs->isNotEmpty() ? 'Ringkasan 10 request create-account terbaru.' : 'Belum ada request OMS yang terekam.',
            'shell' => 'from-emerald-500 via-teal-500 to-emerald-700',
            'panel' => 'border-emerald-200/80 bg-emerald-50/80',
            'iconBg' => 'bg-white/20',
            'icon' => 'chart',
        ],
    ];

    $securityNotes = [
        ['title' => 'No Plaintext', 'text' => 'Password hanya hidup di memory lalu langsung di-hash sebelum disimpan.', 'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-800'],
        ['title' => 'Sanitized Logs', 'text' => 'Encrypted password, signature, token, dan authorization di-redact dari log.', 'tone' => 'border-sky-200 bg-sky-50 text-sky-800'],
        ['title' => 'Signed Request', 'text' => 'HMAC adalah jalur autentikasi utama dan bearer hanya fallback saat diset.', 'tone' => 'border-violet-200 bg-violet-50 text-violet-800'],
        ['title' => 'Idempotent', 'text' => 'Duplicate request ID yang sudah sukses tidak akan membuat akun ganda.', 'tone' => 'border-amber-200 bg-amber-50 text-amber-800'],
    ];

    $envRows = [
        'OMS_INTEGRATION_ENABLED',
        'OMS_INBOUND_SECRET',
        'OMS_SIGNATURE_SECRET',
        'OMS_PASSWORD_ENCRYPTION_KEY',
        'OMS_RESPONSE_SUCCESS_CODE=00',
        'OMS_RESPONSE_FAILED_CODE=99',
    ];

    $aliasFields = [
        'kode_new_epic',
        'nama_new_epic',
        'email_addr_new_epic',
        'no_tlp_new_epic',
        'nama_epi_store_new_epic',
        'kode_epic_sponsor',
        'nama_epic_sponsor',
        'password_terenkripsi',
    ];

    $headerRows = [
        'X-OMS-Request-Id',
        'X-OMS-Timestamp',
        'X-OMS-Signature',
        'Authorization: Bearer ...',
    ];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,#14295f_0%,#1e3a8a_38%,#0ea5e9_100%)] p-[1px] shadow-[0_24px_70px_rgba(30,58,138,0.20)]">
            <div class="relative overflow-hidden rounded-[calc(2rem-1px)] bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.16),transparent_30%),linear-gradient(135deg,#14295f_0%,#1e3a8a_40%,#0b1224_100%)] px-6 py-7 text-white md:px-8 md:py-8">
                <div class="pointer-events-none absolute -right-10 -top-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-32 w-32 rounded-full bg-cyan-300/10 blur-2xl"></div>

                <div class="relative flex flex-col gap-6">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-cyan-100">
                            <span class="inline-flex h-2 w-2 rounded-full {{ $integrationEnabled ? 'bg-emerald-300' : 'bg-rose-300' }}"></span>
                            OMS Integration Guide
                        </div>
                        <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white md:text-4xl">Integrasi API OMS ⇄ EPIC Hub</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200 md:text-base">
                            Dokumentasi teknis inbound OMS untuk create atau resend account EPI Channel, lengkap dengan kontrak request, response, posture keamanan, dan status konfigurasi lingkungan saat ini.
                        </p>

                        <div class="mt-5 grid gap-3 md:grid-cols-2 2xl:hidden">
                            <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/90 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M7 12h10M13 8l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/80">Method</div>
                                    <div class="mt-1 font-semibold">POST only</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/90 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/80">Tolerance</div>
                                    <div class="mt-1 font-semibold">5 minute skew</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hidden 2xl:grid 2xl:grid-cols-4 2xl:gap-3">
                        <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/90 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M7 12h10M13 8l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/80">Method</div>
                                <div class="mt-1 font-semibold">POST only</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/90 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/80">Tolerance</div>
                                <div class="mt-1 font-semibold">5 minute skew</div>
                            </div>
                        </div>
                        <div class="rounded-[1.4rem] border border-white/12 bg-white/10 p-4 backdrop-blur-sm">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/75">Endpoint</div>
                            <div class="mt-2 truncate text-sm font-semibold leading-6 text-white">{{ $endpointUrl }}</div>
                        </div>
                        <div class="rounded-[1.4rem] border border-white/12 bg-white/10 p-4 backdrop-blur-sm">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/75">Response Code</div>
                            <div class="mt-2 flex items-center gap-2 text-sm font-semibold text-white">
                                <span class="rounded-full bg-emerald-400/15 px-2.5 py-1 text-emerald-100">00</span>
                                <span class="rounded-full bg-rose-400/15 px-2.5 py-1 text-rose-100">99</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
            @foreach ($statusCards as $card)
                <div class="relative overflow-hidden rounded-[1.6rem] bg-gradient-to-br {{ $card['shell'] }} p-[1px] shadow-[0_18px_40px_rgba(15,23,42,0.10)]">
                    <div class="relative h-full rounded-[calc(1.6rem-1px)] {{ $card['panel'] }} p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">{{ $card['label'] }}</div>
                                <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ $card['value'] }}</div>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $card['hint'] }}</p>
                            </div>
                            <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $card['iconBg'] }} text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.18)]">
                                @switch($card['icon'])
                                    @case('shield')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="m9.5 12 1.7 1.7 3.3-3.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                    @case('key')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M14.5 9.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM14 10h7M18 10v3M21 10v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                    @case('chart')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M5 20V10M12 20V4M19 20v-7M4 20h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M16 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 12a8 8 0 0 1 8-8m0 16a8 8 0 0 0 8-8m-8 8c-1.8 0-3.4-.6-4.8-1.5M12 20c1.8 0 3.4-.6 4.8-1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(148,163,184,0.10)]">
                <div class="border-b border-slate-200 bg-[linear-gradient(135deg,#14295f_0%,#1e3a8a_100%)] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4 7h16M7 4v6m10-6v6M5 11h14v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-blue-100/75">Inbound Contract</div>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Endpoint & Security Envelope</h3>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-[1.3rem] border border-slate-200 bg-slate-50/80 p-4">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Method</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">POST</div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Endpoint ini hanya dipakai OMS untuk create atau resend account.</p>
                        </div>
                        <div class="rounded-[1.3rem] border border-slate-200 bg-slate-50/80 p-4 md:col-span-2">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">URL</div>
                            <div class="mt-2 overflow-x-auto rounded-2xl bg-slate-950 px-4 py-3 text-sm font-semibold text-slate-100">{{ $endpointUrl }}</div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Purpose: Create / Resend Account EPI Channel dari OMS ke EPIC Hub.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-2">
                        <div class="rounded-[1.3rem] border border-sky-200 bg-[linear-gradient(180deg,#f0f9ff_0%,#eefaff_100%)] p-5">
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-500 text-white shadow-[0_10px_24px_rgba(14,165,233,0.20)]">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="text-lg font-semibold tracking-tight text-slate-900">Required Headers</div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($headerRows as $header)
                                    <span class="inline-flex rounded-full border border-sky-200 bg-white px-3 py-1.5 text-xs font-semibold text-sky-800 shadow-sm">{{ $header }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-[1.3rem] border border-violet-200 bg-[linear-gradient(180deg,#f5f3ff_0%,#f8f5ff_100%)] p-5">
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-500 text-white shadow-[0_10px_24px_rgba(139,92,246,0.20)]">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M7 12h10M12 7v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M5 5h14v14H5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="text-lg font-semibold tracking-tight text-slate-900">Signature Formula</div>
                            </div>
                            <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 px-4 py-3 text-sm text-slate-100">hash_hmac('sha256', timestamp + request_id + raw_body, OMS_SIGNATURE_SECRET)</pre>
                            <p class="mt-3 text-sm leading-6 text-slate-600">Tolerance timestamp saat ini 5 menit dan signature tetap menjadi rekomendasi utama.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="overflow-hidden rounded-[1.8rem] border border-amber-200 bg-white shadow-[0_18px_45px_rgba(251,191,36,0.10)]">
                    <div class="border-b border-amber-200 bg-[linear-gradient(135deg,#92400e_0%,#d97706_100%)] px-6 py-5 text-white">
                        <div class="flex items-center gap-3">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 9v4m0 4h.01M10.3 3.84 2.82 17a2 2 0 0 0 1.74 3h14.88a2 2 0 0 0 1.74-3L13.7 3.84a2 2 0 0 0-3.48 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-amber-100/75">Security Posture</div>
                                <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Guardrails & Safety Notes</h3>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 p-6">
                        @foreach ($securityNotes as $note)
                            <div class="rounded-[1.2rem] border px-4 py-4 {{ $note['tone'] }}">
                                <div class="text-sm font-semibold">{{ $note['title'] }}</div>
                                <div class="mt-1 text-sm leading-6">{{ $note['text'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-[1.8rem] border border-emerald-200 bg-white shadow-[0_18px_45px_rgba(16,185,129,0.10)]">
                    <div class="border-b border-emerald-200 bg-[linear-gradient(135deg,#065f46_0%,#059669_100%)] px-6 py-5 text-white">
                        <div class="flex items-center gap-3">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M7 7h10M7 12h10M7 17h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-emerald-100/75">Runtime Config</div>
                                <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Environment & Status</h3>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5 p-6">
                        <div class="grid gap-3">
                            @foreach ($envRows as $env)
                                <div class="rounded-[1.1rem] border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm font-semibold text-emerald-900">
                                    <code>{{ $env }}</code>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid gap-3">
                            <div class="rounded-[1.1rem] border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">OMS_INTEGRATION_ENABLED:</span>
                                <span class="ml-2 rounded-full {{ $integrationEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} px-2.5 py-1 text-xs font-semibold">{{ $integrationEnabled ? 'aktif' : 'tidak aktif' }}</span>
                            </div>
                            <div class="rounded-[1.1rem] border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">Signature secret configured:</span>
                                <span class="ml-2 rounded-full {{ $signatureConfigured ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700' }} px-2.5 py-1 text-xs font-semibold">{{ $signatureConfigured ? 'yes' : 'no' }}</span>
                            </div>
                            <div class="rounded-[1.1rem] border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">Password encryption key configured:</span>
                                <span class="ml-2 rounded-full {{ $passwordKeyConfigured ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700' }} px-2.5 py-1 text-xs font-semibold">{{ $passwordKeyConfigured ? 'yes' : 'no' }}</span>
                            </div>
                            <div class="rounded-[1.1rem] border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">Bearer fallback configured:</span>
                                <span class="ml-2 rounded-full {{ $bearerConfigured ? 'bg-violet-100 text-violet-700' : 'bg-slate-200 text-slate-700' }} px-2.5 py-1 text-xs font-semibold">{{ $bearerConfigured ? 'yes' : 'no' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[1.8rem] border border-cyan-200 bg-white shadow-[0_18px_45px_rgba(14,165,233,0.10)]">
                <div class="border-b border-cyan-200 bg-[linear-gradient(135deg,#0e7490_0%,#0284c7_100%)] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7 8h10M7 12h7M7 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-cyan-100/75">Payload</div>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Primary Request Body</h3>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 p-6">
                    <pre class="overflow-x-auto rounded-[1.3rem] bg-slate-950 px-4 py-4 text-sm leading-6 text-slate-100">{
  "kode_epic": "EPI12345",
  "nama_epic": "Budi Santoso",
  "email_epic": "budi@example.com",
  "no_tlp_epic": "628123456789",
  "nama_epi_store": "Budi Gold Store",
  "sponsor_epic_code": "EPI00001",
  "sponsor_name": "Ahmad Sponsor",
  "encrypted_password": "..."
}</pre>
                    <p class="text-sm leading-6 text-slate-600">Snake case utama diprioritaskan, tetapi endpoint juga menerima alias field dari dokumen OMS lalu menormalkannya ke internal contract EPIC Hub.</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[1.8rem] border border-violet-200 bg-white shadow-[0_18px_45px_rgba(139,92,246,0.10)]">
                <div class="border-b border-violet-200 bg-[linear-gradient(135deg,#3730a3_0%,#7c3aed_100%)] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M8 7h8M8 12h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-violet-100/75">Alias Support</div>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Field Mapping dari OMS</h3>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($aliasFields as $field)
                            <span class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-800 shadow-sm">{{ $field }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[1.8rem] border border-emerald-200 bg-white shadow-[0_18px_45px_rgba(16,185,129,0.10)]">
                <div class="border-b border-emerald-200 bg-[linear-gradient(135deg,#065f46_0%,#10b981_100%)] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="m7 12 3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-emerald-100/75">Response</div>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Success Payload</h3>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <pre class="overflow-x-auto rounded-[1.3rem] bg-slate-950 px-4 py-4 text-sm leading-6 text-slate-100">{
  "response_code": "{{ $successCode }}",
  "message": "Sukses",
  "data": {
    "epic_code": "EPI12345",
    "email": "budi@example.com"
  }
}</pre>
                </div>
            </div>

            <div class="overflow-hidden rounded-[1.8rem] border border-rose-200 bg-white shadow-[0_18px_45px_rgba(244,63,94,0.10)]">
                <div class="border-b border-rose-200 bg-[linear-gradient(135deg,#9f1239_0%,#f43f5e_100%)] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-rose-100/75">Response</div>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Business Failure Payload</h3>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <pre class="overflow-x-auto rounded-[1.3rem] bg-slate-950 px-4 py-4 text-sm leading-6 text-slate-100">{
  "response_code": "{{ $failedCode }}",
  "message": "Gagal",
  "error": "Email sudah terdaftar dengan kode EPIC berbeda."
}</pre>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(148,163,184,0.10)]">
            <div class="border-b border-slate-200 bg-[linear-gradient(135deg,#0f172a_0%,#1e293b_100%)] px-6 py-5 text-white">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-300">Audit Trail</div>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight text-white">Latest OMS Create Account Logs</h3>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-200">
                        <span class="inline-flex h-2 w-2 rounded-full {{ $latestLogs->isNotEmpty() ? 'bg-emerald-300' : 'bg-slate-400' }}"></span>
                        {{ $latestLogs->count() }} item terbaru
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Waktu</th>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Request ID</th>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">EPIC Code</th>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Email</th>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Response</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($latestLogs as $log)
                            <tr class="transition-colors duration-150 hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-slate-700">{{ $log->processed_at?->format('d M Y H:i:s') ?? $log->created_at?->format('d M Y H:i:s') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-800">{{ $log->action }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($log->request_id ?: '-', 26, '...') }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $log->epic_code ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $log->email ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $log->status?->value === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $log->status?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $log->response_code === $successCode ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $log->response_code ?: '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada log create account OMS.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
