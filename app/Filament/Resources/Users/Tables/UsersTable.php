<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari nama, email, WhatsApp, atau ID EPIC')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Kontak')
                    ->description(fn (User $record): string => $record->whatsapp_number ?: '-')
                    ->searchable(['email', 'whatsapp_number'])
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('epiChannel.status')
                    ->label('Status EPI Channel')
                    ->formatStateUsing(fn ($state): string => method_exists($state, 'label') ? $state->label() : ((string) $state ?: 'Belum'))
                    ->badge()
                    ->color(fn (User $record): string => match ($record->epiChannel?->status?->value) {
                        'active' => 'success',
                        'qualified' => 'info',
                        'prospect' => 'warning',
                        'inactive', 'suspended' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('epiChannel.epic_code')
                    ->label('ID EPIC')
                    ->placeholder('-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhereHas('epiChannel', fn (Builder $epiQuery) => $epiQuery->where('epic_code', 'like', "%{$search}%"));
                    })
                    ->toggleable(),

                TextColumn::make('referrerEpiChannel.epic_code')
                    ->label('ID EPIC Pereferral')
                    ->placeholder('-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhereHas('referrerEpiChannel', fn (Builder $epiQuery) => $epiQuery->where('epic_code', 'like', "%{$search}%"));
                    })
                    ->toggleable(),

                TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->sortable(),

                TextColumn::make('active_user_products_count')
                    ->label('Produk Aktif')
                    ->sortable(),

                TextColumn::make('latest_oms_status')
                    ->label('OMS')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'success' => 'Synced',
                        'failed' => 'Error',
                        default => 'Belum',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_activity_at')
                    ->label('Aktivitas Terakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('last_activity_at', $direction))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(Role::query()->orderBy('name')->pluck('name', 'name')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! filled($value)) {
                            return $query;
                        }

                        return $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $value));
                    }),

                TernaryFilter::make('email_verified')
                    ->label('Email terverifikasi')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query,
                    ),

                SelectFilter::make('epi_channel_status')
                    ->label('Status EPI Channel')
                    ->options([
                        'prospect' => 'Prospect',
                        'qualified' => 'Qualified',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'inactive' => 'Inactive',
                        'none' => 'Belum ada',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! filled($value)) {
                            return $query;
                        }

                        if ($value === 'none') {
                            return $query->whereDoesntHave('epiChannel');
                        }

                        return $query->whereHas('epiChannel', fn (Builder $epiQuery) => $epiQuery->where('status', $value));
                    }),

                TernaryFilter::make('has_orders')
                    ->label('Punya order')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('orders'),
                        false: fn (Builder $query) => $query->whereDoesntHave('orders'),
                        blank: fn (Builder $query) => $query,
                    ),

                TernaryFilter::make('has_access_products')
                    ->label('Punya akses produk')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('userProducts', fn (Builder $userProductQuery) => $userProductQuery->active()),
                        false: fn (Builder $query) => $query->whereDoesntHave('userProducts', fn (Builder $userProductQuery) => $userProductQuery->active()),
                        blank: fn (Builder $query) => $query,
                    ),

                Filter::make('created_at')
                    ->label('Tanggal daftar')
                    ->form([
                        DatePicker::make('registered_from')
                            ->label('Dari'),
                        DatePicker::make('registered_until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['registered_from'] ?? null),
                                fn (Builder $userQuery) => $userQuery->whereDate('created_at', '>=', $data['registered_from']),
                            )
                            ->when(
                                filled($data['registered_until'] ?? null),
                                fn (Builder $userQuery) => $userQuery->whereDate('created_at', '<=', $data['registered_until']),
                            );
                    }),

                TernaryFilter::make('locked_referrer')
                    ->label('Locked referrer')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('referrer_epi_channel_id'),
                        false: fn (Builder $query) => $query->whereNull('referrer_epi_channel_id'),
                        blank: fn (Builder $query) => $query,
                    ),

                SelectFilter::make('oms_sync_status')
                    ->label('OMS sync')
                    ->options([
                        'success' => 'Synced',
                        'failed' => 'Error',
                        'none' => 'Belum ada log',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! filled($value)) {
                            return $query;
                        }

                        if ($value === 'none') {
                            return $query->whereDoesntHave('omsIntegrationLogsByEmail');
                        }

                        return $query->whereHas('omsIntegrationLogsByEmail', fn (Builder $omsQuery) => $omsQuery->where('status', $value));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detail'),

                    EditAction::make()
                        ->label('Edit profil'),

                    Action::make('edit_roles')
                        ->label('Edit role')
                        ->icon('heroicon-o-shield-check')
                        ->visible(fn (User $record): bool => UserResource::canManageRoles() && UserResource::canModifyRolesForRecord($record))
                        ->fillForm(fn (User $record): array => [
                            'roles' => $record->roles->pluck('name')->all(),
                        ])
                        ->form([
                            Select::make('roles')
                                ->label('Role')
                                ->multiple()
                                ->options(UserResource::assignableRolesForCurrentActor())
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (User $record, array $data): void {
                            $actor = auth()->user();

                            if (! $actor instanceof User) {
                                throw new RuntimeException('Unauthorized.');
                            }

                            try {
                                UserResource::syncRolesForUser($record, (array) ($data['roles'] ?? []), $actor);
                            } catch (RuntimeException $exception) {
                                Notification::make()
                                    ->title('Perubahan role ditolak')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Role pengguna diperbarui')
                                ->success()
                                ->send();
                        }),

                    Action::make('send_reset_password')
                        ->label('Kirim reset password')
                        ->icon('heroicon-o-envelope')
                        ->visible(fn (): bool => UserResource::canManageUsers())
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $actor = auth()->user();

                            if (! $actor instanceof User) {
                                throw new RuntimeException('Unauthorized.');
                            }

                            try {
                                UserResource::sendResetPasswordLink($record, $actor);
                            } catch (RuntimeException $exception) {
                                Notification::make()
                                    ->title('Gagal mengirim reset password')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Link reset password dikirim')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
