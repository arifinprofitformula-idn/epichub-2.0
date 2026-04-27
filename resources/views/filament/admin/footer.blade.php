<div
    x-data="{ openModal: null }"
    class="fi-admin-footer-shell"
>
    <div class="fi-main fi-width-7xl">
        <footer class="fi-admin-footer">
            <div>&copy; {{ now()->year }} EPIC HUB</div>

            <div class="fi-admin-footer-links">
                <button
                    type="button"
                    class="fi-admin-footer-link"
                    x-on:click="openModal = 'terms'"
                >
                    Terms of Service
                </button>

                <span class="fi-admin-footer-divider">|</span>

                <button
                    type="button"
                    class="fi-admin-footer-link"
                    x-on:click="openModal = 'privacy'"
                >
                    Privacy
                </button>
            </div>
        </footer>
    </div>

    <div
        x-cloak
        x-show="openModal"
        x-on:keydown.escape.window="openModal = null"
        class="fi-admin-footer-modal-backdrop"
    >
        <div
            class="fi-admin-footer-modal"
            x-on:click.outside="openModal = null"
        >
            <template x-if="openModal === 'terms'">
                <div class="fi-admin-footer-modal-content">
                    <div>
                        <h2 class="fi-admin-footer-modal-title">Terms of Service</h2>
                        <p class="fi-admin-footer-modal-subtitle">Template syarat dan ketentuan penggunaan platform EPIC HUB.</p>
                    </div>

                    <div class="fi-admin-footer-modal-body">
                        <p>Dengan menggunakan EPIC HUB, pengguna setuju untuk memakai platform ini secara sah, wajar, dan tidak melanggar hukum maupun hak pihak lain.</p>
                        <p>Akses terhadap produk digital, kelas, event, dan fitur lain diberikan sesuai jenis pembelian, entitlement, atau persetujuan admin yang berlaku pada akun pengguna.</p>
                        <p>Pengguna dilarang mendistribusikan ulang materi, membagikan akses akun, mencoba mengganggu sistem, atau menggunakan platform untuk aktivitas yang merugikan EPIC HUB maupun pengguna lain.</p>
                        <p>EPIC HUB berhak memperbarui fitur, kebijakan, harga, maupun ketentuan layanan dari waktu ke waktu untuk menjaga kualitas layanan dan keamanan sistem.</p>
                        <p>Apabila ditemukan penyalahgunaan, EPIC HUB dapat membatasi akses, menangguhkan akun, atau mengambil tindakan administratif lain sesuai kebijakan internal.</p>
                    </div>

                    <div class="fi-admin-footer-modal-actions">
                        <button
                            type="button"
                            class="fi-admin-footer-modal-close"
                            x-on:click="openModal = null"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="openModal === 'privacy'">
                <div class="fi-admin-footer-modal-content">
                    <div>
                        <h2 class="fi-admin-footer-modal-title">Privacy Policy</h2>
                        <p class="fi-admin-footer-modal-subtitle">Template kebijakan privasi untuk penggunaan platform EPIC HUB.</p>
                    </div>

                    <div class="fi-admin-footer-modal-body">
                        <p>EPIC HUB dapat mengumpulkan data dasar pengguna seperti nama, email, aktivitas pembelian, progress belajar, dan data teknis yang diperlukan untuk menjalankan layanan.</p>
                        <p>Data digunakan untuk autentikasi, pemberian akses produk, pengalaman belajar yang lebih baik, dukungan pengguna, analitik operasional, dan kebutuhan administratif platform.</p>
                        <p>Data pengguna tidak dibagikan secara sembarangan kepada pihak lain di luar kebutuhan layanan, kepatuhan hukum, atau integrasi sistem yang memang diperlukan untuk operasional.</p>
                        <p>EPIC HUB berupaya menjaga keamanan data dengan kontrol akses, validasi sistem, dan praktik pengelolaan data yang wajar sesuai kebutuhan aplikasi.</p>
                        <p>Pengguna dapat menghubungi admin atau pengelola platform untuk permintaan pembaruan data, pertanyaan privasi, atau klarifikasi terkait kebijakan ini.</p>
                    </div>

                    <div class="fi-admin-footer-modal-actions">
                        <button
                            type="button"
                            class="fi-admin-footer-modal-close"
                            x-on:click="openModal = null"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
