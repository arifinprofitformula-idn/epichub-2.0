<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /* ── Ringkasan Order ── */
                Section::make('Ringkasan Order')
                    ->description('Informasi utama dan status pesanan')
                    ->icon('heroicon-o-shopping-cart')
                    ->iconColor('primary')
                    ->extraAttributes(['class' => 'fi-order-section-summary'])
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('order_number')
                                ->label('Nomor Order')
                                ->disabled()
                                ->dehydrated(false)
                                ->extraInputAttributes(['class' => 'font-mono font-bold text-navy']),

                            Select::make('status')
                                ->label('Status')
                                ->options(
                                    collect(OrderStatus::cases())
                                        ->mapWithKeys(fn (OrderStatus $s) => [$s->value => $s->label()])
                                        ->all()
                                )
                                ->required()
                                ->native(false),

                            TextInput::make('total_amount')
                                ->label('Total Pembayaran')
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(false),
                        ]),

                        Grid::make(3)->schema([
                            Placeholder::make('created_at_display')
                                ->label('Tanggal Order')
                                ->content(fn ($record) => $record?->created_at
                                    ? new HtmlString('<span class="fi-order-meta-value">'.$record->created_at->translatedFormat('d F Y, H:i').' WIB</span>')
                                    : '-'),

                            Placeholder::make('paid_at_display')
                                ->label('Tanggal Lunas')
                                ->content(fn ($record) => $record?->paid_at
                                    ? new HtmlString('<span class="fi-order-meta-value fi-order-paid">'.$record->paid_at->translatedFormat('d F Y, H:i').' WIB</span>')
                                    : new HtmlString('<span class="fi-order-meta-empty">Belum dibayar</span>')),

                            Placeholder::make('subtotal_display')
                                ->label('Subtotal')
                                ->content(fn ($record) => $record?->subtotal_amount !== null
                                    ? new HtmlString('<span class="fi-order-meta-value">Rp '.number_format((float) $record->subtotal_amount, 0, ',', '.').'</span>')
                                    : '-'),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Data Pembeli ── */
                Section::make('Data Pembeli')
                    ->description('Informasi kontak dan identitas pelanggan')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('info')
                    ->extraAttributes(['class' => 'fi-order-section-buyer'])
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('customer_name')
                                ->label('Nama Lengkap')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('customer_email')
                                ->label('Email')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('customer_phone')
                                ->label('No. Telepon')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Referral ── */
                Section::make('Informasi Referral')
                    ->description('Data saluran referral yang mereferensikan order ini')
                    ->icon('heroicon-o-link')
                    ->iconColor('warning')
                    ->extraAttributes(['class' => 'fi-order-section-referral'])
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('referrerEpiChannel.epic_code')
                                ->label('EPIC Referrer Code')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Tidak ada referrer'),

                            TextInput::make('referral_source')
                                ->label('Referral Source')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('-'),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Item Pesanan ── */
                Section::make('Item Pesanan')
                    ->description('Daftar produk yang dipesan')
                    ->icon('heroicon-o-rectangle-stack')
                    ->iconColor('success')
                    ->extraAttributes(['class' => 'fi-order-section-items'])
                    ->schema([
                        Placeholder::make('order_items_table')
                            ->label('')
                            ->content(function ($record) {
                                if (! $record || $record->items->isEmpty()) {
                                    return new HtmlString('<p class="fi-order-no-items">Tidak ada item dalam pesanan ini.</p>');
                                }

                                $rows = $record->items->map(function ($item) {
                                    $price = 'Rp '.number_format((float) $item->unit_price, 0, ',', '.');
                                    $subtotal = 'Rp '.number_format((float) $item->subtotal_amount, 0, ',', '.');
                                    $type = $item->product_type ?? '-';

                                    return <<<HTML
                                        <tr class="fi-order-item-row">
                                            <td class="fi-order-item-cell fi-order-item-title">
                                                <span class="fi-order-item-name">{$item->product_title}</span>
                                                <span class="fi-order-item-type">{$type}</span>
                                            </td>
                                            <td class="fi-order-item-cell fi-order-item-qty">{$item->quantity}</td>
                                            <td class="fi-order-item-cell fi-order-item-price">{$price}</td>
                                            <td class="fi-order-item-cell fi-order-item-subtotal">{$subtotal}</td>
                                        </tr>
                                    HTML;
                                })->implode('');

                                return new HtmlString(<<<HTML
                                    <div class="fi-order-items-wrap">
                                        <table class="fi-order-items-table">
                                            <thead>
                                                <tr class="fi-order-items-thead-row">
                                                    <th class="fi-order-item-th">Produk</th>
                                                    <th class="fi-order-item-th fi-order-item-th-center">Qty</th>
                                                    <th class="fi-order-item-th fi-order-item-th-right">Harga Satuan</th>
                                                    <th class="fi-order-item-th fi-order-item-th-right">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>{$rows}</tbody>
                                        </table>
                                    </div>
                                HTML);
                            }),
                    ])
                    ->columnSpanFull(),

                /* ── Catatan ── */
                Section::make('Catatan')
                    ->description('Catatan tambahan pada pesanan')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->iconColor('gray')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('notes_display')
                            ->label('')
                            ->content(fn ($record) => $record?->notes
                                ? new HtmlString('<p class="fi-order-notes">'.$record->notes.'</p>')
                                : new HtmlString('<p class="fi-order-meta-empty">Tidak ada catatan.</p>')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
