<?php

namespace App\Filament\Resources\LegacyV1UserMappings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1UserMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),
                TextColumn::make('legacy_user_id')
                    ->label('Legacy User ID')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_epic_id')
                    ->label('ID EPIC')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_email')
                    ->label('Email Legacy')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('User 2.0')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('match_method')
                    ->label('Match')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                        'conflict' => 'Conflict',
                    ]),
            ]);
    }
}
