<?php

namespace App\Filament\Resources\LegacyV1Payments\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),
                TextColumn::make('legacy_payment_number')
                    ->label('Payment No')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_order_id')
                    ->label('Legacy Order')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_user_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('normalized_status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->placeholder('-'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Paid At')
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
