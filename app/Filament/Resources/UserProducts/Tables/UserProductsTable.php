<?php

namespace App\Filament\Resources\UserProducts\Tables;

use App\Actions\Access\RevokeProductAccessAction;
use App\Enums\ProductType;
use App\Enums\UserProductStatus;
use App\Models\User;
use App\Models\UserProduct;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.title')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.product_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (ProductType|string|null $state): string => $state instanceof ProductType ? $state->label() : (string) $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (UserProductStatus|string|null $state): string => $state instanceof UserProductStatus ? $state->label() : (string) $state)
                    ->color(fn (UserProductStatus|string|null $state): string => $state instanceof UserProductStatus ? $state->getColor() : (UserProductStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('Order No.')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sourceProduct.title')
                    ->label('Source (bundle)')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('granted_at')
                    ->label('Granted')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('revoked_at')
                    ->label('Revoked')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(UserProductStatus::cases())->mapWithKeys(fn (UserProductStatus $s) => [$s->value => $s->label()])->all()),

                SelectFilter::make('product_type')
                    ->label('Tipe produk')
                    ->options(collect(ProductType::cases())->mapWithKeys(fn (ProductType $t) => [$t->value => $t->label()])->all())
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }

                        return $query->whereHas('product', fn ($q) => $q->where('product_type', $value));
                    }),

                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'order' => 'Order',
                        'manual' => 'Manual',
                        'bundle' => 'Bundle',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }

                        return match ($value) {
                            'bundle' => $query->whereNotNull('source_product_id'),
                            'order' => $query->whereNotNull('order_id')->whereNull('source_product_id'),
                            'manual' => $query->whereNull('order_id')->whereNull('source_product_id'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (UserProduct $record): bool => $record->status === UserProductStatus::Active)
                    ->action(function (UserProduct $record, array $data): void {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new \RuntimeException('Unauthorized.');
                        }

                        app(RevokeProductAccessAction::class)->execute($record, $actor, (string) $data['reason']);
                    }),
            ]);
    }
}

