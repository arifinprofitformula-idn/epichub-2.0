<?php

namespace App\Filament\Resources\LegacyV1Commissions\Tables;

use App\Actions\LegacyV1\MarkLegacyV1CommissionAsPayableAction;
use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Enums\LegacyV1CommissionStatus;
use App\Filament\Resources\LegacyV1Commissions\LegacyV1CommissionResource;
use App\Models\LegacyV1Commission;
use App\Models\LegacyV1CommissionImportError;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class LegacyV1CommissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('earned_at', 'desc')
            ->recordUrl(fn (LegacyV1Commission $record): string => LegacyV1CommissionResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('importBatch.id')
                    ->label('Batch')
                    ->sortable(),
                TextColumn::make('source_label')
                    ->label('Sumber')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('legacy_commission_id')
                    ->label('Legacy ID')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('legacy_user_epic_id')
                    ->label('ID EPIC')
                    ->searchable()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('legacy_user_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('Unresolved'),
                TextColumn::make('epiChannel.epic_code')
                    ->label('EPI Channel')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('product.title')
                    ->label('Produk 2.0')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('legacy_product_name')
                    ->label('Produk Legacy')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('commission_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? str($state)->headline()->value() : '-')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('commission_level')
                    ->label('Level')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('commission_amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('commission_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LegacyV1CommissionStatus|string|null $state): string => $state instanceof LegacyV1CommissionStatus ? $state->label() : (string) $state)
                    ->color(fn (LegacyV1CommissionStatus|string|null $state): string => $state instanceof LegacyV1CommissionStatus ? $state->getColor() : (LegacyV1CommissionStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),
                TextColumn::make('migration_status')
                    ->label('Migration')
                    ->badge()
                    ->formatStateUsing(fn (LegacyV1CommissionMigrationStatus|string|null $state): string => $state instanceof LegacyV1CommissionMigrationStatus ? $state->label() : (string) $state)
                    ->color(fn (LegacyV1CommissionMigrationStatus|string|null $state): string => $state instanceof LegacyV1CommissionMigrationStatus ? $state->getColor() : (LegacyV1CommissionMigrationStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),
                TextColumn::make('earned_at')
                    ->label('Earned At')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('import_batch_id')
                    ->label('Batch')
                    ->relationship('importBatch', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('legacy_user_epic_id')
                    ->label('ID EPIC')
                    ->form([
                        TextInput::make('legacy_user_epic_id')
                            ->label('Legacy ID EPIC'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['legacy_user_epic_id'] ?? null),
                            fn (Builder $builder) => $builder->where('legacy_user_epic_id', 'like', '%'.trim((string) $data['legacy_user_epic_id']).'%'),
                        );
                    }),
                Filter::make('legacy_user_email')
                    ->label('Email')
                    ->form([
                        TextInput::make('legacy_user_email')
                            ->label('Legacy Email'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['legacy_user_email'] ?? null),
                            fn (Builder $builder) => $builder->where('legacy_user_email', 'like', '%'.trim((string) $data['legacy_user_email']).'%'),
                        );
                    }),
                SelectFilter::make('commission_status')
                    ->label('Status')
                    ->options(collect(LegacyV1CommissionStatus::cases())->mapWithKeys(fn (LegacyV1CommissionStatus $status) => [$status->value => $status->label()])->all()),
                Filter::make('period')
                    ->label('Periode')
                    ->form([
                        TextInput::make('period_month')
                            ->label('Bulan')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('period_year')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['period_month'] ?? null), fn (Builder $builder) => $builder->where('legacy_period_month', (int) $data['period_month']))
                            ->when(filled($data['period_year'] ?? null), fn (Builder $builder) => $builder->where('legacy_period_year', (int) $data['period_year']));
                    }),
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload(),
                Filter::make('commission_amount')
                    ->label('Nominal')
                    ->form([
                        TextInput::make('amount_min')
                            ->label('Minimal')
                            ->numeric(),
                        TextInput::make('amount_max')
                            ->label('Maksimal')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['amount_min'] ?? null), fn (Builder $builder) => $builder->where('commission_amount', '>=', (float) $data['amount_min']))
                            ->when(filled($data['amount_max'] ?? null), fn (Builder $builder) => $builder->where('commission_amount', '<=', (float) $data['amount_max']));
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (LegacyV1Commission $record): string => LegacyV1CommissionResource::getUrl('view', ['record' => $record])),
                Action::make('resolve_user')
                    ->label('Resolve User')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Select::make('user_id')
                            ->label('User EPIC HUB 2.0')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->name.' - '.$record->email)
                            ->required(),
                    ])
                    ->action(function (LegacyV1Commission $record, array $data): void {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new RuntimeException('Unauthorized.');
                        }

                        $user = User::query()->with('epiChannel')->findOrFail((int) $data['user_id']);
                        $hasLegacyProduct = $record->legacy_product_code !== null || $record->legacy_product_name !== null;
                        $migrationStatus = $record->commission_status === LegacyV1CommissionStatus::Unknown
                            ? LegacyV1CommissionMigrationStatus::UnknownStatus
                            : (($record->product_id === null && $hasLegacyProduct)
                                ? LegacyV1CommissionMigrationStatus::UnresolvedProduct
                                : LegacyV1CommissionMigrationStatus::Resolved);

                        $record->forceFill([
                            'user_id' => $user->id,
                            'epi_channel_id' => $user->epiChannel?->id,
                            'migration_status' => $migrationStatus,
                        ])->save();

                        LegacyV1CommissionImportError::query()
                            ->where('import_batch_id', $record->import_batch_id)
                            ->where('legacy_v1_commission_id', $record->id)
                            ->whereIn('code', ['unresolved_user', 'identity_conflict'])
                            ->whereNull('resolved_at')
                            ->update([
                                'resolved_at' => now(),
                                'resolved_by' => $actor->id,
                                'updated_at' => now(),
                            ]);

                        Notification::make()
                            ->title('User legacy commission berhasil di-resolve')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_payable')
                    ->label('Mark Payable')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (LegacyV1Commission $record): bool => $record->user_id !== null && $record->payout_id === null && ! $record->is_payable && $record->commission_status === LegacyV1CommissionStatus::Approved)
                    ->requiresConfirmation()
                    ->action(function (LegacyV1Commission $record): void {
                        app(MarkLegacyV1CommissionAsPayableAction::class)->execute($record, true);

                        Notification::make()
                            ->title('Legacy commission ditandai payable')
                            ->success()
                            ->send();
                    }),
                Action::make('unmark_payable')
                    ->label('Batalkan Payable')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (LegacyV1Commission $record): bool => $record->payout_id === null && $record->is_payable)
                    ->requiresConfirmation()
                    ->action(function (LegacyV1Commission $record): void {
                        app(MarkLegacyV1CommissionAsPayableAction::class)->execute($record, false);

                        Notification::make()
                            ->title('Status payable legacy commission dibatalkan')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
