<?php

namespace App\Filament\Resources\LegacyV1ProductMappings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LegacyV1ProductMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('legacy_product_key')
            ->columns([
                TextColumn::make('legacy_product_key')
                    ->label('Legacy Product Key')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('legacy_product_name')
                    ->label('Legacy Product Name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('product.title')
                    ->label('Produk 2.0')
                    ->placeholder('-')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('mapped_at')
                    ->label('Mapped At')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
