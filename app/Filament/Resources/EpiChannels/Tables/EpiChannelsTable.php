<?php

namespace App\Filament\Resources\EpiChannels\Tables;

use App\Actions\Affiliates\ActivateEpiChannelAction;
use App\Actions\Affiliates\SuspendEpiChannelAction;
use App\Enums\EpiChannelStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EpiChannelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('epic_code')
                    ->label('EPIC')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('store_name')
                    ->label('Store')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (EpiChannelStatus|string|null $state): string => $state instanceof EpiChannelStatus ? $state->label() : (string) $state)
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('activated_at')
                    ->label('Activated')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(EpiChannelStatus::cases())->mapWithKeys(fn (EpiChannelStatus $s) => [$s->value => $s->label()])->all()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('activate')
                    ->label('Activate')
                    ->color('success')
                    ->visible(fn (\App\Models\EpiChannel $record): bool => $record->status !== EpiChannelStatus::Active)
                    ->action(function (\App\Models\EpiChannel $record): void {
                        app(ActivateEpiChannelAction::class)->execute($record, auth()->user());
                    }),
                Action::make('suspend')
                    ->label('Suspend')
                    ->color('warning')
                    ->visible(fn (\App\Models\EpiChannel $record): bool => $record->status === EpiChannelStatus::Active)
                    ->requiresConfirmation()
                    ->action(function (\App\Models\EpiChannel $record): void {
                        app(SuspendEpiChannelAction::class)->execute($record, auth()->user());
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

