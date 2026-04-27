<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\ProductCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumb')
                    ->disk('public')
                    ->square()
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (ProductType|string|null $state): string => $state instanceof ProductType ? $state->label() : (string) $state)
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn (string|int|float|null $state): string => 'Rp '.number_format((float) ($state ?? 0), 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->label('Promo')
                    ->formatStateUsing(fn (string|int|float|null $state): string => $state === null ? '-' : 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ProductStatus|string|null $state): string => $state instanceof ProductStatus ? $state->label() : (string) $state)
                    ->color(fn (ProductStatus|string|null $state): string => $state instanceof ProductStatus ? $state->getColor() : (ProductStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),

                TextColumn::make('visibility')
                    ->label('Visibilitas')
                    ->badge()
                    ->formatStateUsing(fn (ProductVisibility|string|null $state): string => $state instanceof ProductVisibility ? $state->label() : (string) $state)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_affiliate_enabled')
                    ->label('Affiliate')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('publish_at')
                    ->label('Publish')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('product_type')
                    ->label('Tipe')
                    ->options(collect(ProductType::cases())->mapWithKeys(fn (ProductType $type) => [$type->value => $type->label()])->all()),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status) => [$status->value => $status->label()])->all()),
                SelectFilter::make('visibility')
                    ->label('Visibilitas')
                    ->options(collect(ProductVisibility::cases())->mapWithKeys(fn (ProductVisibility $visibility) => [$visibility->value => $visibility->label()])->all()),
                SelectFilter::make('product_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
                TernaryFilter::make('is_affiliate_enabled')
                    ->label('Affiliate'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('publish')
                    ->label('Publish')
                    ->color('primary')
                    ->visible(fn (\App\Models\Product $record): bool => $record->status !== ProductStatus::Published)
                    ->action(function (\App\Models\Product $record): void {
                        $record->update([
                            'status' => ProductStatus::Published,
                            'publish_at' => $record->publish_at ?? Carbon::now(),
                        ]);
                    }),
                Action::make('archive')
                    ->label('Archive')
                    ->color('warning')
                    ->visible(fn (\App\Models\Product $record): bool => $record->status !== ProductStatus::Archived)
                    ->action(function (\App\Models\Product $record): void {
                        $record->update([
                            'status' => ProductStatus::Archived,
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
