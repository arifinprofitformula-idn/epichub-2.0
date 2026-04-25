<footer class="mt-auto flex flex-col gap-3 border-t border-slate-200/80 px-1 pt-6 pb-0 text-sm text-slate-500 md:flex-row md:items-center md:justify-between md:pb-0">
    <div>© 2026 EPIC Hub</div>

    <div class="flex items-center gap-3">
        <flux:modal.trigger name="dashboard-terms-of-service">
            <button type="button" class="transition hover:text-slate-900">
                Terms of Service
            </button>
        </flux:modal.trigger>

        <span class="text-slate-300">|</span>

        <flux:modal.trigger name="dashboard-privacy-policy">
            <button type="button" class="transition hover:text-slate-900">
                Privacy
            </button>
        </flux:modal.trigger>
    </div>
</footer>

<flux:modal name="dashboard-terms-of-service" class="max-w-3xl">
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Terms of Service</h2>
            <p class="mt-2 text-sm text-slate-500">Template syarat dan ketentuan penggunaan platform EPIC Hub.</p>
        </div>

        <div class="space-y-4 text-sm leading-relaxed text-slate-600">
            <p>Dengan menggunakan EPIC Hub, pengguna setuju untuk memakai platform ini secara sah, wajar, dan tidak melanggar hukum maupun hak pihak lain.</p>
            <p>Akses terhadap produk digital, kelas, event, dan fitur lain diberikan sesuai jenis pembelian, entitlement, atau persetujuan admin yang berlaku pada akun pengguna.</p>
            <p>Pengguna dilarang mendistribusikan ulang materi, membagikan akses akun, mencoba mengganggu sistem, atau menggunakan platform untuk aktivitas yang merugikan EPIC Hub maupun pengguna lain.</p>
            <p>EPIC Hub berhak memperbarui fitur, kebijakan, harga, maupun ketentuan layanan dari waktu ke waktu untuk menjaga kualitas layanan dan keamanan sistem.</p>
            <p>Apabila ditemukan penyalahgunaan, EPIC Hub dapat membatasi akses, menangguhkan akun, atau mengambil tindakan administratif lain sesuai kebijakan internal.</p>
        </div>

        <div class="flex justify-end">
            <flux:modal.close>
                <button type="button" class="inline-flex items-center justify-center rounded-[1rem] bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Tutup
                </button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>

<flux:modal name="dashboard-privacy-policy" class="max-w-3xl">
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Privacy Policy</h2>
            <p class="mt-2 text-sm text-slate-500">Template kebijakan privasi untuk penggunaan platform EPIC Hub.</p>
        </div>

        <div class="space-y-4 text-sm leading-relaxed text-slate-600">
            <p>EPIC Hub dapat mengumpulkan data dasar pengguna seperti nama, email, aktivitas pembelian, progress belajar, dan data teknis yang diperlukan untuk menjalankan layanan.</p>
            <p>Data digunakan untuk autentikasi, pemberian akses produk, pengalaman belajar yang lebih baik, dukungan pengguna, analitik operasional, dan kebutuhan administratif platform.</p>
            <p>Data pengguna tidak dibagikan secara sembarangan kepada pihak lain di luar kebutuhan layanan, kepatuhan hukum, atau integrasi sistem yang memang diperlukan untuk operasional.</p>
            <p>EPIC Hub berupaya menjaga keamanan data dengan kontrol akses, validasi sistem, dan praktik pengelolaan data yang wajar sesuai kebutuhan aplikasi.</p>
            <p>Pengguna dapat menghubungi admin atau pengelola platform untuk permintaan pembaruan data, pertanyaan privasi, atau klarifikasi terkait kebijakan ini.</p>
        </div>

        <div class="flex justify-end">
            <flux:modal.close>
                <button type="button" class="inline-flex items-center justify-center rounded-[1rem] bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Tutup
                </button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
