<x-filament-panels::page>
    @php
        $apiKeyStored = $apiKeyStored ?? false;
        $dripsenderLists = $dripsenderLists ?? collect();
        $recentLogs = $recentLogs ?? collect();
    @endphp

    <div class="space-y-6">
        <div @class([
            'rounded-xl border px-5 py-4 flex items-center gap-3',
            'bg-emerald-50 border-emerald-200 text-emerald-800' => $isEnabled,
            'bg-amber-50 border-amber-200 text-amber-800' => ! $isEnabled,
        ])>
            @if ($isEnabled)
                <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                <span class="font-medium">DripSender <strong>Aktif</strong></span>
            @else
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500 shrink-0" />
                <span class="font-medium">DripSender <strong>Tidak Aktif</strong> - aktifkan di form di bawah dan simpan.</span>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-gray-500" />
                <h3 class="font-semibold text-gray-700">WhatsApp Integration - DripSender</h3>
            </div>

            <div class="p-5 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach([
                        ['enable_dripsender', 'Aktifkan DripSender', 'Master switch integrasi WhatsApp'],
                        ['dripsender_enable_logs', 'WhatsApp Logs', 'Catat semua pengiriman WhatsApp'],
                        ['dripsender_enable_queue', 'WhatsApp Queue', 'Nonaktifkan untuk shared hosting'],
                    ] as [$field, $label, $hint])
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-gray-50">
                            <div>
                                <p class="font-medium text-sm text-gray-700">{{ $label }}</p>
                                <p class="text-xs text-gray-500">{{ $hint }}</p>
                            </div>
                            <input type="checkbox" wire:model="{{ $field }}" id="{{ $field }}"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        </div>
                    @endforeach
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Kredensial API</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="dripsender_api_key" class="text-sm font-medium text-gray-700">
                                API Key
                                @if($apiKeyStored)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                        ✓ API key tersimpan
                                    </span>
                                @endif
                            </label>
                            <input type="password" id="dripsender_api_key" wire:model="dripsender_api_key"
                                placeholder="{{ $apiKeyStored ? '•••••••••••••••• (kosongkan jika tidak ingin mengubah)' : 'Masukkan API key DripSender' }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                            <p class="text-xs text-gray-500">API key disimpan terenkripsi dan tidak akan ditampilkan penuh setelah tersimpan.</p>
                        </div>
                        <div class="space-y-1">
                            <label for="dripsender_base_url" class="text-sm font-medium text-gray-700">Base URL</label>
                            <input type="text" id="dripsender_base_url" wire:model="dripsender_base_url"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label for="dripsender_default_country_code" class="text-sm font-medium text-gray-700">Default Country Code</label>
                            <input type="text" id="dripsender_default_country_code" wire:model="dripsender_default_country_code"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label for="dripsender_default_footer" class="text-sm font-medium text-gray-700">Default Footer</label>
                            <input type="text" id="dripsender_default_footer" wire:model="dripsender_default_footer"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label for="dripsender_test_phone" class="text-sm font-medium text-gray-700">Test Phone</label>
                            <input type="text" id="dripsender_test_phone" wire:model="dripsender_test_phone"
                                placeholder="6281234567890"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label for="dripsender_admin_phone_numbers" class="text-sm font-medium text-gray-700">Admin Phone Numbers</label>
                            <textarea id="dripsender_admin_phone_numbers" wire:model="dripsender_admin_phone_numbers" rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                                placeholder="6281234567890&#10;6282234567890"></textarea>
                            <p class="text-xs text-gray-500">Pisahkan beberapa nomor dengan koma atau baris baru.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">List Mapping</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach([
                            ['dripsender_default_list_id', 'Default List ID'],
                            ['dripsender_customer_list_id', 'Customer List ID'],
                            ['dripsender_epi_channel_list_id', 'EPI Channel List ID'],
                            ['dripsender_event_participant_list_id', 'Event Participant List ID'],
                            ['dripsender_course_student_list_id', 'Course Student List ID'],
                        ] as [$field, $label])
                            <div class="space-y-1">
                                <label for="{{ $field }}" class="text-sm font-medium text-gray-700">{{ $label }}</label>
                                <input type="text" id="{{ $field }}" wire:model="{{ $field }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Notification Toggles</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach([
                            ['whatsapp_notify_user_registered', 'Registrasi Akun'],
                            ['whatsapp_notify_password_reset', 'Reset Password'],
                            ['whatsapp_notify_order_created', 'Order Dibuat'],
                            ['whatsapp_notify_payment_submitted', 'Bukti Pembayaran Dikirim'],
                            ['whatsapp_notify_payment_approved', 'Pembayaran Disetujui'],
                            ['whatsapp_notify_payment_rejected', 'Pembayaran Ditolak'],
                            ['whatsapp_notify_access_granted', 'Akses Produk Diberikan'],
                            ['whatsapp_notify_event_registration', 'Registrasi Event'],
                            ['whatsapp_notify_course_enrollment', 'Enrollment Kelas'],
                            ['whatsapp_notify_commission_created', 'Komisi Affiliate'],
                            ['whatsapp_notify_payout_paid', 'Payout Komisi'],
                            ['whatsapp_notify_admin_order_created', 'Admin: Order Baru'],
                            ['whatsapp_notify_admin_payment_submitted', 'Admin: Payment Submitted'],
                            ['whatsapp_notify_admin_event_registration', 'Admin: Registrasi Event'],
                            ['whatsapp_notify_admin_payout_paid', 'Admin: Payout Paid'],
                        ] as [$field, $label])
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-gray-50">
                                <p class="font-medium text-sm text-gray-700">{{ $label }}</p>
                                <input type="checkbox" wire:model="{{ $field }}" id="{{ $field }}"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Reminder Opsional</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                        @foreach([
                            ['enable_whatsapp_payment_reminder', 'Payment Reminder'],
                            ['enable_whatsapp_event_reminder', 'Event Reminder'],
                            ['event_reminder_day_before', 'Event H-1'],
                            ['event_reminder_hour_before', 'Event 1 Jam'],
                        ] as [$field, $label])
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-gray-50">
                                <p class="font-medium text-sm text-gray-700">{{ $label }}</p>
                                <input type="checkbox" wire:model="{{ $field }}" id="{{ $field }}"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="space-y-1">
                            <label for="payment_reminder_after_hours" class="text-sm font-medium text-gray-700">Payment Reminder After Hours</label>
                            <input type="number" min="1" id="payment_reminder_after_hours" wire:model="payment_reminder_after_hours"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($dripsenderLists->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="w-5 h-5 text-gray-500" />
                    <h3 class="font-semibold text-gray-700">DripSender Lists ({{ $dripsenderLists->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">List ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">List Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacts</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Synced At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($dripsenderLists as $list)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3"><code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">{{ $list->list_id }}</code></td>
                                    <td class="px-4 py-3 text-gray-700">{{ $list->list_name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $list->contact_count ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $list->synced_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($recentLogs->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-500" />
                    <h3 class="font-semibold text-gray-700">5 WhatsApp Log Terakhir</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $log->created_at?->format('d M H:i') }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->recipient_phone }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->event_type }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badgeClass = match($log->status) {
                                                'sent' => 'bg-emerald-100 text-emerald-700',
                                                'failed' => 'bg-red-100 text-red-700',
                                                'skipped' => 'bg-gray-100 text-gray-600',
                                                default => 'bg-amber-100 text-amber-700',
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
