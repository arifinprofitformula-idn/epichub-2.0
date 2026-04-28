<?php

namespace App\Filament\Resources\LegacyV1Users\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1UsersTable
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

                TextColumn::make('normalized_name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('normalized_epic_id')
                    ->label('ID EPIC')
                    ->searchable(),

                TextColumn::make('normalized_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('normalized_whatsapp')
                    ->label('WhatsApp')
                    ->toggleable(),

                TextColumn::make('importedUser.email')
                    ->label('User 2.0')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('match_status')
                    ->label('Match')
                    ->badge(),

                TextColumn::make('sponsor_status')
                    ->label('Sponsor')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'imported' => 'Imported',
                        'conflict' => 'Conflict',
                        'pending' => 'Pending',
                    ]),
                SelectFilter::make('sponsor_status')
                    ->options([
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                        'fallback_house' => 'Fallback House',
                        'existing_locked' => 'Existing Locked',
                        'forced' => 'Forced',
                        'unresolved' => 'Unresolved',
                    ]),
            ]);
    }
}
