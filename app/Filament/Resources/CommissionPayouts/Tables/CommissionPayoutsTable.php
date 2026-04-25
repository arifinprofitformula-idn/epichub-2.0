<?php

namespace App\Filament\Resources\CommissionPayouts\Tables;

use App\Actions\Affiliates\MarkPayoutPaidAction;
use App\Enums\PayoutStatus;
use App\Models\CommissionPayout;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommissionPayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payout_number')
                    ->label('Payout')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('epiChannel.epic_code')
                    ->label('EPIC')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (PayoutStatus|string|null $state): string => $state instanceof PayoutStatus ? $state->label() : (string) $state)
                    ->sortable(),
                TextColumn::make('commissions_count')
                    ->label('Komisi')
                    ->counts('commissions'),
                TextColumn::make('paid_at')
                    ->label('Paid at')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(PayoutStatus::cases())->mapWithKeys(fn (PayoutStatus $status) => [$status->value => $status->label()])->all()),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CommissionPayout $record): bool => in_array($record->status, [PayoutStatus::Draft, PayoutStatus::Processing], true))
                    ->action(function (CommissionPayout $record): void {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new \RuntimeException('Unauthorized.');
                        }

                        app(MarkPayoutPaidAction::class)->execute($record, $actor);
                    }),
            ]);
    }
}
