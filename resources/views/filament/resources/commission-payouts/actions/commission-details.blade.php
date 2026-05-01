<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm font-semibold text-gray-900">
            {{ $record->user?->name ?? $record->store_name ?? 'Member tanpa nama' }}
        </div>
        <div class="mt-1 text-sm text-gray-600">
            {{ $record->epic_code }} · {{ $commissions->count() }} komisi eligible · Rp {{ number_format((float) $commissions->sum('commission_amount'), 0, ',', '.') }}
        </div>
    </div>

    @if ($commissions->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
            Tidak ada komisi eligible saat ini.
        </div>
    @else
        <div class="max-h-[420px] overflow-auto rounded-xl border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Komisi</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Buyer</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($commissions as $commission)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $commission->product?->title ?? 'Produk tidak ditemukan' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $commission->order?->order_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $commission->buyer?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $commission->approved_at?->format('d M Y H:i') ?? $commission->created_at?->format('d M Y H:i') ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
