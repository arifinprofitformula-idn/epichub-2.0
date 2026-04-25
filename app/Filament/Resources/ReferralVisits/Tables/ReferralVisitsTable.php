<?php

namespace App\Filament\Resources\ReferralVisits\Tables;

use App\Models\EpiChannel;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralVisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('clicked_at')
                    ->label('Clicked')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('epiChannel.epic_code')
                    ->label('EPIC')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.title')
                    ->label('Product')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('landing_url')
                    ->label('Landing')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::query()->orderBy('title')->pluck('title', 'id')),
            ])
            ->defaultSort('clicked_at', 'desc');
    }
}

