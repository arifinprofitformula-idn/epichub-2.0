<?php

namespace App\Filament\Resources\EmailNotificationLogs\Tables;

use App\Filament\Resources\EmailNotificationLogs\EmailNotificationLogResource;
use App\Models\EmailNotificationLog;
use App\Services\Notifications\EmailRetryService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailNotificationLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Event')
                    ->placeholder('-')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mailketing'   => 'primary',
                        'laravel'      => 'info',
                        default        => 'gray',
                    }),

                TextColumn::make('recipient_email')
                    ->label('Penerima')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent'    => 'success',
                        'failed'  => 'danger',
                        'skipped' => 'gray',
                        'pending' => 'warning',
                        default   => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Terkirim')
                    ->dateTime('d M H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('failed_at')
                    ->label('Gagal')
                    ->dateTime('d M H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'sent'    => 'Sent',
                        'failed'  => 'Failed',
                        'skipped' => 'Skipped',
                    ]),

                SelectFilter::make('provider')
                    ->label('Provider')
                    ->options([
                        'mailketing'   => 'Mailketing',
                        'laravel'      => 'Laravel Mail',
                    ]),

                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->searchable()
                    ->options(fn (): array => \App\Models\EmailNotificationLog::query()
                        ->whereNotNull('event_type')
                        ->distinct()
                        ->pluck('event_type', 'event_type')
                        ->toArray()),

                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],  fn (Builder $q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn (Builder $q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('')
                    ->tooltip('Retry email failed')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (EmailNotificationLog $record): bool => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (EmailNotificationLog $record): void {
                        $result = app(EmailRetryService::class)->retry($record);

                        $notification = Notification::make()
                            ->title($result['success'] ? 'Retry berhasil' : 'Retry gagal')
                            ->body($result['message']);

                        if ($result['success']) {
                            $notification->success();
                        } else {
                            $notification->danger();
                        }

                        $notification->send();
                    }),
                ViewAction::make()->label('')->tooltip('Lihat detail'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
