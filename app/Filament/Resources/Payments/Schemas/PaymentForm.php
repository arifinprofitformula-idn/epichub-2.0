<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /* ── Ringkasan Pembayaran ── */
                Section::make('Ringkasan Pembayaran')
                    ->description('Informasi utama dan status pembayaran')
                    ->icon('heroicon-o-credit-card')
                    ->iconColor('primary')
                    ->extraAttributes(['class' => 'fi-pay-section-summary'])
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('payment_number')
                                ->label('Nomor Pembayaran')
                                ->disabled()
                                ->dehydrated(false)
                                ->extraInputAttributes(['class' => 'font-mono font-bold']),

                            Placeholder::make('order_link')
                                ->label('Nomor Order')
                                ->content(fn ($record) => $record?->order?->order_number
                                    ? new HtmlString(
                                        '<a href="/admin/orders/'.$record->order->order_number.'/edit" '.
                                        'class="fi-pay-order-link">'.
                                        $record->order->order_number.
                                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="fi-pay-order-link-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>'.
                                        '</a>'
                                    )
                                    : new HtmlString('<span class="fi-pay-meta-empty">-</span>')),

                            Placeholder::make('status_display')
                                ->label('Status')
                                ->content(function ($record) {
                                    if (! $record?->status) {
                                        return new HtmlString('<span class="fi-pay-meta-empty">-</span>');
                                    }
                                    $status = $record->status instanceof PaymentStatus
                                        ? $record->status
                                        : PaymentStatus::tryFrom((string) $record->status);
                                    $label = $status?->label() ?? (string) $record->status;
                                    $color = match ($status) {
                                        PaymentStatus::Success => 'fi-pay-badge-success',
                                        PaymentStatus::Pending  => 'fi-pay-badge-warning',
                                        PaymentStatus::Failed, PaymentStatus::Expired => 'fi-pay-badge-danger',
                                        PaymentStatus::Refunded => 'fi-pay-badge-info',
                                        default                 => 'fi-pay-badge-gray',
                                    };

                                    return new HtmlString("<span class=\"fi-pay-badge {$color}\">{$label}</span>");
                                }),

                            Placeholder::make('amount_display')
                                ->label('Jumlah Pembayaran')
                                ->content(fn ($record) => $record?->amount !== null
                                    ? new HtmlString('<span class="fi-pay-amount">Rp '.number_format((float) $record->amount, 0, ',', '.').'</span>')
                                    : '-'),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Detail Pembayaran ── */
                Section::make('Detail Pembayaran')
                    ->description('Metode, waktu, dan referensi penyedia pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('info')
                    ->extraAttributes(['class' => 'fi-pay-section-detail'])
                    ->schema([
                        Grid::make(3)->schema([
                            Placeholder::make('method_display')
                                ->label('Metode Pembayaran')
                                ->content(function ($record) {
                                    $method = $record?->payment_method;
                                    $label = $method instanceof PaymentMethod ? $method->label() : ((string) ($method ?? '-'));

                                    return new HtmlString("<span class=\"fi-pay-meta-value\">{$label}</span>");
                                }),

                            Placeholder::make('paid_at_display')
                                ->label('Waktu Pembayaran')
                                ->content(fn ($record) => $record?->paid_at
                                    ? new HtmlString('<span class="fi-pay-meta-value fi-pay-paid">'.$record->paid_at->translatedFormat('d F Y, H:i').' WIB</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">Belum dibayar</span>')),

                            Placeholder::make('expired_at_display')
                                ->label('Kedaluwarsa')
                                ->content(fn ($record) => $record?->expired_at
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->expired_at->translatedFormat('d F Y, H:i').' WIB</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">-</span>')),

                            TextInput::make('provider')
                                ->label('Provider')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('provider_reference')
                                ->label('Referensi Provider')
                                ->disabled()
                                ->dehydrated(false),

                            Placeholder::make('created_at_display')
                                ->label('Tanggal Dibuat')
                                ->content(fn ($record) => $record?->created_at
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->created_at->translatedFormat('d F Y, H:i').' WIB</span>')
                                    : '-'),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Info Customer ── */
                Section::make('Info Customer')
                    ->description('Data pelanggan yang melakukan pembayaran ini')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('warning')
                    ->extraAttributes(['class' => 'fi-pay-section-customer'])
                    ->schema([
                        Grid::make(3)->schema([
                            Placeholder::make('customer_name')
                                ->label('Nama Lengkap')
                                ->content(fn ($record) => $record?->order?->user?->name
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->order->user->name.'</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">-</span>')),

                            Placeholder::make('customer_email')
                                ->label('Email')
                                ->content(fn ($record) => $record?->order?->user?->email
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->order->user->email.'</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">-</span>')),

                            Placeholder::make('customer_phone')
                                ->label('No. Telepon')
                                ->content(fn ($record) => $record?->order?->customer_phone
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->order->customer_phone.'</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">-</span>')),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Verifikasi ── */
                Section::make('Verifikasi')
                    ->description('Informasi verifikasi oleh admin')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->extraAttributes(['class' => 'fi-pay-section-verify'])
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('verified_by_display')
                                ->label('Diverifikasi oleh')
                                ->content(fn ($record) => $record?->verifiedBy?->name
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->verifiedBy->name.'</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">Belum diverifikasi</span>')),

                            Placeholder::make('verified_at_display')
                                ->label('Waktu Verifikasi')
                                ->content(fn ($record) => $record?->verified_at
                                    ? new HtmlString('<span class="fi-pay-meta-value">'.$record->verified_at->translatedFormat('d F Y, H:i').' WIB</span>')
                                    : new HtmlString('<span class="fi-pay-meta-empty">-</span>')),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Bukti Pembayaran ── */
                Section::make('Bukti Pembayaran')
                    ->description('Foto atau file bukti transfer yang diunggah pelanggan')
                    ->icon('heroicon-o-photo')
                    ->iconColor('gray')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('proof_display')
                            ->label('')
                            ->content(function ($record) {
                                $path = $record?->proof_of_payment;
                                if (! $path) {
                                    return new HtmlString('<p class="fi-pay-no-proof">Tidak ada bukti pembayaran yang diunggah.</p>');
                                }
                                $url = str_starts_with($path, 'http') ? $path : '/storage/'.$path;

                                return new HtmlString(
                                    '<div class="fi-pay-proof-wrap">'.
                                    '<a href="'.$url.'" target="_blank" class="fi-pay-proof-link">'.
                                    '<img src="'.$url.'" alt="Bukti Pembayaran" class="fi-pay-proof-img" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'block\'">'.
                                    '<span class="fi-pay-proof-fallback" style="display:none">'.
                                    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:2rem;height:2rem;color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>'.
                                    '<span class="fi-pay-proof-filename">'.basename($path).'</span>'.
                                    '</span>'.
                                    '</a>'.
                                    '<a href="'.$url.'" target="_blank" class="fi-pay-proof-open">Buka file ↗</a>'.
                                    '</div>'
                                );
                            }),
                    ])
                    ->columnSpanFull(),

                /* ── Keterangan Gagal ── */
                Section::make('Keterangan Kegagalan')
                    ->description('Alasan pembayaran gagal atau ditolak')
                    ->icon('heroicon-o-x-circle')
                    ->iconColor('danger')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('failure_reason_display')
                            ->label('')
                            ->content(fn ($record) => $record?->failure_reason
                                ? new HtmlString('<p class="fi-pay-failure">'.$record->failure_reason.'</p>')
                                : new HtmlString('<p class="fi-pay-no-proof">Tidak ada keterangan kegagalan.</p>')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
