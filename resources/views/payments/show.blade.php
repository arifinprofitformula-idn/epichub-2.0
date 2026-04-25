<x-layouts::public :title="'Pembayaran '.$payment->payment_number">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('orders.show', $payment->order)">
                ← Kembali ke invoice
            </x-ui.button>

            <x-ui.badge variant="{{ $payment->status->value === 'success' ? 'success' : 'neutral' }}">
                {{ $payment->status->label() }}
            </x-ui.badge>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Instruksi pembayaran</div>

                    <div class="mt-4 grid gap-3 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 text-sm dark:border-zinc-800">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-zinc-600 dark:text-zinc-300">Order No.</div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $payment->order->order_number }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-zinc-600 dark:text-zinc-300">Payment No.</div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $payment->payment_number }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-zinc-600 dark:text-zinc-300">Total</div>
                            <div class="font-semibold text-zinc-900 dark:text-white">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-ui.alert variant="info" title="Langkah pembayaran">
                            Transfer sesuai total di atas ke rekening berikut, lalu upload bukti pembayaran.
                        </x-ui.alert>
                    </div>

                    <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 text-sm dark:border-zinc-800">
                        <div class="font-semibold text-zinc-900 dark:text-white">
                            {{ data_get(config('epichub.payments.manual_bank_transfer'), 'bank_name') }}
                        </div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-300">
                            No. Rek: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_number') }}
                        </div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-300">
                            A/N: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_name') }}
                        </div>
                    </div>

                    @if ($payment->status->value === 'success')
                        <div class="mt-6">
                            <x-ui.alert variant="success" title="Pembayaran terverifikasi">
                                Diverifikasi
                                @if ($payment->verifiedBy)
                                    oleh {{ $payment->verifiedBy->name }}
                                @endif
                                @if ($payment->verified_at)
                                    pada {{ $payment->verified_at->format('d M Y, H:i') }}.
                                @endif
                            </x-ui.alert>
                        </div>
                    @endif
                </x-ui.card>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Bukti pembayaran</div>

                    @if ($payment->proof_of_payment)
                        <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                            Bukti pembayaran sudah diupload.
                        </div>
                        <div class="mt-4">
                            <x-ui.button variant="secondary" size="sm" :href="asset('storage/'.$payment->proof_of_payment)" target="_blank" rel="noopener noreferrer">
                                Lihat bukti
                            </x-ui.button>
                        </div>
                    @else
                        <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                            Upload bukti pembayaran (JPG/PNG/PDF, maks 5MB).
                        </div>
                    @endif

                    @if ($payment->status->value !== 'success')
                        <form class="mt-5 grid gap-3" method="POST" action="{{ route('payments.proof.store', $payment) }}" enctype="multipart/form-data">
                            @csrf

                            <div>
                                <input
                                    type="file"
                                    name="proof"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="block w-full rounded-[var(--radius-xl)] border border-zinc-200/70 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm file:mr-3 file:rounded-[var(--radius-lg)] file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:file:bg-zinc-900 dark:file:text-white"
                                />
                                @error('proof')
                                    <div class="mt-2 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</div>
                                @enderror
                            </div>

                            <x-ui.button variant="primary" size="sm" type="submit">
                                Upload bukti
                            </x-ui.button>

                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                Pastikan sudah menjalankan <span class="font-semibold">php artisan storage:link</span> agar link preview bekerja.
                            </div>
                        </form>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </section>
</x-layouts::public>

