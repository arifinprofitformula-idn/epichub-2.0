<?php

namespace App\Filament\Resources\LegacyV1Orders\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),
                TextColumn::make('legacy_order_number')
                    ->label('Order No')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_customer_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_user_epic_id')
                    ->label('ID EPIC')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('User 2.0')
                    ->placeholder('-'),
                TextColumn::make('normalized_status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('ordered_at')
                    ->label('Ordered At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('normalized_status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                        'unknown' => 'Unknown',
                    ]),
            ]);
    }
}
