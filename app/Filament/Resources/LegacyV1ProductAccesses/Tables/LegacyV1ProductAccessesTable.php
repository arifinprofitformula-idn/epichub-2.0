<?php

namespace App\Filament\Resources\LegacyV1ProductAccesses\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1ProductAccessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),

                TextColumn::make('row_number')
                    ->label('Row')
                    ->sortable(),

                TextColumn::make('raw_identifier_type')
                    ->label('Identifier')
                    ->badge(),

                TextColumn::make('raw_identifier_value')
                    ->label('Nilai')
                    ->searchable(),

                TextColumn::make('normalized_legacy_product_key')
                    ->label('Legacy Product Key')
                    ->searchable(),

                TextColumn::make('mappedProduct.title')
                    ->label('Produk 2.0')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('matchedUser.email')
                    ->label('User 2.0')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'staged' => 'Staged',
                        'granted' => 'Granted',
                        'reactivated' => 'Reactivated',
                        'duplicate' => 'Duplicate',
                        'unmapped_product' => 'Unmapped Product',
                        'unresolved_user' => 'Unresolved User',
                        'conflict' => 'Conflict',
                        'error' => 'Error',
                        'rolled_back' => 'Rolled Back',
                    ]),
            ]);
    }
}
