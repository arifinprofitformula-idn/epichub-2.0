<?php

namespace App\Filament\Resources\CommissionPayouts\Tables;

use App\Actions\Affiliates\PayAvailableCommissionPayoutAction;
use App\Filament\Resources\EpiChannels\EpiChannelResource;
use App\Models\Commission;
use App\Models\EpiChannel;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use RuntimeException;

class CommissionPayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('available_commission_total_amount', 'desc')
            ->searchPlaceholder('Cari nama, ID EPIC, atau email...')
            ->columns([
                TextColumn::make('epic_code')
                    ->label('ID EPIC')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('latest_payout_number')
                    ->label('ID Payout')
                    ->formatStateUsing(fn (?string $state, EpiChannel $record): string => $state ?: 'AUTO-'.$record->id)
                    ->badge()
                    ->color(fn (?string $state): string => filled($state) ? 'gray' : 'success'),
                ViewColumn::make('member_contact')
                    ->label('Data Member & Kontak')
                    ->view('filament.resources.commission-payouts.columns.member-contact'),
                ViewColumn::make('transfer_destination')
                    ->label('Tujuan Transfer')
                    ->view('filament.resources.commission-payouts.columns.transfer-destination'),
                TextColumn::make('available_commission_total_amount')
                    ->label('Total Saldo')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                ViewColumn::make('payout_input')
                    ->label('Input Payout')
                    ->view('filament.resources.commission-payouts.columns.payout-input'),
            ])
            ->recordActions([
                Action::make('pay_now')
                    ->label('')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->disabled(fn (EpiChannel $record): bool => ! $record->hasCompletePayoutBankInfo() || (float) $record->available_commission_total_amount <= 0)
                    ->tooltip(fn (EpiChannel $record): ?string => $record->hasCompletePayoutBankInfo()
                        ? null
                        : 'Lengkapi data rekening payout terlebih dahulu')
                    ->fillForm(fn (EpiChannel $record): array => [
                        'member_name' => $record->user?->name ?? '-',
                        'epic_code' => $record->epic_code,
                        'available_balance' => 'Rp '.number_format((float) $record->available_commission_total_amount, 0, ',', '.'),
                        'payout_input_amount' => number_format((float) $record->available_commission_total_amount, 2, '.', ''),
                        'transfer_destination' => trim(implode(' | ', array_filter([
                            $record->payout_bank_name,
                            $record->payout_bank_account_number,
                            $record->payout_bank_account_holder_name,
                        ]))),
                        'notes' => null,
                    ])
                    ->form([
                        TextInput::make('member_name')
                            ->label('Nama Member')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('epic_code')
                            ->label('ID EPIC')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('available_balance')
                            ->label('Total Saldo Tersedia')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('transfer_destination')
                            ->label('Tujuan Transfer')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('payout_input_amount')
                            ->label('Nominal Payout')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->helperText('Partial payout belum diaktifkan agar proses tetap aman dan mudah diaudit.'),
                        Textarea::make('notes')
                            ->label('Catatan Admin')
                            ->rows(3)
                            ->placeholder('Opsional. Misalnya referensi transfer atau catatan proses payout.'),
                    ])
                    ->modalHeading('Konfirmasi Pay Now')
                    ->modalDescription('Payout akan langsung dibuat sebagai paid dan seluruh komisi eligible pada channel ini akan ditandai sudah dibayar.')
                    ->requiresConfirmation()
                    ->action(function (EpiChannel $record, array $data): void {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new RuntimeException('Unauthorized.');
                        }

                        $payout = app(PayAvailableCommissionPayoutAction::class)->execute(
                            channel: $record,
                            actor: $actor,
                            requestedAmount: (float) ($data['payout_input_amount'] ?? 0),
                            notes: filled($data['notes'] ?? null) ? (string) $data['notes'] : null,
                        );

                        Notification::make()
                            ->success()
                            ->title('Payout berhasil diproses')
                            ->body('Payout '.$payout->payout_number.' berhasil dibuat dan komisi telah ditandai paid.')
                            ->send();
                    }),
                Action::make('view_commissions')
                    ->label('')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalHeading(fn (EpiChannel $record): string => 'Komisi Eligible - '.$record->epic_code)
                    ->modalContent(fn (EpiChannel $record) => view('filament.resources.commission-payouts.actions.commission-details', [
                        'record' => $record,
                        'commissions' => Commission::query()
                            ->where('epi_channel_id', $record->id)
                            ->eligibleForPayout()
                            ->with(['order', 'product', 'buyer'])
                            ->orderBy('approved_at')
                            ->orderBy('created_at')
                            ->orderBy('id')
                            ->get(),
                    ])),
                Action::make('edit_profile')
                    ->label('')
                    ->icon(fn (EpiChannel $record): string => $record->hasCompletePayoutBankInfo() ? 'heroicon-o-user-circle' : 'heroicon-o-exclamation-triangle')
                    ->color(fn (EpiChannel $record): string => $record->hasCompletePayoutBankInfo() ? 'info' : 'warning')
                    ->url(fn (EpiChannel $record): string => EpiChannelResource::getUrl('edit', ['record' => $record])),
            ])
            ->actionsColumnLabel('Aksi')
            ->emptyStateHeading('Tidak ada payout yang tersedia.')
            ->emptyStateDescription('Semua komisi eligible sudah dibayarkan atau belum ada komisi approved yang siap dipayout.')
            ->paginated([10, 25, 50]);
    }
}
