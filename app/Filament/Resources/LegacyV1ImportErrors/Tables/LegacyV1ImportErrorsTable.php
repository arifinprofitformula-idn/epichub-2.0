<?php

namespace App\Filament\Resources\LegacyV1ImportErrors\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1ImportErrorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),

                TextColumn::make('scope')
                    ->label('Scope')
                    ->badge(),

                TextColumn::make('severity')
                    ->label('Severity')
                    ->badge(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('legacyUser.row_number')
                    ->label('User Row')
                    ->placeholder('-'),

                TextColumn::make('legacyProductAccess.row_number')
                    ->label('Access Row')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('scope')
                    ->options([
                        'user' => 'User',
                        'access' => 'Access',
                        'sponsor' => 'Sponsor',
                    ]),
                SelectFilter::make('severity')
                    ->options([
                        'conflict' => 'Conflict',
                        'error' => 'Error',
                        'warning' => 'Warning',
                    ]),
            ]);
    }
}
