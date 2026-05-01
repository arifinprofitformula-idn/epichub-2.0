<?php

namespace App\Filament\Resources\WhatsAppNotificationLogs\Tables;

use App\Models\WhatsAppNotificationLog;
use App\Services\Notifications\WhatsAppRetryService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppNotificationLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('event_type')->label('Event')->badge()->color('gray')->searchable()->placeholder('-'),
                TextColumn::make('recipient_phone')->label('Penerima')->searchable()->copyable(),
                TextColumn::make('recipient_name')->label('Nama')->placeholder('-')->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('sent_at')->label('Terkirim')->dateTime('d M H:i')->placeholder('-')->sortable(),
                TextColumn::make('failed_at')->label('Gagal')->dateTime('d M H:i')->placeholder('-')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('error_message')->label('Error')->limit(60)->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ]),
                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->searchable()
                    ->options(fn (): array => WhatsAppNotificationLog::query()
                        ->whereNotNull('event_type')
                        ->distinct()
                        ->pluck('event_type', 'event_type')
                        ->toArray()),
                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn (Builder $q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('')
                    ->tooltip('Retry WhatsApp failed')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (WhatsAppNotificationLog $record): bool => $record->status === 'failed' && $record->retry_count < 3)
                    ->requiresConfirmation()
                    ->action(function (WhatsAppNotificationLog $record): void {
                        $result = app(WhatsAppRetryService::class)->retry($record);

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
