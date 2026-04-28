<?php

namespace App\Filament\Resources\LegacyV1SponsorLinks\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1SponsorLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch')
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('User 2.0')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('sponsor_legacy_epic_id')
                    ->label('Sponsor Legacy')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('resolvedReferrerEpiChannel.epic_code')
                    ->label('Resolved Channel')
                    ->placeholder('-'),

                TextColumn::make('resolution_status')
                    ->label('Status')
                    ->badge(),

                IconColumn::make('forced')
                    ->label('Force')
                    ->boolean(),

                TextColumn::make('applied_at')
                    ->label('Applied At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('resolution_status')
                    ->options([
                        'resolved' => 'Resolved',
                        'fallback_house' => 'Fallback House',
                        'existing_locked' => 'Existing Locked',
                        'self_referral' => 'Self Referral',
                        'forced' => 'Forced',
                        'unresolved' => 'Unresolved',
                    ]),
            ]);
    }
}
