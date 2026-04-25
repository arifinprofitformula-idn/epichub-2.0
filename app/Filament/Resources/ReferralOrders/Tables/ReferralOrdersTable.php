<?php

namespace App\Filament\Resources\ReferralOrders\Tables;

use App\Enums\ReferralOrderStatus;
use App\Models\EpiChannel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('epiChannel.epic_code')
                    ->label('EPIC')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('buyer.email')
                    ->label('Buyer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ReferralOrderStatus|string|null $state): string => $state instanceof ReferralOrderStatus ? $state->label() : (string) $state)
                    ->sortable(),

                TextColumn::make('attributed_at')
                    ->label('Attributed')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('epi_channel_id')
                    ->label('EPI Channel')
                    ->options(fn () => EpiChannel::query()->orderBy('epic_code')->pluck('epic_code', 'id')),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ReferralOrderStatus::cases())->mapWithKeys(fn (ReferralOrderStatus $status) => [$status->value => $status->label()])->all()),
            ])
            ->defaultSort('attributed_at', 'desc');
    }
}
