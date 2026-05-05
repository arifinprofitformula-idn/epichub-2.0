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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

                TextColumn::make('visibility_mode')
                    ->label('Visibility Rule')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'public'            => 'Public',
                        'logged_in_only'    => 'Login Only',
                        'selected_audience' => 'Audience',
                        'hidden'            => 'Hidden',
                        default             => 'Public',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'public'            => 'success',
                        'logged_in_only'    => 'info',
                        'selected_audience' => 'warning',
                        'hidden'            => 'danger',
                        default             => 'success',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('purchase_mode')
                    ->label('Purchase Rule')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'everyone'          => 'Everyone',
                        'logged_in_only'    => 'Login Only',
                        'selected_audience' => 'Audience',
                        'disabled'          => 'Disabled',
                        default             => 'Everyone',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'everyone'          => 'success',
                        'logged_in_only'    => 'info',
                        'selected_audience' => 'warning',
                        'disabled'          => 'danger',
                        default             => 'success',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('hidden_from_marketplace')
                    ->label('Hidden MKT')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
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

                SelectFilter::make('visibility_mode')
                    ->label('Visibility Rule')
                    ->options([
                        'public'            => 'Public',
                        'logged_in_only'    => 'Login Only',
                        'selected_audience' => 'Audience Tertentu',
                        'hidden'            => 'Hidden',
                    ]),

                SelectFilter::make('purchase_mode')
                    ->label('Purchase Rule')
                    ->options([
                        'everyone'          => 'Semua orang',
                        'logged_in_only'    => 'Login Only',
                        'selected_audience' => 'Audience Tertentu',
                        'disabled'          => 'Dinonaktifkan',
                    ]),

                TernaryFilter::make('hidden_from_marketplace')
                    ->label('Hidden dari Marketplace'),

                Filter::make('epi_channel_only')
                    ->label('Khusus EPI Channel')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(function (Builder $q): void {
                            $q->whereJsonContains('allowed_viewer_types', 'epi_channel_active')
                              ->orWhereJsonContains('allowed_buyer_types', 'epi_channel_active');
                        })
                    ),
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
