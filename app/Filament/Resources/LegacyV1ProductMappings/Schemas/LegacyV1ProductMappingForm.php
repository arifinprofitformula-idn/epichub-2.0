<?php

namespace App\Filament\Resources\LegacyV1ProductMappings\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LegacyV1ProductMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('legacy_product_key')
                    ->label('Legacy Product Key')
                    ->required()
                    ->maxLength(255),

                TextInput::make('legacy_product_name')
                    ->label('Legacy Product Name')
                    ->maxLength(255),

                Select::make('product_id')
                    ->label('Produk EPIC HUB 2.0')
                    ->options(fn (): array => Product::query()->orderBy('title')->pluck('title', 'id')->all())
                    ->searchable()
                    ->preload(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(4),
            ]);
    }
}
