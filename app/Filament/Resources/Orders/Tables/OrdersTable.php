<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Customer')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('referrerEpiChannel.epic_code')
                    ->label('Referrer')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus|string|null $state): string => $state instanceof OrderStatus ? $state->label() : (string) $state)
                    ->color(fn (OrderStatus|string|null $state): string => $state instanceof OrderStatus ? $state->getColor() : (OrderStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn (string|int|float|null $state): string => 'Rp '.number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}

