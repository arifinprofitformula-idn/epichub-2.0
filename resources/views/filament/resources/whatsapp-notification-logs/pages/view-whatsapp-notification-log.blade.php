<x-filament-panels::page>
    @php
        /** @var \App\Models\WhatsAppNotificationLog $record */
        $record = $this->getRecord();
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-semibold text-gray-700">Informasi WhatsApp</h3>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Dibuat</p>
                    <p class="text-gray-800">{{ $record->created_at?->format('d M Y H:i:s') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Event Type</p>
                    <p class="text-gray-800">{{ $record->event_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Provider</p>
                    <p class="text-gray-800">{{ $record->provider }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                    <p class="text-gray-800 font-mono text-xs">{{ $record->recipient_phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Nama Penerima</p>
                    <p class="text-gray-800">{{ $record->recipient_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
                    <p class="text-gray-800">{{ $record->status }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Pesan</p>
                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs whitespace-pre-wrap">{{ $record->message }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-semibold text-gray-700">Response & Error</h3>
            </div>
            <div class="p-5 space-y-4 text-sm">
                @if($record->error_message)
                    <div>
                        <p class="text-xs font-medium text-red-500 uppercase tracking-wide mb-1">Error Message</p>
                        <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-700 text-xs font-mono whitespace-pre-wrap">{{ $record->error_message }}</div>
                    </div>
                @endif

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Provider Response</p>
                    @if($record->provider_response)
                        <pre class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs font-mono text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ json_encode($record->provider_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <p class="text-gray-400 italic">-</p>
                    @endif
                </div>

                @if($record->metadata)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Metadata</p>
                        <pre class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs font-mono text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ json_encode($record->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
