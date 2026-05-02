<?php

namespace App\Filament\Resources\Commissions\Tables;

use App\Actions\Affiliates\ApproveCommissionAction;
use App\Actions\Affiliates\RejectCommissionAction;
use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('epiChannel.epic_code')
                    ->label('EPIC')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.title')
                    ->label('Produk')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission_amount')
                    ->label('Komisi')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (CommissionStatus|string|null $state): string => $state instanceof CommissionStatus ? $state->label() : (string) $state)
                    ->color(fn (CommissionStatus|string|null $state): string => $state instanceof CommissionStatus ? $state->getColor() : (CommissionStatus::tryFrom((string) $state)?->getColor() ?? 'gray'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(CommissionStatus::cases())->mapWithKeys(fn (CommissionStatus $status) => [$status->value => $status->label()])->all()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check-badge')
                    ->iconButton()
                    ->tooltip('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Commission $record): bool => $record->status === CommissionStatus::Pending)
                    ->action(function (Commission $record): void {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new \RuntimeException('Unauthorized.');
                        }

                        app(ApproveCommissionAction::class)->execute($record, $actor);
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->tooltip('Reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan reject')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (Commission $record): bool => in_array($record->status, [CommissionStatus::Pending, CommissionStatus::Approved], true))
                    ->action(function (Commission $record, array $data): void {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new \RuntimeException('Unauthorized.');
                        }

                        app(RejectCommissionAction::class)->execute($record, $actor, (string) ($data['reason'] ?? ''));
                    }),
            ])
            ->actionsColumnLabel('Action');
    }
}
