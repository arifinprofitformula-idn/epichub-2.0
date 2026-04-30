<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">Integrasi API OMS ⇄ EPIC Hub</h2>
            <p class="mt-2 text-sm text-slate-600">
                Dokumentasi inbound endpoint OMS untuk create atau resend account EPI Channel setelah registrasi disetujui di OMS.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">OMS integration</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $integrationEnabled ? 'Aktif' : 'Nonaktif' }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Signature secret</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $signatureConfigured ? 'Configured' : 'Missing' }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Password key</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $passwordKeyConfigured ? 'Configured' : 'Missing' }}</div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Endpoint</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Method</div>
                    <div class="mt-2 text-sm font-medium text-slate-900">POST</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">URL</div>
                    <div class="mt-2 break-all rounded-xl bg-slate-950 px-4 py-3 text-sm text-slate-100">{{ $endpointUrl }}</div>
                </div>
            </div>
            <div class="mt-4 text-sm text-slate-600">
                Purpose: Create / Resend Account EPI Channel dari OMS ke EPIC Hub.
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Required Headers</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <li><code>X-OMS-Request-Id</code></li>
                    <li><code>X-OMS-Timestamp</code></li>
                    <li><code>X-OMS-Signature</code></li>
                    <li><code>Authorization: Bearer ...</code> optional hanya jika fallback aktif</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Signature</h3>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-slate-950 px-4 py-3 text-sm text-slate-100">hash_hmac('sha256', timestamp + request_id + raw_body, OMS_SIGNATURE_SECRET)</pre>
                <p class="mt-4 text-sm text-slate-600">
                    Timestamp tolerance saat ini 5 menit. Signature tetap menjadi skema yang direkomendasikan.
                </p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Request Payload</h3>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-slate-950 px-4 py-3 text-sm text-slate-100">{
  "kode_epic": "EPI12345",
  "nama_epic": "Budi Santoso",
  "email_epic": "budi@example.com",
  "no_tlp_epic": "628123456789",
  "nama_epi_store": "Budi Gold Store",
  "sponsor_epic_code": "EPI00001",
  "sponsor_name": "Ahmad Sponsor",
  "encrypted_password": "..."
}</pre>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Alias Payload dari OMS</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <li><code>kode_new_epic</code></li>
                    <li><code>nama_new_epic</code></li>
                    <li><code>email_addr_new_epic</code></li>
                    <li><code>no_tlp_new_epic</code></li>
                    <li><code>nama_epi_store_new_epic</code></li>
                    <li><code>kode_epic_sponsor</code></li>
                    <li><code>nama_epic_sponsor</code></li>
                    <li><code>password_terenkripsi</code></li>
                </ul>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Response Success</h3>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-slate-950 px-4 py-3 text-sm text-slate-100">{
  "response_code": "{{ $successCode }}",
  "message": "Sukses",
  "data": {
    "epic_code": "EPI12345",
    "email": "budi@example.com"
  }
}</pre>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Response Failed</h3>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-slate-950 px-4 py-3 text-sm text-slate-100">{
  "response_code": "{{ $failedCode }}",
  "message": "Gagal",
  "error": "Email sudah terdaftar dengan kode EPIC berbeda."
}</pre>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Security Notes</h3>
            <ul class="mt-4 space-y-2 text-sm text-slate-700">
                <li>Password tidak pernah disimpan plaintext.</li>
                <li>Encrypted password tidak masuk log mentah.</li>
                <li>Decryption key tidak dikirim di payload.</li>
                <li>Request harus signed jika signature secret tersedia.</li>
                <li>Request ID wajib untuk idempotency.</li>
                <li>Duplicate request tidak membuat akun ganda.</li>
            </ul>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Environment Variables</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <li><code>OMS_INTEGRATION_ENABLED</code></li>
                    <li><code>OMS_INBOUND_SECRET</code></li>
                    <li><code>OMS_SIGNATURE_SECRET</code></li>
                    <li><code>OMS_PASSWORD_ENCRYPTION_KEY</code></li>
                    <li><code>OMS_RESPONSE_SUCCESS_CODE=00</code></li>
                    <li><code>OMS_RESPONSE_FAILED_CODE=99</code></li>
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Integration Status</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <li>OMS_INTEGRATION_ENABLED: {{ $integrationEnabled ? 'aktif' : 'tidak aktif' }}</li>
                    <li>Signature secret configured: {{ $signatureConfigured ? 'yes' : 'no' }}</li>
                    <li>Password encryption key configured: {{ $passwordKeyConfigured ? 'yes' : 'no' }}</li>
                    <li>Bearer fallback configured: {{ $bearerConfigured ? 'yes' : 'no' }}</li>
                </ul>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Latest Logs</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Waktu</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Action</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Request ID</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">EPIC Code</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Email</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Status</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Response</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($latestLogs as $log)
                            <tr>
                                <td class="px-3 py-2 text-slate-700">{{ $log->processed_at?->format('d M Y H:i:s') ?? $log->created_at?->format('d M Y H:i:s') ?? '-' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $log->action }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $log->request_id ?: '-' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $log->epic_code ?: '-' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $log->email ?: '-' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $log->status?->label() ?? '-' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $log->response_code ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-slate-500">Belum ada log create account OMS.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
