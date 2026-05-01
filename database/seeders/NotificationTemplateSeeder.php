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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Assalamu'alaikum {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Selamat datang di EPIC HUB! Akun Anda telah berhasil dibuat dengan email <strong>{member_email}</strong>. Kami siap membantu Anda belajar lebih mudah dan terstruktur.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 8px; font-weight:700; font-size:15px;">Siap pakai dashboard</p>
    <p style="margin:0; font-size:15px;">Akses semua kelas, event, dan produk digital yang Anda beli melalui dashboard EPIC HUB.</p>
</div>
<p style="margin:0 0 24px;"><a href="{dashboard_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Buka Dashboard</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Assalamu'alaikum {member_name}, selamat datang di EPIC HUB.\nAkun Anda berhasil dibuat.\nSilakan masuk ke dashboard:\n{dashboard_url}",
                'default_email_subject' => 'Selamat Datang di EPIC HUB, {member_name}!',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Assalamu'alaikum {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Selamat datang di EPIC HUB! Akun Anda telah berhasil dibuat dengan email <strong>{member_email}</strong>. Kami siap membantu Anda belajar lebih mudah dan terstruktur.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 8px; font-weight:700; font-size:15px;">Siap pakai dashboard</p>
    <p style="margin:0; font-size:15px;">Akses semua kelas, event, dan produk digital yang Anda beli melalui dashboard EPIC HUB.</p>
</div>
<p style="margin:0 0 24px;"><a href="{dashboard_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Buka Dashboard</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Assalamu'alaikum {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Kami menerima permintaan reset password untuk akun EPIC HUB Anda. Untuk menjaga keamanan, silakan gunakan tautan di bawah ini sebelum kadaluarsa.</p>
<p style="margin:0 0 24px;"><a href="{reset_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Atur Ulang Password</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.</p>
<p style="margin:24px 0 0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Assalamu'alaikum {member_name}.\nKami menerima permintaan reset password EPIC HUB.\nSilakan cek email Anda untuk melanjutkan reset password.\nJika bukan Anda yang meminta, abaikan pesan ini.",
                'default_email_subject' => 'Permintaan Reset Password EPIC HUB',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Assalamu'alaikum {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Kami menerima permintaan reset password untuk akun EPIC HUB Anda. Untuk menjaga keamanan, silakan gunakan tautan di bawah ini sebelum kadaluarsa.</p>
<p style="margin:0 0 24px;"><a href="{reset_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Atur Ulang Password</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.</p>
<p style="margin:24px 0 0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Assalamu'alaikum {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Order Anda sudah berhasil dibuat. Terima kasih telah memilih EPIC HUB. Berikut ringkasan order Anda:</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Order</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Produk: {products_list}</p>
    <p style="margin:0;">Total: {total_amount}</p>
</div>
<p style="margin:0 0 24px;">Silakan lanjutkan pembayaran melalui tautan berikut untuk memastikan akses produk Anda segera aktif.</p>
<p style="margin:0 0 24px;"><a href="{payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Bayar Sekarang</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Assalamu'alaikum {member_name}, order Anda berhasil dibuat.\n\nOrder: {order_number}\nProduk: {products_list}\nTotal: {total_amount}\n\nSilakan lanjutkan pembayaran:\n{payment_url}",
                'default_email_subject' => 'Order Anda Berhasil Dibuat - {order_number}',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Assalamu'alaikum {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Order Anda sudah berhasil dibuat. Terima kasih telah memilih EPIC HUB. Berikut ringkasan order Anda:</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Order</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Produk: {products_list}</p>
    <p style="margin:0;">Total: {total_amount}</p>
</div>
<p style="margin:0 0 24px;">Silakan lanjutkan pembayaran melalui tautan berikut untuk memastikan akses produk Anda segera aktif.</p>
<p style="margin:0 0 24px;"><a href="{payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Bayar Sekarang</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Order baru masuk.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Order</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Member: {member_name}</p>
    <p style="margin:0 0 4px;">Email: {member_email}</p>
    <p style="margin:0;">WhatsApp: {member_whatsapp}</p>
</div>
<p style="margin:0 0 24px;">Total pembelian: <strong>{total_amount}</strong></p>
<p style="margin:0 0 24px;"><a href="{admin_order_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Order</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Order baru masuk.\nOrder: {order_number}\nMember: {member_name}\nTotal: {total_amount}",
                'default_email_subject' => 'Order Baru Masuk - {order_number}',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Order baru masuk.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Order</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Member: {member_name}</p>
    <p style="margin:0 0 4px;">Email: {member_email}</p>
    <p style="margin:0;">WhatsApp: {member_whatsapp}</p>
