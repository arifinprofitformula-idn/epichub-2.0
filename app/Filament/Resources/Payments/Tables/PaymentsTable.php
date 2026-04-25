<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Actions\Payments\MarkPaymentAsPaidAction;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label('Payment No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.user.email')
                    ->label('Customer')
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
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (string|int|float|null $state): string => 'Rp '.number_format((float) ($state ?? 0), 0, ',', '.'))
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

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('mark_as_paid')
                    ->label('Mark as paid')
                    ->color('primary')
                    ->requiresConfirmation()
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

