<?php

namespace App\Services\Notifications;

class WhatsAppMessageTemplateService
{
    /** @param array<string, mixed> $data */
    public function render(string $eventType, array $data = []): string
    {
        return match ($eventType) {
            'user_registered' => trim($this->interpolate(
                "Assalamu'alaikum {name}, selamat datang di EPIC HUB.\nAkun Anda berhasil dibuat.\nSilakan masuk ke dashboard untuk mengakses Marketplace dan Produk Saya:\n{dashboard_url}",
                $data,
            )),
            'password_reset_requested' => trim($this->interpolate(
                "Assalamu'alaikum {name}.\nKami menerima permintaan reset password EPIC HUB.\nSilakan cek email Anda untuk melanjutkan reset password.\nJika bukan Anda yang meminta, abaikan pesan ini.",
                $data,
            )),
            'order_created' => trim($this->interpolate(
                "Assalamu'alaikum {name}, order Anda berhasil dibuat.\nNomor Order: {order_number}\nTotal: {total_amount}\nSilakan lakukan pembayaran dan unggah bukti pembayaran melalui:\n{payment_url}",
                $data,
            )),
            'payment_submitted' => trim($this->interpolate(
                "Terima kasih {name}.\nBukti pembayaran Anda untuk {payment_number} sudah kami terima dan sedang diverifikasi admin.\nKami akan mengabari Anda setelah pembayaran disetujui.",
                $data,
            )),
            'payment_approved' => trim($this->interpolate(
                "Alhamdulillah, pembayaran Anda berhasil disetujui.\nAkses produk Anda sudah aktif.\nSilakan buka Produk Saya:\n{produk_saya_url}",
                $data,
            )),
            'payment_rejected' => trim($this->interpolate(
                "Mohon maaf {name}, bukti pembayaran Anda perlu diperiksa ulang.\nAlasan: {reason}\nSilakan unggah ulang bukti pembayaran melalui:\n{payment_url}",
                $data,
            )),
            'access_granted' => trim($this->interpolate(
                "Akses produk Anda sudah aktif.\nProduk: {product_name}\nSilakan akses melalui Produk Saya:\n{produk_saya_url}",
                $data,
            )),
            'event_registration_confirmed' => trim($this->interpolate(
                "Registrasi event Anda berhasil.\nEvent: {event_name}\nWaktu: {event_datetime}\nSilakan cek detail event di dashboard:\n{event_url}",
                $data,
            )),
            'course_enrolled' => trim($this->interpolate(
                "Anda sudah terdaftar di kelas:\n{course_name}\nSilakan mulai belajar melalui:\n{course_url}",
                $data,
            )),
            'affiliate_commission_created' => trim($this->interpolate(
                "Komisi affiliate baru masuk.\nProduk: {product_name}\nNominal: {commission_amount}\nStatus: {status}\nLihat detail di dashboard komisi Anda.",
                $data,
            )),
            'commission_payout_paid' => trim($this->interpolate(
                "Payout komisi Anda telah diproses.\nNominal: {amount}\nTanggal: {paid_at}\nSilakan cek riwayat payout di dashboard.",
                $data,
            )),
            'admin_order_created' => trim($this->interpolate(
                "Order baru masuk.\nOrder: {order_number}\nMember: {member_name}\nTotal: {total_amount}",
                $data,
            )),
            'admin_payment_submitted' => trim($this->interpolate(
                "Bukti pembayaran baru perlu dicek.\nPayment: {payment_number}\nMember: {member_name}\nTotal: {amount}",
                $data,
            )),
            'admin_event_registration' => trim($this->interpolate(
                "Pendaftaran event baru.\nEvent: {event_name}\nPeserta: {member_name}",
                $data,
            )),
            'admin_payout_paid' => trim($this->interpolate(
                "Payout komisi diproses.\nMember: {member_name}\nNominal: {amount}\nTanggal: {paid_at}",
                $data,
            )),
            'payment_reminder' => trim($this->interpolate(
                "Assalamu'alaikum {name}, ini pengingat pembayaran untuk order {order_number} sebesar {total_amount}.\nSilakan selesaikan pembayaran melalui:\n{payment_url}",
                $data,
            )),
            'event_reminder_day_before' => trim($this->interpolate(
                "Reminder event EPIC HUB.\nEvent: {event_name}\nWaktu: {event_datetime}\nAcara dimulai besok. Detail:\n{event_url}",
                $data,
            )),
            'event_reminder_hour_before' => trim($this->interpolate(
                "Reminder event EPIC HUB.\nEvent: {event_name}\nWaktu: {event_datetime}\nAcara dimulai 1 jam lagi. Detail:\n{event_url}",
                $data,
            )),
            'test_whatsapp', 'test_whatsapp_cli' => trim($this->interpolate(
                "Ini adalah test WhatsApp dari EPIC HUB.\nWaktu: {sent_at}\nJika pesan ini masuk, integrasi DripSender berjalan dengan baik.",
                $data,
            )),
            default => trim((string) ($data['message'] ?? '')),
        };
    }

    /** @param array<string, mixed> $data */
    private function interpolate(string $template, array $data): string
    {
        return preg_replace_callback('/\{([a-z0-9_]+)\}/i', function (array $matches) use ($data): string {
            $value = data_get($data, $matches[1]);

            return is_scalar($value) ? (string) $value : '';
        }, $template) ?? $template;
    }
}
