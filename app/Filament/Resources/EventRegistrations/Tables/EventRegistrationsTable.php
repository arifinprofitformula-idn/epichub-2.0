<?php

namespace App\Filament\Resources\EventRegistrations\Tables;

use App\Actions\Event\CancelEventRegistrationAction;
use App\Actions\Event\MarkEventAttendedAction;
use App\Enums\EventRegistrationStatus;
use App\Models\Event;
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

class EventRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (EventRegistrationStatus|string|null $state): string => $state instanceof EventRegistrationStatus ? $state->label() : (string) $state)
                    ->sortable(),

                TextColumn::make('registered_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('attended_at')
                    ->label('Attended')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('order_id')
                    ->label('Order ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_product_id')
                    ->label('UserProduct ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(fn () => Event::query()->orderBy('starts_at')->orderBy('title')->pluck('title', 'id')),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(EventRegistrationStatus::cases())->mapWithKeys(fn (EventRegistrationStatus $s) => [$s->value => $s->label()])->all()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('markAttended')
                    ->label('Mark attended')
                    ->color('success')
                    ->visible(fn (\App\Models\EventRegistration $record): bool => $record->status !== EventRegistrationStatus::Attended)
                    ->action(function (\App\Models\EventRegistration $record): void {
                        app(MarkEventAttendedAction::class)->execute($record, auth()->user());
                    }),
                Action::make('cancel')
                    ->label('Cancel')
                    ->color('warning')
                    ->visible(fn (\App\Models\EventRegistration $record): bool => $record->status !== EventRegistrationStatus::Cancelled)
                    ->requiresConfirmation()
                    ->action(function (\App\Models\EventRegistration $record): void {
                        app(CancelEventRegistrationAction::class)->execute($record, auth()->user());
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

