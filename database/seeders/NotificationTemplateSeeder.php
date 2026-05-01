<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $row) {
            NotificationTemplate::updateOrCreate(
                [
                    'event_key'  => $row['event_key'],
                    'target_key' => $row['target_key'],
                ],
                $row,
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function templates(): array
    {
        return [

            // ── user_registered ──────────────────────────────────────────────

            [
                'event_key'             => 'user_registered',
                'event_label'           => 'Pendaftaran',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Selamat Datang di EPIC HUB, {member_name}!',
                'email_body'            => "Assalamu'alaikum {member_name},\n\nSelamat datang di EPIC HUB.\nAkun Anda berhasil dibuat dengan email {member_email}.\n\nSilakan masuk ke dashboard Anda:\n{dashboard_url}\n\nJika ada pertanyaan, silakan hubungi tim kami.\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Assalamu'alaikum {member_name}, selamat datang di EPIC HUB.\nAkun Anda berhasil dibuat.\nSilakan masuk ke dashboard:\n{dashboard_url}",
                'default_email_subject' => 'Selamat Datang di EPIC HUB, {member_name}!',
                'default_email_body'    => "Assalamu'alaikum {member_name},\n\nSelamat datang di EPIC HUB.\nAkun Anda berhasil dibuat dengan email {member_email}.\n\nSilakan masuk ke dashboard Anda:\n{dashboard_url}\n\nJika ada pertanyaan, silakan hubungi tim kami.\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Assalamu'alaikum {member_name}, selamat datang di EPIC HUB.\nAkun Anda berhasil dibuat.\nSilakan masuk ke dashboard:\n{dashboard_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── password_reset_requested ─────────────────────────────────────

            [
                'event_key'             => 'password_reset_requested',
                'event_label'           => 'Reset Password',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Permintaan Reset Password EPIC HUB',
                'email_body'            => "Assalamu'alaikum {member_name},\n\nKami menerima permintaan reset password untuk akun EPIC HUB Anda.\n\nSilakan klik link di email ini untuk mengatur ulang password Anda.\n\nJika bukan Anda yang meminta, abaikan email ini. Password Anda tidak akan berubah.\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Assalamu'alaikum {member_name}.\nKami menerima permintaan reset password EPIC HUB.\nSilakan cek email Anda untuk melanjutkan reset password.\nJika bukan Anda yang meminta, abaikan pesan ini.",
                'default_email_subject' => 'Permintaan Reset Password EPIC HUB',
                'default_email_body'    => "Assalamu'alaikum {member_name},\n\nKami menerima permintaan reset password untuk akun EPIC HUB Anda.\n\nSilakan klik link di email ini untuk mengatur ulang password Anda.\n\nJika bukan Anda yang meminta, abaikan email ini. Password Anda tidak akan berubah.\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Assalamu'alaikum {member_name}.\nKami menerima permintaan reset password EPIC HUB.\nSilakan cek email Anda untuk melanjutkan reset password.\nJika bukan Anda yang meminta, abaikan pesan ini.",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── order_created ────────────────────────────────────────────────

            [
                'event_key'             => 'order_created',
                'event_label'           => 'Order Baru',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Order Anda Berhasil Dibuat - {order_number}',
                'email_body'            => "Assalamu'alaikum {member_name},\n\nOrder Anda berhasil dibuat.\n\nNomor Order: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nSilakan lanjutkan pembayaran melalui link berikut:\n{payment_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Assalamu'alaikum {member_name}, order Anda berhasil dibuat.\n\nOrder: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nSilakan lanjutkan pembayaran:\n{payment_url}",
                'default_email_subject' => 'Order Anda Berhasil Dibuat - {order_number}',
                'default_email_body'    => "Assalamu'alaikum {member_name},\n\nOrder Anda berhasil dibuat.\n\nNomor Order: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nSilakan lanjutkan pembayaran melalui link berikut:\n{payment_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Assalamu'alaikum {member_name}, order Anda berhasil dibuat.\n\nOrder: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nSilakan lanjutkan pembayaran:\n{payment_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],
            [
                'event_key'             => 'order_created',
                'event_label'           => 'Order Baru',
                'target_key'            => 'admin',
                'target_label'          => 'Admin Platform',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Order Baru Masuk - {order_number}',
                'email_body'            => "Order baru telah masuk.\n\nNomor Order: {order_number}\nMember: {member_name}\nEmail: {member_email}\nWhatsApp: {member_whatsapp}\nTotal: {total_amount}\nProduk: {products_list}\n\nLihat detail order di admin:\n{admin_order_url}",
                'whatsapp_body'         => "Order baru masuk.\nOrder: {order_number}\nMember: {member_name}\nTotal: {total_amount}",
                'default_email_subject' => 'Order Baru Masuk - {order_number}',
                'default_email_body'    => "Order baru telah masuk.\n\nNomor Order: {order_number}\nMember: {member_name}\nEmail: {member_email}\nWhatsApp: {member_whatsapp}\nTotal: {total_amount}\nProduk: {products_list}\n\nLihat detail order di admin:\n{admin_order_url}",
                'default_whatsapp_body' => "Order baru masuk.\nOrder: {order_number}\nMember: {member_name}\nTotal: {total_amount}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── payment_submitted ────────────────────────────────────────────

            [
                'event_key'             => 'payment_submitted',
                'event_label'           => 'Bukti Pembayaran Dikirim',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Bukti Pembayaran Anda Sedang Diverifikasi',
                'email_body'            => "Terima kasih {member_name},\n\nBukti pembayaran Anda untuk {payment_number} sudah kami terima dan sedang diverifikasi oleh admin.\n\nNomor Pembayaran: {payment_number}\nNomor Order: {order_number}\nNominal: {payment_amount}\n\nKami akan mengabari Anda setelah pembayaran disetujui.\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Terima kasih {member_name}.\nBukti pembayaran Anda untuk {payment_number} sudah kami terima dan sedang diverifikasi admin.\nKami akan mengabari Anda setelah pembayaran disetujui.",
                'default_email_subject' => 'Bukti Pembayaran Anda Sedang Diverifikasi',
                'default_email_body'    => "Terima kasih {member_name},\n\nBukti pembayaran Anda untuk {payment_number} sudah kami terima dan sedang diverifikasi oleh admin.\n\nNomor Pembayaran: {payment_number}\nNomor Order: {order_number}\nNominal: {payment_amount}\n\nKami akan mengabari Anda setelah pembayaran disetujui.\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Terima kasih {member_name}.\nBukti pembayaran Anda untuk {payment_number} sudah kami terima dan sedang diverifikasi admin.\nKami akan mengabari Anda setelah pembayaran disetujui.",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],
            [
                'event_key'             => 'payment_submitted',
                'event_label'           => 'Bukti Pembayaran Dikirim',
                'target_key'            => 'admin',
                'target_label'          => 'Admin Platform',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Bukti Pembayaran Baru Perlu Diverifikasi',
                'email_body'            => "Bukti pembayaran baru perlu dicek.\n\nNomor Pembayaran: {payment_number}\nNomor Order: {order_number}\nMember: {member_name}\nEmail: {member_email}\nWhatsApp: {member_whatsapp}\nNominal: {payment_amount}\n\nVerifikasi di admin:\n{admin_payment_url}",
                'whatsapp_body'         => "Bukti pembayaran baru perlu dicek.\nPayment: {payment_number}\nMember: {member_name}\nNominal: {payment_amount}",
                'default_email_subject' => 'Bukti Pembayaran Baru Perlu Diverifikasi',
                'default_email_body'    => "Bukti pembayaran baru perlu dicek.\n\nNomor Pembayaran: {payment_number}\nNomor Order: {order_number}\nMember: {member_name}\nEmail: {member_email}\nWhatsApp: {member_whatsapp}\nNominal: {payment_amount}\n\nVerifikasi di admin:\n{admin_payment_url}",
                'default_whatsapp_body' => "Bukti pembayaran baru perlu dicek.\nPayment: {payment_number}\nMember: {member_name}\nNominal: {payment_amount}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── payment_approved ─────────────────────────────────────────────

            [
                'event_key'             => 'payment_approved',
                'event_label'           => 'Order Lunas',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Pembayaran Berhasil, Akses Produk Aktif',
                'email_body'            => "Alhamdulillah {member_name},\n\nPembayaran Anda berhasil disetujui.\n\nNomor Order: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nAkses produk Anda sudah aktif. Silakan buka Produk Saya:\n{produk_saya_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Alhamdulillah {member_name}, pembayaran Anda sudah disetujui.\n\nProduk Anda sudah aktif dan bisa diakses melalui:\n{produk_saya_url}",
                'default_email_subject' => 'Pembayaran Berhasil, Akses Produk Aktif',
                'default_email_body'    => "Alhamdulillah {member_name},\n\nPembayaran Anda berhasil disetujui.\n\nNomor Order: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nAkses produk Anda sudah aktif. Silakan buka Produk Saya:\n{produk_saya_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Alhamdulillah {member_name}, pembayaran Anda sudah disetujui.\n\nProduk Anda sudah aktif dan bisa diakses melalui:\n{produk_saya_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── payment_rejected ─────────────────────────────────────────────

            [
                'event_key'             => 'payment_rejected',
                'event_label'           => 'Pembayaran Ditolak',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Bukti Pembayaran Perlu Diperiksa Ulang',
                'email_body'            => "Mohon maaf {member_name},\n\nBukti pembayaran Anda perlu diperiksa ulang.\n\nNomor Pembayaran: {payment_number}\nNomor Order: {order_number}\nAlasan: {reason}\n\nSilakan unggah ulang bukti pembayaran melalui:\n{payment_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Mohon maaf {member_name}, bukti pembayaran Anda perlu diperiksa ulang.\nAlasan: {reason}\nSilakan unggah ulang bukti pembayaran melalui:\n{payment_url}",
                'default_email_subject' => 'Bukti Pembayaran Perlu Diperiksa Ulang',
                'default_email_body'    => "Mohon maaf {member_name},\n\nBukti pembayaran Anda perlu diperiksa ulang.\n\nNomor Pembayaran: {payment_number}\nNomor Order: {order_number}\nAlasan: {reason}\n\nSilakan unggah ulang bukti pembayaran melalui:\n{payment_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Mohon maaf {member_name}, bukti pembayaran Anda perlu diperiksa ulang.\nAlasan: {reason}\nSilakan unggah ulang bukti pembayaran melalui:\n{payment_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── order_expired ────────────────────────────────────────────────

            [
                'event_key'             => 'order_expired',
                'event_label'           => 'Order Expired',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Order {order_number} Sudah Kedaluwarsa',
                'email_body'            => "Halo {member_name},\n\nSayang sekali, order Anda sudah kedaluwarsa karena belum ada pembayaran.\n\nNomor Order: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nJika masih berminat, silakan buat order baru:\n{payment_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Halo {member_name}, order {order_number} Anda sudah kedaluwarsa.\n\nProduk: {products_list}\nTotal: {total_amount}\n\nJika masih berminat, silakan buat order baru.",
                'default_email_subject' => 'Order {order_number} Sudah Kedaluwarsa',
                'default_email_body'    => "Halo {member_name},\n\nSayang sekali, order Anda sudah kedaluwarsa karena belum ada pembayaran.\n\nNomor Order: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nJika masih berminat, silakan buat order baru:\n{payment_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Halo {member_name}, order {order_number} Anda sudah kedaluwarsa.\n\nProduk: {products_list}\nTotal: {total_amount}\n\nJika masih berminat, silakan buat order baru.",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── access_granted ───────────────────────────────────────────────

            [
                'event_key'             => 'access_granted',
                'event_label'           => 'Akses Produk Aktif',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Akses Produk Anda Sudah Aktif',
                'email_body'            => "Halo {member_name},\n\nAkses produk Anda sudah aktif.\n\nProduk: {product_name}\n\nSilakan akses melalui Produk Saya:\n{produk_saya_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Akses produk Anda sudah aktif.\nProduk: {product_name}\nSilakan akses melalui Produk Saya:\n{produk_saya_url}",
                'default_email_subject' => 'Akses Produk Anda Sudah Aktif',
                'default_email_body'    => "Halo {member_name},\n\nAkses produk Anda sudah aktif.\n\nProduk: {product_name}\n\nSilakan akses melalui Produk Saya:\n{produk_saya_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Akses produk Anda sudah aktif.\nProduk: {product_name}\nSilakan akses melalui Produk Saya:\n{produk_saya_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── event_registration_confirmed ─────────────────────────────────

            [
                'event_key'             => 'event_registration_confirmed',
                'event_label'           => 'Registrasi Event',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Registrasi Event Berhasil - {event_name}',
                'email_body'            => "Halo {member_name},\n\nRegistrasi event Anda berhasil.\n\nEvent: {event_name}\nJadwal: {event_schedule}\nLokasi: {event_location}\n\nCek detail event di dashboard:\n{event_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Registrasi event Anda berhasil.\nEvent: {event_name}\nWaktu: {event_datetime}\nSilakan cek detail event di dashboard:\n{event_url}",
                'default_email_subject' => 'Registrasi Event Berhasil - {event_name}',
                'default_email_body'    => "Halo {member_name},\n\nRegistrasi event Anda berhasil.\n\nEvent: {event_name}\nJadwal: {event_schedule}\nLokasi: {event_location}\n\nCek detail event di dashboard:\n{event_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Registrasi event Anda berhasil.\nEvent: {event_name}\nWaktu: {event_datetime}\nSilakan cek detail event di dashboard:\n{event_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],
            [
                'event_key'             => 'event_registration_confirmed',
                'event_label'           => 'Registrasi Event',
                'target_key'            => 'admin',
                'target_label'          => 'Admin Platform',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Pendaftaran Event Baru - {event_name}',
                'email_body'            => "Pendaftaran event baru.\n\nEvent: {event_name}\nWaktu: {event_datetime}\nPeserta: {member_name}\nEmail: {member_email}\nWhatsApp: {member_whatsapp}\n\nLihat detail di admin:\n{admin_event_registration_url}",
                'whatsapp_body'         => "Pendaftaran event baru.\nEvent: {event_name}\nPeserta: {member_name}",
                'default_email_subject' => 'Pendaftaran Event Baru - {event_name}',
                'default_email_body'    => "Pendaftaran event baru.\n\nEvent: {event_name}\nWaktu: {event_datetime}\nPeserta: {member_name}\nEmail: {member_email}\nWhatsApp: {member_whatsapp}\n\nLihat detail di admin:\n{admin_event_registration_url}",
                'default_whatsapp_body' => "Pendaftaran event baru.\nEvent: {event_name}\nPeserta: {member_name}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── course_enrolled ──────────────────────────────────────────────

            [
                'event_key'             => 'course_enrolled',
                'event_label'           => 'Course Enrollment',
                'target_key'            => 'member',
                'target_label'          => 'Member',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Anda Terdaftar di Kelas - {course_name}',
                'email_body'            => "Halo {member_name},\n\nAnda sudah terdaftar di kelas:\n{course_name}\n\n{course_description}\n\nMulai belajar melalui:\n{course_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Anda sudah terdaftar di kelas:\n{course_name}\nSilakan mulai belajar melalui:\n{course_url}",
                'default_email_subject' => 'Anda Terdaftar di Kelas - {course_name}',
                'default_email_body'    => "Halo {member_name},\n\nAnda sudah terdaftar di kelas:\n{course_name}\n\n{course_description}\n\nMulai belajar melalui:\n{course_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Anda sudah terdaftar di kelas:\n{course_name}\nSilakan mulai belajar melalui:\n{course_url}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── affiliate_commission_created ─────────────────────────────────

            [
                'event_key'             => 'affiliate_commission_created',
                'event_label'           => 'Komisi Affiliate',
                'target_key'            => 'sponsor',
                'target_label'          => 'Sponsor / Affiliate',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Komisi Affiliate Baru Masuk',
                'email_body'            => "Halo {member_name},\n\nKomisi affiliate baru masuk.\n\nProduk: {product_name}\nNominal: {commission_amount}\nStatus: {commission_status}\n\nLihat detail di dashboard komisi Anda:\n{dashboard_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Komisi affiliate baru masuk.\nProduk: {product_name}\nNominal: {commission_amount}\nStatus: {commission_status}\nLihat detail di dashboard komisi Anda.",
                'default_email_subject' => 'Komisi Affiliate Baru Masuk',
                'default_email_body'    => "Halo {member_name},\n\nKomisi affiliate baru masuk.\n\nProduk: {product_name}\nNominal: {commission_amount}\nStatus: {commission_status}\n\nLihat detail di dashboard komisi Anda:\n{dashboard_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Komisi affiliate baru masuk.\nProduk: {product_name}\nNominal: {commission_amount}\nStatus: {commission_status}\nLihat detail di dashboard komisi Anda.",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],

            // ── commission_payout_paid ───────────────────────────────────────

            [
                'event_key'             => 'commission_payout_paid',
                'event_label'           => 'Payout Diproses',
                'target_key'            => 'sponsor',
                'target_label'          => 'Sponsor / Affiliate',
                'email_enabled'         => true,
                'whatsapp_enabled'      => true,
                'email_subject'         => 'Payout Komisi Telah Diproses',
                'email_body'            => "Halo {member_name},\n\nPayout komisi Anda telah diproses.\n\nNominal: {payout_amount}\nTanggal: {paid_at}\n\nSilakan cek riwayat payout di dashboard:\n{dashboard_url}\n\nSalam,\nTim EPIC HUB",
                'whatsapp_body'         => "Payout komisi Anda telah diproses.\nNominal: {payout_amount}\nTanggal: {paid_at}\nSilakan cek riwayat payout di dashboard.",
                'default_email_subject' => 'Payout Komisi Telah Diproses',
                'default_email_body'    => "Halo {member_name},\n\nPayout komisi Anda telah diproses.\n\nNominal: {payout_amount}\nTanggal: {paid_at}\n\nSilakan cek riwayat payout di dashboard:\n{dashboard_url}\n\nSalam,\nTim EPIC HUB",
                'default_whatsapp_body' => "Payout komisi Anda telah diproses.\nNominal: {payout_amount}\nTanggal: {paid_at}\nSilakan cek riwayat payout di dashboard.",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => true],
            ],
            [
                'event_key'             => 'commission_payout_paid',
                'event_label'           => 'Payout Diproses',
                'target_key'            => 'admin',
                'target_label'          => 'Admin Platform',
                'email_enabled'         => true,
                'whatsapp_enabled'      => false,
                'email_subject'         => 'Payout Komisi Diproses - {member_name}',
                'email_body'            => "Payout komisi diproses.\n\nMember: {member_name}\nEmail: {member_email}\nNominal: {payout_amount}\nTanggal: {paid_at}\n\nLihat detail di admin:\n{admin_payout_url}",
                'whatsapp_body'         => "Payout komisi diproses.\nMember: {member_name}\nNominal: {payout_amount}\nTanggal: {paid_at}",
                'default_email_subject' => 'Payout Komisi Diproses - {member_name}',
                'default_email_body'    => "Payout komisi diproses.\n\nMember: {member_name}\nEmail: {member_email}\nNominal: {payout_amount}\nTanggal: {paid_at}\n\nLihat detail di admin:\n{admin_payout_url}",
                'default_whatsapp_body' => "Payout komisi diproses.\nMember: {member_name}\nNominal: {payout_amount}\nTanggal: {paid_at}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => false],
            ],
        ];
    }
}
