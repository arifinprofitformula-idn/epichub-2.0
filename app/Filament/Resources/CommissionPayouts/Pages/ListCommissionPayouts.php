<?php

namespace App\Filament\Resources\CommissionPayouts\Pages;

use App\Actions\Affiliates\CreateCommissionPayoutAction;
use App\Enums\CommissionStatus;
use App\Filament\Resources\CommissionPayouts\CommissionPayoutResource;
use App\Models\Commission;
use App\Models\EpiChannel;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ListRecords;

class ListCommissionPayouts extends ListRecords
{
    protected static string $resource = CommissionPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_payout')
                ->label('Create Payout')
                ->color('primary')
                ->form([
                    Select::make('epi_channel_id')
                        ->label('EPI Channel')
                        ->options(fn (): array => EpiChannel::query()->orderBy('epic_code')->pluck('epic_code', 'id')->all())
                        ->searchable()
                        ->live()
                        ->required(),
                    Select::make('commission_ids')
                        ->label('Approved Commissions')
                        ->multiple()
                        ->options(function (callable $get): array {
                            $channelId = (int) ($get('epi_channel_id') ?? 0);

                            if ($channelId <= 0) {
                                return [];
                            }

                            return Commission::query()
                                ->where('epi_channel_id', $channelId)
                                ->where('status', CommissionStatus::Approved)
                                ->whereNull('commission_payout_id')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Commission $commission) => [
                                    $commission->id => sprintf(
                                        '#%d | %s | Rp %s',
                                        $commission->id,
                                        $commission->order?->order_number ?? '-',
                                        number_format((float) $commission->commission_amount, 0, ',', '.')
                                    ),
                                ])
                                ->all();
                        })
                        ->searchable()
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $actor = auth()->user();

                    if (! $actor instanceof User) {
                        throw new \RuntimeException('Unauthorized.');
                    }

                    $channel = EpiChannel::query()->findOrFail((int) $data['epi_channel_id']);
                    $commissionIds = array_map('intval', (array) ($data['commission_ids'] ?? []));

                    app(CreateCommissionPayoutAction::class)->execute(
                        channel: $channel,
                        actor: $actor,
                        commissionIds: $commissionIds,
                        notes: isset($data['notes']) ? (string) $data['notes'] : null,
                    );
                }),
        ];
    }
}