</div>
<p style="margin:0 0 24px;">Total pembelian: <strong>{total_amount}</strong></p>
<p style="margin:0 0 24px;"><a href="{admin_order_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Order</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Terima kasih {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Kami telah menerima bukti pembayaran Anda. Tim kami sedang memeriksa data dan akan segera memberi kabar apabila verifikasi selesai.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Ringkasan Pembayaran</p>
    <p style="margin:0 0 4px;">Nomor Pembayaran: <strong>{payment_number}</strong></p>
    <p style="margin:0 0 4px;">Nomor Order: {order_number}</p>
    <p style="margin:0;">Nominal: {payment_amount}</p>
</div>
<p style="margin:0; font-size:15px; color:#475569;">Kami akan menghubungi Anda setelah pembayaran disetujui.</p>
<p style="margin:24px 0 0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Terima kasih {member_name}.\nBukti pembayaran Anda untuk {payment_number} sudah kami terima dan sedang diverifikasi admin.\nKami akan mengabari Anda setelah pembayaran disetujui.",
                'default_email_subject' => 'Bukti Pembayaran Anda Sedang Diverifikasi',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Terima kasih {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Kami telah menerima bukti pembayaran Anda. Tim kami sedang memeriksa data dan akan segera memberi kabar apabila verifikasi selesai.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Ringkasan Pembayaran</p>
    <p style="margin:0 0 4px;">Nomor Pembayaran: <strong>{payment_number}</strong></p>
    <p style="margin:0 0 4px;">Nomor Order: {order_number}</p>
    <p style="margin:0;">Nominal: {payment_amount}</p>
</div>
<p style="margin:0; font-size:15px; color:#475569;">Kami akan menghubungi Anda setelah pembayaran disetujui.</p>
<p style="margin:24px 0 0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Bukti pembayaran baru masuk.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Pembayaran</p>
    <p style="margin:0 0 4px;">Nomor Pembayaran: <strong>{payment_number}</strong></p>
    <p style="margin:0 0 4px;">Nomor Order: {order_number}</p>
    <p style="margin:0 0 4px;">Member: {member_name}</p>
    <p style="margin:0 0 4px;">Email: {member_email}</p>
    <p style="margin:0;">WhatsApp: {member_whatsapp}</p>
</div>
<p style="margin:0 0 24px;">Nominal: <strong>{payment_amount}</strong></p>
<p style="margin:0 0 24px;"><a href="{admin_payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Verifikasi Sekarang</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Bukti pembayaran baru perlu dicek.\nPayment: {payment_number}\nMember: {member_name}\nNominal: {payment_amount}",
                'default_email_subject' => 'Bukti Pembayaran Baru Perlu Diverifikasi',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Bukti pembayaran baru masuk.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Pembayaran</p>
    <p style="margin:0 0 4px;">Nomor Pembayaran: <strong>{payment_number}</strong></p>
    <p style="margin:0 0 4px;">Nomor Order: {order_number}</p>
    <p style="margin:0 0 4px;">Member: {member_name}</p>
    <p style="margin:0 0 4px;">Email: {member_email}</p>
    <p style="margin:0;">WhatsApp: {member_whatsapp}</p>
</div>
<p style="margin:0 0 24px;">Nominal: <strong>{payment_amount}</strong></p>
<p style="margin:0 0 24px;"><a href="{admin_payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Verifikasi Sekarang</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Alhamdulillah {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Pembayaran Anda sudah disetujui dan akses produk Anda kini aktif. Selamat belajar!</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Ringkasan Pesanan</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Produk: {products_list}</p>
    <p style="margin:0;">Total: {total_amount}</p>
</div>
<p style="margin:0 0 24px;">Akses produk sudah aktif. Silakan buka Produk Saya untuk mulai belajar sekarang.</p>
<p style="margin:0 0 24px;"><a href="{produk_saya_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Buka Produk Saya</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Alhamdulillah {member_name}, pembayaran Anda sudah disetujui.\n\nProduk Anda sudah aktif dan bisa diakses melalui:\n{produk_saya_url}",
                'default_email_subject' => 'Pembayaran Berhasil, Akses Produk Aktif',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Alhamdulillah {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Pembayaran Anda sudah disetujui dan akses produk Anda kini aktif. Selamat belajar!</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Ringkasan Pesanan</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Produk: {products_list}</p>
    <p style="margin:0;">Total: {total_amount}</p>
</div>
<p style="margin:0 0 24px;">Akses produk sudah aktif. Silakan buka Produk Saya untuk mulai belajar sekarang.</p>
<p style="margin:0 0 24px;"><a href="{produk_saya_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Buka Produk Saya</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Mohon maaf {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Bukti pembayaran Anda perlu diperiksa ulang agar data sesuai dan akses produk dapat diproses dengan lancar.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Verifikasi</p>
    <p style="margin:0 0 4px;">Nomor Pembayaran: <strong>{payment_number}</strong></p>
    <p style="margin:0 0 4px;">Nomor Order: {order_number}</p>
    <p style="margin:0;">Alasan: {reason}</p>
</div>
<p style="margin:0 0 24px;">Silakan unggah ulang bukti pembayaran melalui tautan berikut agar proses verifikasi dapat dilanjutkan.</p>
<p style="margin:0 0 24px;"><a href="{payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Unggah Ulang</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Mohon maaf {member_name}, bukti pembayaran Anda perlu diperiksa ulang.\nAlasan: {reason}\nSilakan unggah ulang bukti pembayaran melalui:\n{payment_url}",
                'default_email_subject' => 'Bukti Pembayaran Perlu Diperiksa Ulang',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Mohon maaf {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Bukti pembayaran Anda perlu diperiksa ulang agar data sesuai dan akses produk dapat diproses dengan lancar.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Verifikasi</p>
    <p style="margin:0 0 4px;">Nomor Pembayaran: <strong>{payment_number}</strong></p>
    <p style="margin:0 0 4px;">Nomor Order: {order_number}</p>
    <p style="margin:0;">Alasan: {reason}</p>
</div>
<p style="margin:0 0 24px;">Silakan unggah ulang bukti pembayaran melalui tautan berikut agar proses verifikasi dapat dilanjutkan.</p>
<p style="margin:0 0 24px;"><a href="{payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Unggah Ulang</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Order Anda telah kedaluwarsa karena pembayaran belum diterima. Jangan khawatir, Anda bisa membuat order baru kapan saja.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Ringkasan Order</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Produk: {products_list}</p>
    <p style="margin:0;">Total: {total_amount}</p>
</div>
<p style="margin:0 0 24px;">Jika Anda masih tertarik, silakan buat order baru melalui tautan berikut.</p>
<p style="margin:0 0 24px;"><a href="{payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Buat Order Baru</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Halo {member_name}, order {order_number} Anda sudah kedaluwarsa.\n\nProduk: {products_list}\nTotal: {total_amount}\n\nJika masih berminat, silakan buat order baru.",
                'default_email_subject' => 'Order {order_number} Sudah Kedaluwarsa',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Order Anda telah kedaluwarsa karena pembayaran belum diterima. Jangan khawatir, Anda bisa membuat order baru kapan saja.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Ringkasan Order</p>
    <p style="margin:0 0 4px;">Nomor Order: <strong>{order_number}</strong></p>
    <p style="margin:0 0 4px;">Produk: {products_list}</p>
    <p style="margin:0;">Total: {total_amount}</p>
</div>
<p style="margin:0 0 24px;">Jika Anda masih tertarik, silakan buat order baru melalui tautan berikut.</p>
<p style="margin:0 0 24px;"><a href="{payment_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Buat Order Baru</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Akses produk Anda sudah aktif. Berikut detail produk yang bisa langsung Anda gunakan.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Produk Aktif</p>
    <p style="margin:0 0 4px;">Produk: <strong>{product_name}</strong></p>
</div>
<p style="margin:0 0 24px;">Silakan akses melalui halaman Produk Saya untuk mulai menikmati konten belajar Anda.</p>
<p style="margin:0 0 24px;"><a href="{produk_saya_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Produk Saya</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Akses produk Anda sudah aktif.\nProduk: {product_name}\nSilakan akses melalui Produk Saya:\n{produk_saya_url}",
                'default_email_subject' => 'Akses Produk Anda Sudah Aktif',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Akses produk Anda sudah aktif. Berikut detail produk yang bisa langsung Anda gunakan.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Produk Aktif</p>
    <p style="margin:0 0 4px;">Produk: <strong>{product_name}</strong></p>
</div>
<p style="margin:0 0 24px;">Silakan akses melalui halaman Produk Saya untuk mulai menikmati konten belajar Anda.</p>
<p style="margin:0 0 24px;"><a href="{produk_saya_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Produk Saya</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Selamat! Registrasi event Anda telah berhasil. Berikut detail penting yang perlu Anda simpan.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Event</p>
    <p style="margin:0 0 4px;">Event: <strong>{event_name}</strong></p>
    <p style="margin:0 0 4px;">Jadwal: {event_schedule}</p>
    <p style="margin:0;">Lokasi: {event_location}</p>
</div>
<p style="margin:0 0 24px;">Cek informasi lengkap event pada tautan berikut agar Anda tidak melewatkan sesi penting.</p>
<p style="margin:0 0 24px;"><a href="{event_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Detail Event</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Registrasi event Anda berhasil.\nEvent: {event_name}\nWaktu: {event_datetime}\nSilakan cek detail event di dashboard:\n{event_url}",
                'default_email_subject' => 'Registrasi Event Berhasil - {event_name}',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Selamat! Registrasi event Anda telah berhasil. Berikut detail penting yang perlu Anda simpan.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Event</p>
    <p style="margin:0 0 4px;">Event: <strong>{event_name}</strong></p>
    <p style="margin:0 0 4px;">Jadwal: {event_schedule}</p>
    <p style="margin:0;">Lokasi: {event_location}</p>
</div>
<p style="margin:0 0 24px;">Cek informasi lengkap event pada tautan berikut agar Anda tidak melewatkan sesi penting.</p>
<p style="margin:0 0 24px;"><a href="{event_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Detail Event</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Pendaftaran event baru masuk.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Pendaftaran</p>
    <p style="margin:0 0 4px;">Event: <strong>{event_name}</strong></p>
    <p style="margin:0 0 4px;">Waktu: {event_datetime}</p>
    <p style="margin:0 0 4px;">Peserta: {member_name}</p>
    <p style="margin:0;">Email: {member_email}</p>
</div>
<p style="margin:0 0 24px;">Silakan cek detail pendaftaran di admin untuk menyiapkan kebutuhan event.</p>
<p style="margin:0 0 24px;"><a href="{admin_event_registration_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Pendaftaran</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Pendaftaran event baru.\nEvent: {event_name}\nPeserta: {member_name}",
                'default_email_subject' => 'Pendaftaran Event Baru - {event_name}',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Pendaftaran event baru masuk.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Pendaftaran</p>
    <p style="margin:0 0 4px;">Event: <strong>{event_name}</strong></p>
    <p style="margin:0 0 4px;">Waktu: {event_datetime}</p>
    <p style="margin:0 0 4px;">Peserta: {member_name}</p>
    <p style="margin:0;">Email: {member_email}</p>
</div>
<p style="margin:0 0 24px;">Silakan cek detail pendaftaran di admin untuk menyiapkan kebutuhan event.</p>
<p style="margin:0 0 24px;"><a href="{admin_event_registration_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Pendaftaran</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Selamat! Anda berhasil terdaftar di kelas berikut.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Kelas</p>
    <p style="margin:0 0 4px;">Kelas: <strong>{course_name}</strong></p>
    <p style="margin:0;">{course_description}</p>
</div>
<p style="margin:0 0 24px;">Mulai belajar dengan membuka kelas berikut ini.</p>
<p style="margin:0 0 24px;"><a href="{course_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Mulai Kelas</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Anda sudah terdaftar di kelas:\n{course_name}\nSilakan mulai belajar melalui:\n{course_url}",
                'default_email_subject' => 'Anda Terdaftar di Kelas - {course_name}',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Selamat! Anda berhasil terdaftar di kelas berikut.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Kelas</p>
    <p style="margin:0 0 4px;">Kelas: <strong>{course_name}</strong></p>
    <p style="margin:0;">{course_description}</p>
</div>
<p style="margin:0 0 24px;">Mulai belajar dengan membuka kelas berikut ini.</p>
<p style="margin:0 0 24px;"><a href="{course_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Mulai Kelas</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Komisi affiliate baru telah masuk ke akun Anda. Berikut detailnya.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Komisi</p>
    <p style="margin:0 0 4px;">Produk: <strong>{product_name}</strong></p>
    <p style="margin:0 0 4px;">Nominal: {commission_amount}</p>
    <p style="margin:0;">Status: {commission_status}</p>
</div>
<p style="margin:0 0 24px;">Lihat detail komisi Anda di dashboard EPIC HUB untuk informasi lebih lanjut.</p>
<p style="margin:0 0 24px;"><a href="{dashboard_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Komisi</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Komisi affiliate baru masuk.\nProduk: {product_name}\nNominal: {commission_amount}\nStatus: {commission_status}\nLihat detail di dashboard komisi Anda.",
                'default_email_subject' => 'Komisi Affiliate Baru Masuk',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Komisi affiliate baru telah masuk ke akun Anda. Berikut detailnya.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Komisi</p>
    <p style="margin:0 0 4px;">Produk: <strong>{product_name}</strong></p>
    <p style="margin:0 0 4px;">Nominal: {commission_amount}</p>
    <p style="margin:0;">Status: {commission_status}</p>
</div>
<p style="margin:0 0 24px;">Lihat detail komisi Anda di dashboard EPIC HUB untuk informasi lebih lanjut.</p>
<p style="margin:0 0 24px;"><a href="{dashboard_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Komisi</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Payout komisi Anda telah diproses. Dana akan segera masuk ke rekening Anda sesuai jadwal.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Payout</p>
    <p style="margin:0 0 4px;">Nominal: <strong>{payout_amount}</strong></p>
    <p style="margin:0 0 4px;">Tanggal: {paid_at}</p>
</div>
<p style="margin:0 0 24px;">Lihat riwayat payout di dashboard untuk informasi lengkap.</p>
<p style="margin:0 0 24px;"><a href="{dashboard_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Dashboard</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Payout komisi Anda telah diproses.\nNominal: {payout_amount}\nTanggal: {paid_at}\nSilakan cek riwayat payout di dashboard.",
                'default_email_subject' => 'Payout Komisi Telah Diproses',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Halo {member_name},</p>
<p style="margin:0 0 18px; font-size:16px; color:#475569;">Payout komisi Anda telah diproses. Dana akan segera masuk ke rekening Anda sesuai jadwal.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Payout</p>
    <p style="margin:0 0 4px;">Nominal: <strong>{payout_amount}</strong></p>
    <p style="margin:0 0 4px;">Tanggal: {paid_at}</p>
</div>
<p style="margin:0 0 24px;">Lihat riwayat payout di dashboard untuk informasi lengkap.</p>
<p style="margin:0 0 24px;"><a href="{dashboard_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Dashboard</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Salam,<br>Tim EPIC HUB</p>
HTML
),
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
                'email_body'            => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Payout komisi telah diproses.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Payout</p>
    <p style="margin:0 0 4px;">Member: <strong>{member_name}</strong></p>
    <p style="margin:0 0 4px;">Email: {member_email}</p>
    <p style="margin:0 0 4px;">Nominal: {payout_amount}</p>
    <p style="margin:0;">Tanggal: {paid_at}</p>
</div>
<p style="margin:0 0 24px;">Lihat detail di admin untuk memastikan proses payout telah tercatat.</p>
<p style="margin:0 0 24px;"><a href="{admin_payout_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Detail</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
                'whatsapp_body'         => "Payout komisi diproses.\nMember: {member_name}\nNominal: {payout_amount}\nTanggal: {paid_at}",
                'default_email_subject' => 'Payout Komisi Diproses - {member_name}',
                'default_email_body'    => $this->emailBody(<<<'HTML'
<p style="margin:0 0 18px; font-size:16px; color:#0f172a;">Payout komisi telah diproses.</p>
<div style="margin:0 0 24px; padding:18px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155;">
    <p style="margin:0 0 10px; font-weight:700;">Detail Payout</p>
    <p style="margin:0 0 4px;">Member: <strong>{member_name}</strong></p>
    <p style="margin:0 0 4px;">Email: {member_email}</p>
    <p style="margin:0 0 4px;">Nominal: {payout_amount}</p>
    <p style="margin:0;">Tanggal: {paid_at}</p>
</div>
<p style="margin:0 0 24px;">Lihat detail di admin untuk memastikan proses payout telah tercatat.</p>
<p style="margin:0 0 24px;"><a href="{admin_payout_url}" style="display:inline-block; padding:12px 22px; border-radius:999px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:600;">Lihat Detail</a></p>
<p style="margin:0; font-size:15px; color:#475569;">Tim EPIC HUB</p>
HTML
),
                'default_whatsapp_body' => "Payout komisi diproses.\nMember: {member_name}\nNominal: {payout_amount}\nTanggal: {paid_at}",
                'metadata'              => ['default_email_enabled' => true, 'default_whatsapp_enabled' => false],
            ],
        ];
    }

    private function emailBody(string $content): string
    {
        return <<<HTML
<div style="font-family:Inter, 'Segoe UI', Roboto, sans-serif; color:#111827; line-height:1.75; font-size:16px;">
{$content}
</div>
HTML;
    }
}
