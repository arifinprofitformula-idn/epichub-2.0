<x-filament-panels::page>
    @php
        $tokenStored     = $tokenStored ?? false;
        $mailketingLists = $mailketingLists ?? collect();
        $recentLogs      = $recentLogs ?? collect();
    @endphp

    <div class="space-y-6">

        {{-- ── Status banner ────────────────────────────────────────────── --}}
        <div @class([
            'rounded-xl border px-5 py-4 flex items-center gap-3',
            'bg-emerald-50 border-emerald-200 text-emerald-800' => $isEnabled,
            'bg-amber-50 border-amber-200 text-amber-800'       => !$isEnabled,
        ])>
            @if ($isEnabled)
                <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                <span class="font-medium">Mailketing <strong>Aktif</strong></span>
            @else
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500 shrink-0" />
                <span class="font-medium">Mailketing <strong>Tidak Aktif</strong> — aktifkan di form di bawah dan simpan.</span>
            @endif
        </div>

        {{-- ── Form Settings ─────────────────────────────────────────────── --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-gray-500" />
                <h3 class="font-semibold text-gray-700">Mailketing Integration Settings</h3>
            </div>

            <div class="p-5 space-y-6">

                {{-- Toggle row ------------------------------------------------ --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-gray-50">
                        <div>
                            <p class="font-medium text-sm text-gray-700">Aktifkan Mailketing</p>
                            <p class="text-xs text-gray-500">Master switch integrasi email</p>
                        </div>
                        <x-filament::input.wrapper>
                            <input type="checkbox" wire:model="enable_mailketing" id="enable_mailketing"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        </x-filament::input.wrapper>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-gray-50">
                        <div>
                            <p class="font-medium text-sm text-gray-700">Email Logs</p>
                            <p class="text-xs text-gray-500">Catat semua pengiriman email</p>
                        </div>
                        <x-filament::input.wrapper>
                            <input type="checkbox" wire:model="enable_email_logs" id="enable_email_logs"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        </x-filament::input.wrapper>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-gray-50">
                        <div>
                            <p class="font-medium text-sm text-gray-700">Email Queue</p>
                            <p class="text-xs text-gray-500">Nonaktifkan untuk shared hosting</p>
                        </div>
                        <x-filament::input.wrapper>
                            <input type="checkbox" wire:model="enable_email_queue" id="enable_email_queue"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        </x-filament::input.wrapper>
                    </div>
                </div>

                {{-- API Credentials --------------------------------------------- --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Kredensial API</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="space-y-1">
                            <label for="mailketing_api_token" class="text-sm font-medium text-gray-700">
                                API Token
                                @if($tokenStored)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                        ✓ Token tersimpan
                                    </span>
                                @endif
                            </label>
                            <input type="password" id="mailketing_api_token" wire:model="mailketing_api_token"
                                placeholder="{{ $tokenStored ? '••••••••••••••••••••• (kosongkan jika tidak ingin mengubah)' : 'Masukkan API token Mailketing' }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                            <p class="text-xs text-gray-500">Token disimpan terenkripsi. Kosongkan field ini jika tidak ingin mengubah token yang sudah tersimpan.</p>
                        </div>

                        <div class="space-y-1">
                            <label for="test_recipient_email" class="text-sm font-medium text-gray-700">Test Recipient Email</label>
                            <input type="email" id="test_recipient_email" wire:model="test_recipient_email"
                                placeholder="test@example.com"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                            <p class="text-xs text-gray-500">Email tujuan saat klik "Send Test Email"</p>
                        </div>

                    </div>
                </div>

                {{-- Sender -------------------------------------------------------- --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Pengirim</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label for="mailketing_from_name" class="text-sm font-medium text-gray-700">From Name</label>
                            <input type="text" id="mailketing_from_name" wire:model="mailketing_from_name"
                                placeholder="EPIC HUB"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label for="mailketing_from_email" class="text-sm font-medium text-gray-700">From Email</label>
                            <input type="email" id="mailketing_from_email" wire:model="mailketing_from_email"
                                placeholder="noreply@example.com"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label for="mailketing_reply_to_email" class="text-sm font-medium text-gray-700">Reply-To Email <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <input type="email" id="mailketing_reply_to_email" wire:model="mailketing_reply_to_email"
                                placeholder="support@example.com"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Notification ------------------------------------------------- --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Notifikasi Admin</h4>
                    <div class="space-y-1 max-w-lg">
                        <label for="admin_notification_email" class="text-sm font-medium text-gray-700">Admin Notification Email <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input type="text" id="admin_notification_email" wire:model="admin_notification_email"
                            placeholder="admin@example.com, finance@example.com"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        <p class="text-xs text-gray-500">Pisahkan beberapa email dengan koma</p>
                    </div>
                </div>

                {{-- Mailketing List IDs ------------------------------------------ --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                        Mailketing List IDs
                        @if($mailketingLists->isNotEmpty())
                            <span class="ml-2 text-xs font-normal text-gray-400 normal-case">
                                ({{ $mailketingLists->count() }} list tersedia — gunakan ID dari tabel di bawah)
                            </span>
                        @endif
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach([
                            ['mailketing_default_list_id',           'Default List ID',             'Untuk semua subscriber umum'],
                            ['mailketing_customer_list_id',          'Customer List ID',             'Untuk pembeli/customer'],
                            ['mailketing_epi_channel_list_id',       'EPI Channel List ID',         'Untuk anggota EPI Channel'],
                            ['mailketing_event_participant_list_id', 'Event Participant List ID',   'Untuk peserta event'],
                            ['mailketing_course_student_list_id',    'Course Student List ID',      'Untuk peserta kursus'],
                        ] as [$field, $label, $hint])
                            <div class="space-y-1">
                                <label for="{{ $field }}" class="text-sm font-medium text-gray-700">{{ $label }}</label>
                                <input type="text" id="{{ $field }}" wire:model="{{ $field }}"
                                    placeholder="Contoh: 1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                                <p class="text-xs text-gray-500">{{ $hint }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Mailketing Lists Table ─────────────────────────────────────── --}}
        @if($mailketingLists->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <x-heroicon-o-list-bullet class="w-5 h-5 text-gray-500" />
                <h3 class="font-semibold text-gray-700">Mailketing Lists ({{ $mailketingLists->count() }})</h3>
                <span class="text-xs text-gray-400 ml-auto">
                    Terakhir sync: {{ $mailketingLists->max('synced_at')?->diffForHumans() ?? '-' }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">List ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">List Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Synced At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($mailketingLists as $list)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-mono text-gray-800">{{ $list->list_id }}</code>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $list->list_name }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $list->synced_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-gray-500 text-sm">
            Belum ada Mailketing list yang di-sync. Klik <strong>Sync Lists</strong> atau <strong>Test Connection</strong> untuk mengambil list dari Mailketing.
        </div>
        @endif

        {{-- ── Recent Email Logs ──────────────────────────────────────────── --}}
        @if($recentLogs->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-500" />
                <h3 class="font-semibold text-gray-700">5 Email Log Terakhir</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $log->created_at?->format('d M H:i') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $log->recipient_email }}</td>
                            <td class="px-4 py-3 text-gray-600 truncate max-w-xs">{{ $log->subject }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badgeClass = match($log->status) {
                                        'sent'    => 'bg-emerald-100 text-emerald-700',
                                        'failed'  => 'bg-red-100 text-red-700',
                                        'skipped' => 'bg-gray-100 text-gray-600',
                                        default   => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
