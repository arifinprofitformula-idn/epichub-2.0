<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label('Payment No.')
                    ->description(fn ($record) => $record->created_at?->translatedFormat('d M Y, H:i') ?? '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->url(fn ($record) => $record->order?->order_number
                        ? '/admin/orders/'.$record->order->order_number.'/edit'
                        : null)
                    ->openUrlInNewTab(false)
                    ->extraAttributes(['class' => 'fi-payment-order-link'])
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.user.name')
                    ->label('Info Customer')
                    ->description(fn ($record) => $record->order?->user?->email ?? '-')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->formatStateUsing(fn (PaymentMethod|string|null $state): string => $state instanceof PaymentMethod ? $state->label() : (string) $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus|string|null $state): string => $state instanceof PaymentStatus ? $state->label() : (string) $state)
                    ->color(fn (PaymentStatus|string|null $state): string => $state instanceof PaymentStatus ? $state->getColor() : (PaymentStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (string|int|float|null $state): string => 'Rp '.number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->description(fn (Payment $record): HtmlString|string => filled($record->proof_of_payment)
                        ? new HtmlString('<span class="fi-proof-hint">Lihat bukti transfer →</span>')
                        : '')
                    ->action(
                        Action::make('view_proof')
                            ->modalHeading(fn (Payment $record) => 'Bukti Transfer — '.$record->payment_number)
                            ->modalWidth('2xl')
                            ->modalContent(function (Payment $record): HtmlString {
                                $path = $record->proof_of_payment;

                                if (! $path) {
                                    return new HtmlString('<p class="fi-proof-modal-none">Tidak ada bukti pembayaran.</p>');
                                }

                                $url = str_starts_with($path, 'http') ? $path : '/storage/'.$path;
                                $name = $record->order?->customer_name ?? $record->order?->user?->name ?? '-';
                                $email = $record->order?->user?->email ?? '-';
                                $amount = 'Rp '.number_format((float) $record->amount, 0, ',', '.');
                                $date = $record->created_at?->translatedFormat('d F Y, H:i') ?? '-';

                                return new HtmlString(<<<HTML
                                    <div class="fi-proof-modal-wrap">
                                        <div class="fi-proof-modal-meta">
                                            <div class="fi-proof-modal-meta-row">
                                                <span class="fi-proof-modal-meta-label">Pelanggan</span>
                                                <span class="fi-proof-modal-meta-value">{$name} &middot; {$email}</span>
                                            </div>
                                            <div class="fi-proof-modal-meta-row">
                                                <span class="fi-proof-modal-meta-label">Jumlah</span>
                                                <span class="fi-proof-modal-meta-value fi-proof-modal-amount">{$amount}</span>
                                            </div>
                                            <div class="fi-proof-modal-meta-row">
                                                <span class="fi-proof-modal-meta-label">Tanggal</span>
                                                <span class="fi-proof-modal-meta-value">{$date}</span>
                                            </div>
                                        </div>
                                        <div class="fi-proof-modal-img-wrap">
                                            <a href="{$url}" target="_blank" class="fi-proof-modal-img-link" title="Buka gambar penuh">
                                                <img src="{$url}" alt="Bukti Transfer" class="fi-proof-modal-img"
                                                    onerror="this.style.display='none';document.getElementById('proof-fallback').style.display='flex'">
                                                <div id="proof-fallback" class="fi-proof-modal-fallback" style="display:none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:3rem;height:3rem;color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                    <span style="font-size:.8rem;color:#64748b">Gambar tidak dapat ditampilkan</span>
                                                    <a href="{$url}" target="_blank" style="font-size:.8rem;color:#1d4ed8;text-decoration:underline">Buka file ↗</a>
                                                </div>
                                            </a>
                                        </div>
                                        <p class="fi-proof-modal-open-hint">Klik gambar untuk membuka ukuran penuh</p>
                                    </div>
                                HTML);
                            })
                            ->form([
                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('Tambahkan catatan untuk approve / reject (opsional)...')
                                    ->rows(3)
                                    ->maxLength(1000),
                            ])
                            ->modalSubmitActionLabel('Approve — Tandai Lunas')
                            ->modalSubmitAction(fn ($action, Payment $record) => $action
                                ->color('success')
                                ->icon('heroicon-o-check-badge')
                                ->hidden(fn () => ! ($record->status === PaymentStatus::Pending && $record->payment_method === PaymentMethod::ManualBankTransfer))
                            )
                            ->extraModalFooterActions(fn (Payment $record): array => $record->status === PaymentStatus::Pending
                                ? [
                                    Action::make('reject_payment')
                                        ->label('Reject — Tolak Pembayaran')
                                        ->color('danger')
                                        ->icon('heroicon-o-x-circle')
                                        ->requiresConfirmation()
                                        ->modalHeading('Tolak Pembayaran?')
                                        ->modalDescription('Pembayaran akan ditandai sebagai Gagal. Pastikan catatan penolakan sudah diisi.')
                                        ->modalSubmitActionLabel('Ya, Tolak')
                                        ->action(function (Payment $record, array $data): void {
                                            $record->update([
                                                'status' => PaymentStatus::Failed,
                                                'failure_reason' => $data['notes'] ?? null,
                                            ]);

                                            if ($record->order) {
                                                $record->order->update([
                                                    'status' => OrderStatus::Failed,
                                                ]);
                                            }
                                        }),
                                ]
                                : []
                            )
                            ->action(function (Payment $record, array $data): void {
                                if ($record->status !== PaymentStatus::Pending || $record->payment_method !== PaymentMethod::ManualBankTransfer) {
                                    return;
                                }

                                $user = auth()->user();

                                if (! $user instanceof \App\Models\User) {
                                    throw new \RuntimeException('Unauthorized.');
                                }

                                app(MarkPaymentAsPaidAction::class)->execute($record, $user);

                                if (filled($data['notes'] ?? null)) {
                                    $record->update(['failure_reason' => null]);
                                }
                            })
                    )
                    ->sortable(),

                IconColumn::make('proof_of_payment')
                    ->label('Proof')
                    ->boolean()
                    ->getStateUsing(fn (Payment $record): bool => filled($record->proof_of_payment))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('verified_at')
                    ->label('Verified at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Filter::make('needs_verification')
                    ->label('Perlu Verifikasi')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', PaymentStatus::Pending)
                        ->where('payment_method', PaymentMethod::ManualBankTransfer)
                    )
                    ->toggle()
                    ->indicateUsing(fn (array $data): ?string => $data['isActive'] ? 'Perlu Verifikasi' : null),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(PaymentStatus::cases())
                        ->mapWithKeys(fn (PaymentStatus $s) => [$s->value => $s->label()])
                        ->all())
                    ->multiple()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options(collect(PaymentMethod::cases())
                        ->mapWithKeys(fn (PaymentMethod $m) => [$m->value => $m->label()])
                        ->all()),

                TernaryFilter::make('has_proof')
                    ->label('Bukti Transfer')
                    ->placeholder('Semua')
                    ->trueLabel('Ada bukti')
                    ->falseLabel('Tanpa bukti')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('proof_of_payment')->where('proof_of_payment', '!=', ''),
                        false: fn (Builder $q) => $q->where(fn ($q) => $q->whereNull('proof_of_payment')->orWhere('proof_of_payment', '')),
                        blank: fn (Builder $q) => $q,
                    ),

                Filter::make('created_from')
                    ->label('Tanggal Mulai')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari tanggal')
                            ->displayFormat('d M Y')
                            ->native(false),
                    ])
                    ->query(fn (Builder $q, array $data): Builder => $q->when(
                        $data['created_from'] ?? null,
                        fn ($q, $date) => $q->whereDate('created_at', '>=', $date)
                    ))
                    ->indicateUsing(fn (array $data): ?string => filled($data['created_from'] ?? null)
                        ? 'Dari: '.date('d M Y', strtotime($data['created_from']))
                        : null
                    ),

                Filter::make('created_until')
                    ->label('Tanggal Akhir')
                    ->form([
                        DatePicker::make('created_until')
                            ->label('Sampai tanggal')
                            ->displayFormat('d M Y')
                            ->native(false),
                    ])
                    ->query(fn (Builder $q, array $data): Builder => $q->when(
                        $data['created_until'] ?? null,
                        fn ($q, $date) => $q->whereDate('created_at', '<=', $date)
                    ))
                    ->indicateUsing(fn (array $data): ?string => filled($data['created_until'] ?? null)
                        ? 'Sampai: '.date('d M Y', strtotime($data['created_until']))
                        : null
                    ),

                TernaryFilter::make('is_verified')
                    ->label('Verifikasi Admin')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah diverifikasi')
                    ->falseLabel('Belum diverifikasi')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('verified_at'),
                        false: fn (Builder $q) => $q->whereNull('verified_at'),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->label('Filter')
                    ->icon('heroicon-o-funnel')
            )
            ->recordActions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit'),

                Action::make('mark_as_paid')
                    ->label('')
                    ->tooltip('Tandai Lunas')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Pembayaran sebagai Lunas?')
                    ->modalDescription('Pastikan bukti transfer sudah diverifikasi sebelum menandai sebagai lunas.')
                    ->modalSubmitActionLabel('Ya, Tandai Lunas')
                    ->visible(fn (Payment $record): bool => $record->status === PaymentStatus::Pending && $record->payment_method === PaymentMethod::ManualBankTransfer)
                    ->action(function (Payment $record): void {
                        $user = auth()->user();

                        if (! $user instanceof \App\Models\User) {
                            throw new \RuntimeException('Unauthorized.');
                        }

                        app(MarkPaymentAsPaidAction::class)->execute($record, $user);
                    }),
            ]);
    }
}
