<?php

namespace App\Filament\Resources\EpiChannels\Schemas;

use App\Enums\EpiChannelStatus;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

            /* ── 2. Sponsor & Sumber ── */
            Section::make('Sponsor & Sumber')
                ->description('Informasi sponsor upline dan asal pendaftaran channel')
                ->icon('heroicon-o-user-group')
                ->iconColor('warning')
                ->extraAttributes(['class' => 'fi-epic-section-sponsor'])
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('sponsor_epic_code')
                            ->label('EPIC Code Sponsor')
                            ->maxLength(50)
                            ->nullable()
                            ->helperText('EPIC Code dari upline / sponsor yang mereferensikan'),

                        TextInput::make('sponsor_name')
                            ->label('Nama Sponsor')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('source')
                            ->label('Sumber Registrasi')
                            ->maxLength(20)
                            ->default('manual')
                            ->helperText('Contoh: manual, import, referral'),
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
