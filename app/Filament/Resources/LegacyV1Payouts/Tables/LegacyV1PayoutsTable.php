<?php

namespace App\Filament\Resources\LegacyV1Payouts\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),
                TextColumn::make('legacy_payout_id')
                    ->label('Legacy Payout ID')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_user_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_user_epic_id')
                    ->label('ID EPIC')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('normalized_status')
                    ->label('Status')
                    ->badge(),
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
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        'unknown' => 'Unknown',
                    ]),
            ]);
    }
}
