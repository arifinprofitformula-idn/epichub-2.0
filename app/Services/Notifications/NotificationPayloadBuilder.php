<?php

namespace App\Services\Notifications;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserProduct;
use Carbon\Carbon;

class NotificationPayloadBuilder
{
    private const TIMEZONE = 'Asia/Jakarta';

    // ── Member / Auth ────────────────────────────────────────────────────────

    public function forUserRegistered(User $user): array
    {
        return $this->withAliases([
            'member_name'    => $user->name,
            'member_email'   => $user->email,
            'member_whatsapp'=> $user->whatsapp_number ?? '',
            'dashboard_url'  => url('/dashboard'),
            'login_url'      => url('/login'),
            'sent_at'        => $this->now(),
        ]);
    }

    public function forPasswordReset(User $user, ?string $resetUrl = null): array
    {
        $payload = [
            'member_name'  => $user->name,
            'member_email' => $user->email,
            'login_url'    => url('/login'),
            'sent_at'      => $this->now(),
        ];

        if ($resetUrl !== null) {
            $payload['reset_url'] = $resetUrl;
        }

        return $this->withAliases($payload);
    }

    public function forPaymentReminder(Payment $payment, int $attemptNumber = 1): array
    {
        $payment->loadMissing(['order.items.product', 'order.user']);

        $order = $payment->order;
        $user  = $order?->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'member_email'   => $user?->email ?? '',
            'member_whatsapp'=> $user?->whatsapp_number ?? '',
            'order_number'   => $order?->order_number ?? '',
            'products_list'  => $this->productsList($order),
            'total_amount'   => $this->formatCurrency((float) $order?->total_amount),
            'payment_url'    => $this->orderPaymentUrl($order),
            'attempt_number' => $attemptNumber,
        ], 'payment_reminder');
    }

    public function forEventReminder(EventRegistration $registration, string $eventType): array
    {
        $registration->loadMissing(['event', 'user']);

        $event = $registration->event;
        $user  = $registration->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'member_email'   => $user?->email ?? '',
            'member_whatsapp'=> $user?->whatsapp_number ?? '',
            'event_name'     => $event?->title ?? '',
            'event_datetime' => $this->formatEventDatetime($event),
            'event_location' => $this->eventLocation($event),
            'event_url'      => $registration->id ? url('/my-events/'.$registration->id) : url('/my-events'),
        ], $eventType);
    }

    // ── Order ────────────────────────────────────────────────────────────────

    public function forOrderCreated(Order $order): array
    {
        $order->loadMissing(['user', 'items.product']);

        $user     = $order->user;
        $products = $this->productsList($order);

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'member_email'   => $user?->email ?? '',
            'member_whatsapp'=> $user?->whatsapp_number ?? '',
            'order_number'   => $order->order_number,
            'products_list'  => $products,
            'product_name'   => $this->firstProductName($order),
            'total_amount'   => $this->formatCurrency((float) $order->total_amount),
            'payment_url'    => $this->orderPaymentUrl($order),
            'dashboard_url'  => url('/dashboard'),
        ]);
    }

    public function forOrderExpired(Order $order): array
    {
        $order->loadMissing(['user', 'items.product']);

        $user = $order->user;

        return $this->withAliases([
            'member_name'   => $user?->name ?? '',
            'order_number'  => $order->order_number,
            'products_list' => $this->productsList($order),
            'total_amount'  => $this->formatCurrency((float) $order->total_amount),
            'payment_url'   => $this->orderPaymentUrl($order),
        ]);
    }

    public function forAdminOrder(Order $order): array
    {
        $order->loadMissing(['user', 'items.product']);

        $user = $order->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'member_email'   => $user?->email ?? '',
            'member_whatsapp'=> $user?->whatsapp_number ?? '',
            'order_number'   => $order->order_number,
            'products_list'  => $this->productsList($order),
            'total_amount'   => $this->formatCurrency((float) $order->total_amount),
            'admin_order_url'=> url('/admin/orders/'.$order->id.'/edit'),
        ]);
    }

    // ── Payment ──────────────────────────────────────────────────────────────

    public function forPaymentSubmitted(Payment $payment): array
    {
        $payment->loadMissing(['order.user', 'order.items.product']);

        $order = $payment->order;
        $user  = $order?->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'payment_number' => $payment->payment_number,
            'order_number'   => $order?->order_number ?? '',
            'payment_amount' => $this->formatCurrency((float) $payment->amount),
            'total_amount'   => $this->formatCurrency((float) $order?->total_amount),
            'sent_at'        => $this->now(),
        ], 'payment_submitted');
    }

    public function forPaymentApproved(Payment $payment): array
    {
        $payment->loadMissing(['order.user', 'order.items.product']);

        $order = $payment->order;
        $user  = $order?->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'order_number'   => $order?->order_number ?? '',
            'payment_number' => $payment->payment_number,
            'products_list'  => $this->productsList($order),
            'produk_saya_url'=> url('/my-products'),
            'total_amount'   => $this->formatCurrency((float) $order?->total_amount),
        ]);
    }

    public function forPaymentRejected(Payment $payment, ?string $reason = null): array
    {
        $payment->loadMissing(['order.user']);

        $order = $payment->order;
        $user  = $order?->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'payment_number' => $payment->payment_number,
            'order_number'   => $order?->order_number ?? '',
            'reason'         => $reason ?? $payment->failure_reason ?? '-',
            'payment_url'    => $this->orderPaymentUrl($order),
        ]);
    }

    public function forAdminPayment(Payment $payment): array
    {
        $payment->loadMissing(['order.user']);

        $order = $payment->order;
        $user  = $order?->user;

        return $this->withAliases([
            'member_name'     => $user?->name ?? '',
            'member_email'    => $user?->email ?? '',
            'member_whatsapp' => $user?->whatsapp_number ?? '',
            'payment_number'  => $payment->payment_number,
            'order_number'    => $order?->order_number ?? '',
            'payment_amount'  => $this->formatCurrency((float) $payment->amount),
            'admin_payment_url' => url('/admin/payments/'.$payment->id.'/edit'),
        ], 'admin_payment_submitted');
    }

    // ── Access ───────────────────────────────────────────────────────────────

    public function forAccessGranted(UserProduct $userProduct): array
    {
        $userProduct->loadMissing(['user', 'product']);

        $user    = $userProduct->user;
        $product = $userProduct->product;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'product_name'   => $product?->title ?? '',
            'produk_saya_url'=> url('/my-products'),
        ]);
    }

    // ── Event Registration ───────────────────────────────────────────────────

    public function forEventRegistration(EventRegistration $registration): array
    {
        $registration->loadMissing(['event', 'user']);

        $event = $registration->event;
        $user  = $registration->user;

        return $this->withAliases([
            'member_name'    => $user?->name ?? '',
            'member_email'   => $user?->email ?? '',
            'member_whatsapp'=> $user?->whatsapp_number ?? '',
            'event_name'     => $event?->title ?? '',
            'event_datetime' => $this->formatEventDatetime($event),
            'event_schedule' => $this->formatEventSchedule($event),
            'event_location' => $this->eventLocation($event),
            'event_url'      => $registration->id ? url('/my-events/'.$registration->id) : url('/my-events'),
        ]);
    }

    public function forAdminEventRegistration(EventRegistration $registration): array
    {
        $registration->loadMissing(['event', 'user']);

        $event = $registration->event;
        $user  = $registration->user;

        return $this->withAliases([
            'member_name'                  => $user?->name ?? '',
            'member_email'                 => $user?->email ?? '',
            'member_whatsapp'              => $user?->whatsapp_number ?? '',
            'event_name'                   => $event?->title ?? '',
            'event_datetime'               => $this->formatEventDatetime($event),
            'admin_event_registration_url' => url('/admin/event-registrations/'.$registration->id.'/edit'),
        ]);
    }

    // ── Course ───────────────────────────────────────────────────────────────

    public function forCourseEnrollment(UserProduct $userProduct): array
    {
        $userProduct->loadMissing(['user', 'product.course']);

        $user    = $userProduct->user;
        $product = $userProduct->product;
        $course  = $product?->course;

        return $this->withAliases([
            'member_name'        => $user?->name ?? '',
            'member_email'       => $user?->email ?? '',
            'course_name'        => $course?->title ?? $product?->title ?? '',
            'course_description' => $course?->short_description ?? '',
            'course_url'         => $userProduct->id ? url('/my-courses/'.$userProduct->id) : url('/my-courses'),
        ]);
    }

    // ── Commission / Payout ──────────────────────────────────────────────────

    public function forCommission(Commission $commission): array
    {
        $commission->loadMissing(['epiChannel.user', 'product']);

        $channelUser = $commission->epiChannel?->user;
        $product     = $commission->product;
        $status      = $commission->status?->label() ?? (string) $commission->status;

        return $this->withAliases([
            'member_name'      => $channelUser?->name ?? '',
            'member_email'     => $channelUser?->email ?? '',
            'product_name'     => $product?->title ?? '',
            'commission_amount'=> $this->formatCurrency((float) $commission->commission_amount),
            'commission_status'=> $status,
            'dashboard_url'    => url('/epi-channel/commissions'),
            'epic_code'        => $commission->epiChannel?->epic_code ?? '',
        ], 'affiliate_commission_created');
    }

    public function forPayout(CommissionPayout $payout): array
    {
        $payout->loadMissing(['epiChannel.user']);

        $channelUser = $payout->epiChannel?->user;

        return $this->withAliases([
            'member_name'  => $channelUser?->name ?? '',
            'member_email' => $channelUser?->email ?? '',
            'payout_amount'=> $this->formatCurrency((float) $payout->total_amount),
            'paid_at'      => $this->formatCarbon($payout->paid_at),
            'dashboard_url'=> url('/epi-channel/payouts'),
        ], 'commission_payout_paid');
    }

    public function forAdminPayout(CommissionPayout $payout): array
    {
        $payout->loadMissing(['epiChannel.user']);

        $channelUser = $payout->epiChannel?->user;

        return $this->withAliases([
            'member_name'   => $channelUser?->name ?? '',
            'member_email'  => $channelUser?->email ?? '',
            'payout_amount' => $this->formatCurrency((float) $payout->total_amount),
            'paid_at'       => $this->formatCarbon($payout->paid_at),
            'admin_payout_url' => url('/admin/commission-payouts'),
        ], 'admin_payout_paid');
    }

    // ── Dummy payload untuk testing ──────────────────────────────────────────

    public function dummyForEvent(string $eventKey): array
    {
        return [
            'member_name'                  => 'Budi Santoso',
            'member_email'                 => 'budi@example.com',
            'member_whatsapp'              => '628123456789',
            'epic_code'                    => 'EPI-ABCD',
            'dashboard_url'                => url('/dashboard'),
            'login_url'                    => url('/login'),
            'product_name'                 => 'Kelas Digital Marketing',
            'products_list'                => "Kelas Digital Marketing\nE-Book SEO",
            'order_number'                 => 'ORD-20240501-001',
            'payment_number'               => 'PAY-20240501-001',
            'total_amount'                 => 'Rp 500.000',
            'payment_amount'               => 'Rp 500.000',
            'payment_url'                  => url('/orders/ORD-20240501-001/pay'),
            'produk_saya_url'              => url('/my-products'),
            'event_name'                   => 'Workshop Digital Marketing 2024',
            'event_datetime'               => '01 Mei 2024, 09:00 WIB',
            'event_schedule'               => '01 Mei 2024, 09:00 - 12:00 WIB',
            'event_location'               => 'Online / Zoom',
            'event_url'                    => url('/my-events/1'),
            'course_name'                  => 'Kelas SEO Dasar',
            'course_description'           => 'Belajar SEO dari nol hingga mahir.',
            'course_url'                   => url('/my-courses/1'),
            'commission_amount'            => 'Rp 100.000',
            'commission_status'            => 'Pending',
            'payout_amount'                => 'Rp 500.000',
            'paid_at'                      => '01 Mei 2024, 10:30 WIB',
            'admin_order_url'              => url('/admin/orders/1/edit'),
            'admin_payment_url'            => url('/admin/payments/1/edit'),
            'admin_event_registration_url' => url('/admin/event-registrations/1/edit'),
            'admin_payout_url'             => url('/admin/commission-payouts'),
            'reason'                       => 'Bukti pembayaran tidak jelas',
            'sent_at'                      => $this->now(),
            'tanggal'                      => now()->timezone(self::TIMEZONE)->translatedFormat('d M Y'),
            'waktu'                        => now()->timezone(self::TIMEZONE)->format('H:i').' WIB',
            // alias lama
            'name'   => 'Budi Santoso',
            'amount' => 'Rp 500.000',
            'status' => 'Pending',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function withAliases(array $payload, ?string $eventKey = null): array
    {
        // Canonical => alias untuk backward compat
        if (! isset($payload['name'])) {
            $payload['name'] = $payload['member_name'] ?? '';
        }

        // amount context-aware
        if (! isset($payload['amount'])) {
            $payload['amount'] = match ($eventKey) {
                'payment_submitted', 'admin_payment_submitted' => $payload['payment_amount'] ?? $payload['total_amount'] ?? '',
                'commission_payout_paid', 'admin_payout_paid', 'admin_commission_payout_paid' => $payload['payout_amount'] ?? '',
                default => $payload['total_amount'] ?? '',
            };
        }

        // status context-aware
        if (! isset($payload['status'])) {
            $payload['status'] = $payload['commission_status'] ?? '';
        }

        return $payload;
    }

    private function productsList(?Order $order): string
    {
        if (! $order) {
            return '';
        }

        return $order->items
            ->map(fn ($item) => $item->product?->title ?? $item->product_name ?? '')
            ->filter()
            ->implode("\n");
    }

    private function firstProductName(?Order $order): string
    {
        if (! $order) {
            return '';
        }

        return $order->items->first()?->product?->title
            ?? $order->items->first()?->product_name
            ?? '';
    }

    private function orderPaymentUrl(?Order $order): string
    {
        if (! $order) {
            return '';
        }

        $payment = $order->latestPayment();

        if ($payment) {
            return route('payments.show', $payment);
        }

        return route('orders.show', $order);
    }

    private function formatEventDatetime($event): string
    {
        if (! $event || ! $event->starts_at) {
            return '-';
        }

        $tz = $event->timezone ?: self::TIMEZONE;

        return $event->starts_at->timezone($tz)->translatedFormat('d M Y, H:i').' '.$tz;
    }

    private function formatEventSchedule($event): string
    {
        if (! $event || ! $event->starts_at) {
            return 'Jadwal akan diinformasikan segera';
        }

        $tz       = $event->timezone ?: self::TIMEZONE;
        $startsAt = $event->starts_at->timezone($tz);
        $schedule = $startsAt->translatedFormat('d M Y, H:i');

        if ($event->ends_at) {
            $schedule .= ' - '.$event->ends_at->timezone($tz)->translatedFormat('H:i');
        }

        return trim($schedule.' '.$tz);
    }

    private function eventLocation($event): string
    {
        if (! $event) {
            return '-';
        }

        foreach (['location', 'location_label', 'venue', 'venue_name'] as $key) {
            $value = trim((string) data_get($event->metadata, $key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return filled($event->zoom_url) ? 'Online' : 'Akan diinformasikan';
    }

    private function formatCarbon(mixed $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof \Carbon\CarbonInterface ? $date : Carbon::parse($date);

        return $carbon->timezone(self::TIMEZONE)->translatedFormat('d M Y, H:i').' WIB';
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function now(): string
    {
        return now()->timezone(self::TIMEZONE)->translatedFormat('d M Y, H:i').' WIB';
    }
}
