<x-filament-panels::page>
    @php
        /** @var \App\Filament\Resources\Users\Pages\ViewUser $this */
        /** @var \App\Models\User $record */
        $record = $this->getRecord();
        $summary = $this->getSummaryStats();
        $referral = $this->getReferralOverview();
        $oms = $this->getOmsSummary();
        $courseProgressRows = $this->getCourseProgressRows();
        $orders = $this->getLatestOrders();
        $payments = $this->getLatestPayments();
        $userProducts = $this->getLatestUserProducts();
        $eventRegistrations = $this->getLatestEventRegistrations();
        $omsLogs = $this->getLatestOmsLogs();
        $accessLogs = $this->getLatestAccessLogs();
        $roles = $record->roles->pluck('name');
        $permissions = $record->getAllPermissions()->pluck('name');
        $tabs = [
            'ringkasan' => 'Ringkasan',
            'profil' => 'Profil & Akun',
            'role' => 'Role & Izin',
            'order' => 'Order & Pembayaran',
            'akses' => 'Akses Produk',
            'course' => 'Progress Kelas',
            'event' => 'Event',
            'referral' => 'Referral & EPI Channel',
            'oms' => 'Sinkronisasi OMS',
            'activity' => 'Log Aktivitas',
        ];
    @endphp

    <div class="space-y-6" x-data="{ tab: 'ringkasan' }">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.5fr)_360px]">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="shrink-0">
                            @if (filled($record->profile_photo_url))
                                <img src="{{ $record->profile_photo_url }}"
                                     alt="{{ $record->name }}"
                                     class="size-16 rounded-full object-cover ring-2 ring-gray-100">
                            @else
                                <div class="flex size-16 items-center justify-center rounded-full bg-gray-100 text-xl font-semibold text-gray-500 ring-2 ring-gray-100">
                                    {{ $record->initials() }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-500">Pengguna</div>
                            <h1 class="mt-1 text-2xl font-semibold text-gray-950">{{ $record->name }}</h1>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                <span>{{ $record->email }}</span>
                                @if (filled($record->whatsapp_number))
                                    <span>•</span>
                                    <span>{{ $record->whatsapp_number }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium {{ $record->email_verified_at ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                            {{ $record->email_verified_at ? 'Email Terverifikasi' : 'Email Belum Terverifikasi' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium {{ $record->epiChannel?->status?->value === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                            EPI Channel: {{ $record->epiChannel?->status?->label() ?? 'Belum' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium {{ $record->hasLockedReferrer() ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                            Referral: {{ $record->referralLockStatusLabel() }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm font-medium text-gray-500">Snapshot</div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Kode EPIC</div>
                        <div class="mt-2 text-lg font-semibold text-gray-950">{{ $record->epiChannel?->epic_code ?? '-' }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Aktivitas Terakhir</div>
                        <div class="mt-2 text-sm font-medium text-gray-900">
                            {{ $summary['last_activity_at'] ? \Illuminate\Support\Carbon::parse($summary['last_activity_at'])->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">OMS</div>
                        <div class="mt-2 text-sm font-medium text-gray-900">{{ $summary['oms_status'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    @foreach ($tabs as $key => $label)
                        <button
                            type="button"
                            @click="tab = '{{ $key }}'"
                            class="rounded-full px-3 py-2 text-sm font-medium transition"
                            :class="tab === '{{ $key }}' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="p-4 md:p-6">
                <div x-show="tab === 'ringkasan'" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">Total order</div>
                            <div class="mt-2 text-2xl font-semibold text-gray-950">{{ $summary['total_orders'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">Pembayaran paid</div>
                            <div class="mt-2 text-2xl font-semibold text-gray-950">{{ $summary['total_paid_payments'] }}</div>
                            <div class="mt-1 text-xs text-gray-500">Rp {{ number_format($summary['total_paid_amount'], 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">Produk aktif</div>
                            <div class="mt-2 text-2xl font-semibold text-gray-950">{{ $summary['total_active_products'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">Course dimiliki</div>
                            <div class="mt-2 text-2xl font-semibold text-gray-950">{{ $summary['total_owned_courses'] }}</div>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-3">
                        <div class="rounded-2xl border border-gray-200 p-5">
                            <div class="text-sm font-medium text-gray-500">Progress course terakhir</div>
                            @if ($summary['latest_course_progress'])
                                <div class="mt-3 text-lg font-semibold text-gray-950">{{ $summary['latest_course_progress']['course_title'] }}</div>
                                <div class="mt-2 text-sm text-gray-600">
                                    {{ $summary['latest_course_progress']['completed_lessons'] }}/{{ $summary['latest_course_progress']['total_lessons'] }} lesson selesai
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-amber-500" style="width: {{ $summary['latest_course_progress']['progress_percent'] }}%"></div>
                                </div>
                            @else
                                <div class="mt-3 text-sm text-gray-500">Belum ada progress kelas.</div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-gray-200 p-5">
                            <div class="text-sm font-medium text-gray-500">Status EPI Channel</div>
                            <div class="mt-3 text-lg font-semibold text-gray-950">{{ $summary['epi_channel_status'] }}</div>
                            <div class="mt-2 text-sm text-gray-600">Referral lock: {{ $summary['locked_referrer'] }}</div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 p-5">
                            <div class="text-sm font-medium text-gray-500">Total komisi & payout</div>
                            <div class="mt-3 text-sm text-gray-700">Komisi: Rp {{ number_format($summary['total_commission_amount'], 0, ',', '.') }}</div>
                            <div class="mt-2 text-sm text-gray-700">Payout: Rp {{ number_format($summary['total_payout_amount'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'profil'" class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-sm font-medium text-gray-500">Profil</div>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Nama</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->name }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Email</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->email }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">WhatsApp</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->whatsapp_number ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Tanggal daftar</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->created_at?->format('d M Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-sm font-medium text-gray-500">Akun</div>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Email terverifikasi</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->email_verified_at?->format('d M Y H:i') ?? 'Belum' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Aktivitas terakhir</dt>
                                <dd class="text-right font-medium text-gray-900">
                                    {{ $summary['last_activity_at'] ? \Illuminate\Support\Carbon::parse($summary['last_activity_at'])->format('d M Y H:i') : '-' }}
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Sumber pendaftaran/referral</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->referral_source ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Status akun</dt>
                                <dd class="text-right font-medium text-gray-900">Field tidak tersedia</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div x-show="tab === 'role'" class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-sm font-medium text-gray-500">Role</div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($roles as $role)
                                <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">{{ $role }}</span>
                            @empty
                                <span class="text-sm text-gray-500">Belum ada role.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-sm font-medium text-gray-500">Izin efektif</div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($permissions as $permission)
                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700">{{ $permission }}</span>
                            @empty
                                <span class="text-sm text-gray-500">Belum ada permission.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'order'" class="space-y-6">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-base font-semibold text-gray-950">Riwayat Order</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Order</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Referrer</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Total</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($orders as $order)
                                        <tr>
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $order->order_number }}</td>
                                            <td class="px-3 py-2">{{ $order->status?->label() ?? $order->status }}</td>
                                            <td class="px-3 py-2">{{ $order->referrerEpiChannel?->epic_code ?? '-' }}</td>
                                            <td class="px-3 py-2">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2">{{ $order->created_at?->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Belum ada order.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-base font-semibold text-gray-950">Riwayat Pembayaran</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Payment</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Order</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Nominal</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Paid at</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($payments as $payment)
                                        <tr>
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $payment->payment_number }}</td>
                                            <td class="px-3 py-2">{{ $payment->order?->order_number ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $payment->status?->label() ?? $payment->status }}</td>
                                            <td class="px-3 py-2">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Belum ada pembayaran.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'akses'" class="space-y-4">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-base font-semibold text-gray-950">Produk Saya / Akses</div>
                                <div class="mt-1 text-sm text-gray-500">Grant atau revoke tetap memakai flow Entitlements yang sudah ada.</div>
                            </div>
                            <a
                                href="{{ $this->getUserProductsIndexUrl() }}"
                                class="inline-flex items-center rounded-full border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50"
                            >
                                Buka Entitlements
                            </a>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Produk</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Tipe</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Granted</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Expired</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Sumber</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($userProducts as $userProduct)
                                        <tr>
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $userProduct->product?->title ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $userProduct->product?->product_type?->label() ?? ($userProduct->product?->product_type?->value ?? '-') }}</td>
                                            <td class="px-3 py-2">{{ $userProduct->status?->label() ?? $userProduct->status }}</td>
                                            <td class="px-3 py-2">{{ $userProduct->granted_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $userProduct->expires_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2">
                                                @if ($userProduct->sourceProduct?->title)
                                                    Bundle: {{ $userProduct->sourceProduct->title }}
                                                @elseif ($userProduct->order?->order_number)
                                                    Order {{ $userProduct->order->order_number }}
                                                @else
                                                    Manual
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">Belum ada akses produk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'course'" class="space-y-4">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-base font-semibold text-gray-950">Progress Kelas</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Course</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Total Lesson</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Selesai</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Progress</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Terakhir Belajar</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($courseProgressRows as $row)
                                        <tr>
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $row['course_title'] }}</td>
                                            <td class="px-3 py-2">{{ $row['total_lessons'] }}</td>
                                            <td class="px-3 py-2">{{ $row['completed_lessons'] }}</td>
                                            <td class="px-3 py-2">{{ $row['progress_percent'] }}%</td>
                                            <td class="px-3 py-2">{{ $row['last_studied_at']?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Belum ada progress kelas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'event'" class="space-y-4">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-base font-semibold text-gray-950">Event</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Event</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Produk</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Registered</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Attended</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($eventRegistrations as $registration)
                                        <tr>
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $registration->event?->title ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $registration->status?->label() ?? $registration->status }}</td>
                                            <td class="px-3 py-2">{{ $registration->userProduct?->product?->title ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $registration->registered_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $registration->attended_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Belum ada event registration.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'referral'" class="grid gap-4 xl:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-sm font-medium text-gray-500">Locked Referrer</div>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Status</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $record->referralLockStatusLabel() }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Referrer</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['referrer_name'] ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Kode EPIC Referrer</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['referrer_epic_code'] ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Dikunci pada</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['locked_at']?->format('d M Y H:i') ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Sumber</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['referral_source'] ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-sm font-medium text-gray-500">EPI Channel</div>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Referral code</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['epic_code'] ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Status</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['epi_channel_status'] }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Referral visits</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['referral_visits_count'] }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Referral orders</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $referral['referral_orders_count'] }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Total komisi</dt>
                                <dd class="text-right font-medium text-gray-900">Rp {{ number_format($referral['commissions_total'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Total payout</dt>
                                <dd class="text-right font-medium text-gray-900">Rp {{ number_format($referral['payouts_total'], 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div x-show="tab === 'oms'" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">OMS status</div>
                            <div class="mt-2 text-lg font-semibold text-gray-950">{{ $oms['status'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">EPIC code</div>
                            <div class="mt-2 text-lg font-semibold text-gray-950">{{ $oms['epic_code'] ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">Last sync</div>
                            <div class="mt-2 text-sm font-medium text-gray-900">{{ $oms['last_sync_at']?->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm text-gray-500">Last error</div>
                            <div class="mt-2 text-sm font-medium text-gray-900">{{ $oms['last_error'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-base font-semibold text-gray-950">Riwayat Sinkronisasi OMS</div>
                            <div class="text-xs text-gray-500">Sync manual tidak ditampilkan karena belum ada service outbound aman yang khusus untuk halaman ini.</div>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Waktu</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Action</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Response</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Error</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($omsLogs as $log)
                                        <tr>
                                            <td class="px-3 py-2">{{ $log->processed_at?->format('d M Y H:i') ?? $log->created_at?->format('d M Y H:i') }}</td>
                                            <td class="px-3 py-2">{{ $log->action }}</td>
                                            <td class="px-3 py-2">{{ $log->status?->label() ?? $log->status }}</td>
                                            <td class="px-3 py-2">{{ $log->response_code ?? '-' }}{{ $log->http_status ? ' / '.$log->http_status : '' }}</td>
                                            <td class="px-3 py-2">{{ $log->error_message ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">Belum ada log OMS.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'activity'" class="space-y-4">
                    <div class="rounded-2xl border border-gray-200 p-5">
                        <div class="text-base font-semibold text-gray-950">Log Aktivitas</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Waktu</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Action</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Subject</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Actor</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">IP</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">User Agent</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($accessLogs as $log)
                                        <tr>
                                            <td class="px-3 py-2">{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $log->action?->label() ?? $log->action }}</td>
                                            <td class="px-3 py-2">{{ $this->getActivitySubjectLabel($log) }}</td>
                                            <td class="px-3 py-2">{{ $log->actor?->email ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $log->ip_address ?? '-' }}</td>
                                            <td class="px-3 py-2 max-w-[240px] truncate" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">Belum ada log aktivitas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
