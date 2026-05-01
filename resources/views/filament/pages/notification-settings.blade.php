<x-filament-panels::page>
    @php
        $tabs               = $tabs ?? [];
        $activeEvent        = $activeEvent ?? '';
        $activeTargets      = $activeTargets ?? [];
        $shortcodesForEvent = $shortcodesForEvent ?? [];
        $allAliases         = $allAliases ?? [];
        $validationResults  = $validationResults ?? [];

        $targetOrder  = ['member', 'sponsor', 'admin'];
        $targetLabels = ['member' => 'Member', 'sponsor' => 'Sponsor / Affiliate', 'admin' => 'Admin Platform'];
        $targetColors = ['member' => 'blue', 'sponsor' => 'amber', 'admin' => 'rose'];
    @endphp

    {{-- ── Page Header ─────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengaturan Notifikasi</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola template pesan otomatis untuk setiap kejadian sistem.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Shortcode Modal Trigger --}}
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-shortcode-modal')"
                    class="inline-flex items-center gap-2 rounded-lg border border-primary-300 bg-primary-50 px-3 py-2 text-sm font-medium text-primary-700 hover:bg-primary-100 transition"
                >
                    <x-heroicon-o-code-bracket class="w-4 h-4" />
                    Shortcode
                </button>

                {{-- Reset Tab --}}
                <button
                    type="button"
                    wire:click="resetEventTab('{{ $activeEvent }}')"
                    wire:confirm="Reset semua template tab ini ke default? Perubahan Anda akan hilang."
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                >
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                    Reset Tab Ini
                </button>

                {{-- Save All --}}
                <button
                    type="button"
                    wire:click="saveAll"
                    class="epic-save-button inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white"
                >
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                    Simpan Semua
                </button>
            </div>
        </div>
    </div>

    {{-- ── Event Tabs ──────────────────────────────────────────────────── --}}
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-1 border-b border-gray-200 min-w-max">
            @foreach($tabs as $tab)
                <button
                    type="button"
                    wire:click="setActiveEvent('{{ $tab['key'] }}')"
                    @class([
                        'px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
                        'border-primary-500 text-primary-600 bg-primary-50' => $activeEvent === $tab['key'],
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeEvent !== $tab['key'],
                    ])
                >
                    {{ $tab['label'] }}
                    @if(collect($validationResults[$tab['key']] ?? [])->flatMap(fn($v) => $v['invalid'] ?? [])->isNotEmpty())
                        <span class="ml-1.5 inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700">!</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Target Blocks ───────────────────────────────────────────────── --}}
    <div class="space-y-6">
        @forelse(array_intersect($targetOrder, array_keys($activeTargets)) as $targetKey)
            @php
                $fields     = $activeTargets[$targetKey];
                $tLabel     = $targetLabels[$targetKey] ?? $fields['target_label'] ?? $targetKey;
                $tColor     = $targetColors[$targetKey] ?? 'gray';
                $validation = $validationResults[$activeEvent][$targetKey] ?? [];
                $invalids   = $validation['invalid'] ?? [];
                $deprecated = $validation['deprecated'] ?? [];
            @endphp

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                {{-- Block Header --}}
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-blue-100 text-blue-700'  => $tColor === 'blue',
                            'bg-amber-100 text-amber-700' => $tColor === 'amber',
                            'bg-rose-100 text-rose-700'  => $tColor === 'rose',
                            'bg-gray-100 text-gray-700'  => !in_array($tColor, ['blue','amber','rose']),
                        ])>
                            {{ $tLabel }}
                        </span>
                        @if(!empty($invalids))
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                {{ count($invalids) }} shortcode invalid
                            </span>
                        @endif
                        @if(!empty($deprecated))
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                {{ count($deprecated) }} alias lama
                            </span>
                        @endif
                    </div>
                    <button
                        type="button"
                        wire:click="resetTarget('{{ $activeEvent }}', '{{ $targetKey }}')"
                        wire:confirm="Reset template {{ $tLabel }} ke default? Perubahan Anda akan hilang."
                        class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 transition"
                    >
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                        Reset ke default
                    </button>
                </div>

                {{-- Validation Warnings --}}
                @if(!empty($invalids))
                    <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <p class="text-sm font-medium text-red-700 mb-1">Shortcode Tidak Valid</p>
                        @foreach($invalids as $warn)
                            <p class="text-xs text-red-600">• {{ $warn }}</p>
                        @endforeach
                    </div>
                @endif

                @if(!empty($deprecated))
                    <div class="mx-5 mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                        <p class="text-sm font-medium text-amber-700 mb-1">Alias Lama Terdeteksi</p>
                        @foreach($deprecated as $warn)
                            <p class="text-xs text-amber-600">• {{ $warn }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Toggle Row --}}
                <div class="px-5 pt-4 pb-2">
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                wire:model="templates.{{ $activeEvent }}.{{ $targetKey }}.email_enabled"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                            >
                            <span class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                <x-heroicon-o-envelope class="w-4 h-4 text-gray-400" />
                                Email Aktif
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                wire:model="templates.{{ $activeEvent }}.{{ $targetKey }}.whatsapp_enabled"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                            >
                            <span class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400" />
                                WhatsApp Aktif
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Two-column content: Email (left) + WhatsApp (right) --}}
                <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- Email Column --}}
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                            <x-heroicon-o-envelope class="w-4 h-4 text-gray-400" />
                            <span class="text-sm font-semibold text-gray-600">Email</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">
                                Subject Email
                            </label>
                            <input
                                type="text"
                                wire:model.defer="templates.{{ $activeEvent }}.{{ $targetKey }}.email_subject"
                                placeholder="Contoh: Order Anda Berhasil Dibuat - {order_number}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">
                                Body Email
                            </label>
                            <textarea
                                wire:model.defer="templates.{{ $activeEvent }}.{{ $targetKey }}.email_body"
                                rows="10"
                                placeholder="<div style=&quot;font-family:Arial,sans-serif&quot;><h2>Halo {member_name}</h2><p>Pembayaran Anda untuk order <strong>{order_number}</strong> sudah kami terima.</p><a href=&quot;{payment_url}&quot; style=&quot;display:inline-block;background:#0f766e;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none&quot;>Lihat Detail</a></div>"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-primary-500 focus:ring-primary-500 focus:outline-none resize-y"
                            ></textarea>
                            <p class="text-xs text-gray-400">Mendukung HTML email dan inline CSS. Gunakan shortcode {snake_case}; klik tombol <strong>Shortcode</strong> di atas untuk melihat daftar.</p>
                        </div>
                    </div>

                    {{-- WhatsApp Column --}}
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400" />
                            <span class="text-sm font-semibold text-gray-600">WhatsApp</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-600 uppercase tracking-wide">
                                Pesan WhatsApp
                            </label>
                            <textarea
                                wire:model.defer="templates.{{ $activeEvent }}.{{ $targetKey }}.whatsapp_body"
                                rows="10"
                                placeholder="Tulis template WhatsApp di sini. Gunakan {shortcode} untuk data dinamis."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-primary-500 focus:ring-primary-500 focus:outline-none resize-y"
                            ></textarea>
                            <p class="text-xs text-gray-400">Teks biasa. Jangan gunakan format @{{double_brace}} — itu khusus Landing Page.</p>
                        </div>

                        {{-- Preview dummy shortcodes --}}
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Shortcode Tersedia</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($shortcodesForEvent as $sc)
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $sc['shortcode'] }}')"
                                        title="{{ $sc['label'] }}: {{ $sc['example'] }}"
                                        class="inline-flex items-center rounded bg-white border border-gray-200 px-2 py-0.5 text-xs font-mono text-primary-600 hover:border-primary-300 hover:bg-primary-50 transition cursor-pointer"
                                    >
                                        {{ $sc['shortcode'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                <x-heroicon-o-document-text class="mx-auto w-10 h-10 text-gray-300 mb-3" />
                <p class="text-sm text-gray-500">Belum ada template untuk event ini.</p>
                <p class="text-xs text-gray-400 mt-1">Jalankan seeder untuk mengisi template default.</p>
                <code class="mt-2 block text-xs bg-gray-100 rounded px-3 py-1.5 text-gray-600">php artisan db:seed --class=NotificationTemplateSeeder</code>
            </div>
        @endforelse
    </div>

    {{-- ── Shortcode Modal ─────────────────────────────────────────────── --}}
    <div
        x-data="{ open: false }"
        @open-shortcode-modal.window="open = true"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/40"
            @click="open = false"
        ></div>

        {{-- Modal Panel --}}
        <div
            class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl bg-white shadow-2xl"
            @click.stop
        >
            {{-- Modal Header --}}
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Shortcode Notifikasi</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Event: <span class="font-medium text-primary-600">{{ $activeEvent }}</span>
                    </p>
                </div>
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4 space-y-4">

                {{-- Canonical Shortcodes --}}
                @forelse($shortcodesForEvent as $sc)
                    <div class="flex items-start gap-4 rounded-lg border border-gray-100 p-4 hover:bg-gray-50 transition group">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <code class="text-sm font-mono font-semibold text-primary-600 bg-primary-50 rounded px-2 py-0.5">
                                    {{ $sc['shortcode'] }}
                                </code>
                                <span class="text-sm font-medium text-gray-700">{{ $sc['label'] }}</span>
                                @if($sc['safe_for_email'] && $sc['safe_for_whatsapp'])
                                    <span class="text-xs text-gray-400">Email + WA</span>
                                @elseif($sc['safe_for_email'])
                                    <span class="text-xs text-gray-400">Email only</span>
                                @elseif($sc['safe_for_whatsapp'])
                                    <span class="text-xs text-gray-400">WA only</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">{{ $sc['description'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Contoh: <span class="font-medium text-gray-600">{{ $sc['example'] }}</span></p>
                        </div>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $sc['shortcode'] }}').then(() => { this.textContent = '✓ Copied'; setTimeout(() => { this.textContent = 'Copy'; }, 1500); })"
                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-primary-300 hover:text-primary-600 transition"
                        >
                            Copy
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Tidak ada shortcode untuk event ini.</p>
                @endforelse

                {{-- Alias Compatibility Section --}}
                @if(!empty($allAliases))
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Alias Lama (Backward Compatibility)</p>
                        <div class="space-y-2">
                            @foreach($allAliases as $alias => $canonical)
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <code class="font-mono bg-amber-50 text-amber-600 rounded px-1.5 py-0.5">{{ '{' . $alias . '}' }}</code>
                                    <span class="text-gray-300">→</span>
                                    <span>{{ $canonical }}</span>
                                    <span class="text-gray-400">(didukung, tetapi gunakan canonical)</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="sticky bottom-0 border-t border-gray-100 bg-white px-6 py-3 flex justify-end">
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }

        .epic-save-button {
            position: relative;
            overflow: hidden;
            border-radius: 0.9rem;
            border: 1px solid color-mix(in oklab, var(--primary-700) 68%, #0f172a 32%);
            background:
                linear-gradient(180deg, color-mix(in oklab, var(--primary-400) 82%, white 18%) 0%, color-mix(in oklab, var(--primary-600) 88%, #0f172a 12%) 52%, color-mix(in oklab, var(--primary-700) 82%, #082f49 18%) 100%);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.26),
                inset 0 -1px 0 rgba(255, 255, 255, 0.08),
                0 1px 0 rgba(15, 23, 42, 0.16),
                0 8px 18px color-mix(in oklab, var(--primary-700) 24%, transparent),
                0 14px 28px rgba(14, 116, 144, 0.18);
            transform: translateY(0);
            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                filter 180ms ease;
        }

        .epic-save-button::before {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: 0.78rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.03));
            pointer-events: none;
        }

        .epic-save-button::after {
            content: '';
            position: absolute;
            top: -140%;
            left: -38%;
            width: 34%;
            height: 380%;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.08) 18%, rgba(255, 255, 255, 0.5) 50%, rgba(255, 255, 255, 0.08) 82%, transparent 100%);
            transform: rotate(24deg) translateX(-220%);
            transition: transform 720ms ease;
            pointer-events: none;
            mix-blend-mode: screen;
        }

        .epic-save-button:hover {
            transform: translateY(-1px);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.3),
                inset 0 -1px 0 rgba(255, 255, 255, 0.1),
                0 2px 0 rgba(15, 23, 42, 0.18),
                0 14px 24px color-mix(in oklab, var(--primary-700) 28%, transparent),
                0 18px 34px rgba(14, 116, 144, 0.22);
            filter: saturate(1.06);
        }

        .epic-save-button:hover::after {
            transform: rotate(24deg) translateX(520%);
        }

        .epic-save-button:active {
            transform: translateY(1px);
            box-shadow:
                inset 0 2px 6px rgba(15, 23, 42, 0.18),
                0 1px 0 rgba(15, 23, 42, 0.12),
                0 6px 14px color-mix(in oklab, var(--primary-700) 18%, transparent);
        }

        .epic-save-button:focus-visible {
            outline: none;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.26),
                inset 0 -1px 0 rgba(255, 255, 255, 0.08),
                0 1px 0 rgba(15, 23, 42, 0.16),
                0 8px 18px color-mix(in oklab, var(--primary-700) 24%, transparent),
                0 14px 28px rgba(14, 116, 144, 0.18),
                0 0 0 3px color-mix(in oklab, var(--primary-300) 36%, transparent);
        }

        .epic-save-button > * {
            position: relative;
            z-index: 1;
        }
    </style>
</x-filament-panels::page>
