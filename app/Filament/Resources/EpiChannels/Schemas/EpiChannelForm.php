<?php

namespace App\Filament\Resources\EpiChannels\Schemas;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EpiChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* ── 1. Identitas Channel ── */
            Section::make('Identitas EPI Channel')
                ->description('Data utama channel, pemilik, dan status aktivasi')
                ->icon('heroicon-o-building-storefront')
                ->iconColor('primary')
                ->extraAttributes(['class' => 'fi-epic-section-identity'])
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('user_id')
                            ->label('Pemilik (User)')
                            ->options(fn () => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [
                                    $u->id => $u->name.' — '.$u->email,
                                ]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        TextInput::make('epic_code')
                            ->label('EPIC Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Kode unik channel, digunakan sebagai referral code'),

                        TextInput::make('store_name')
                            ->label('Nama Store (opsional)')
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('Nama toko atau brand afiliasi'),

                        Select::make('status')
                            ->label('Status')
                            ->options(collect(EpiChannelStatus::cases())
                                ->mapWithKeys(fn (EpiChannelStatus $s) => [$s->value => $s->label()])
                                ->all())
                            ->required()
                            ->default(EpiChannelStatus::Active->value)
                            ->native(false),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 2. Pereferral ── */
            Section::make('Pereferral')
                ->description('Pilih channel pereferral yang mereferensikan pendaftaran ini')
                ->icon('heroicon-o-user-group')
                ->iconColor('warning')
                ->extraAttributes(['class' => 'fi-epic-section-sponsor'])
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('sponsor_epic_code')
                            ->label('Pereferral')
                            ->placeholder('Cari nama atau ID EPIC pereferral…')
                            ->helperText('Ketik nama atau ID EPIC untuk mencari pereferral dari data yang sudah ada')
                            ->searchable()
                            ->nullable()
                            ->native(false)
                            ->columnSpan(1)
                            ->getSearchResultsUsing(function (string $search): array {
                                return EpiChannel::query()
                                    ->where(function ($q) use ($search): void {
                                        $q->where('epic_code', 'like', "%{$search}%")
                                            ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                                            ->orWhere('store_name', 'like', "%{$search}%");
                                    })
                                    ->with('user')
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn (EpiChannel $c): array => [
                                        $c->epic_code => $c->epic_code.' — '.($c->user?->name ?? $c->store_name ?? '-'),
                                    ])
                                    ->all();
                            })
                            ->getOptionLabelUsing(fn (?string $value): ?string => $value
                                ? (function () use ($value): string {
                                    $c = EpiChannel::query()->with('user')->where('epic_code', $value)->first();

                                    return $c ? $c->epic_code.' — '.($c->user?->name ?? $c->store_name ?? '-') : $value;
                                })()
                                : null
                            )
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (! $state) {
                                    $set('sponsor_name', null);

                                    return;
                                }

                                $channel = EpiChannel::query()->with('user')->where('epic_code', $state)->first();
                                $set('sponsor_name', $channel?->user?->name ?? $channel?->store_name);
                            })
                            ->live(),

                        TextInput::make('sponsor_name')
                            ->label('Nama Pereferral')
                            ->maxLength(255)
                            ->nullable()
                            ->readOnly()
                            ->placeholder('Terisi otomatis setelah memilih pereferral')
                            ->helperText('Diisi otomatis berdasarkan pilihan pereferral di atas')
                            ->columnSpan(1),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 3. Tanggal Aktivasi (collapsed) ── */
            Section::make('Riwayat Aktivasi')
                ->description('Tanggal channel diaktifkan atau disuspend')
                ->icon('heroicon-o-calendar-days')
                ->iconColor('info')
                ->extraAttributes(['class' => 'fi-epic-section-dates'])
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        DateTimePicker::make('activated_at')
                            ->label('Tanggal Aktivasi')
                            ->seconds(false)
                            ->nullable()
                            ->helperText('Kosongkan jika langsung aktif saat dibuat'),

                        DateTimePicker::make('suspended_at')
                            ->label('Tanggal Suspensi')
                            ->seconds(false)
                            ->nullable()
                            ->helperText('Isi saat channel perlu dibekukan'),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 4. Metadata (collapsed) ── */
            Section::make('Metadata (opsional)')
                ->description('Key-value tambahan untuk kebutuhan integrasi atau kustomisasi')
                ->icon('heroicon-o-code-bracket')
                ->iconColor('gray')
                ->collapsed()
                ->schema([
                    KeyValue::make('metadata')
                        ->label('')
                        ->addButtonLabel('+ Tambah Item')
                        ->keyLabel('Key')
                        ->valueLabel('Value')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }
}
