<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profil Pengguna')
                ->description('Kelola identitas utama dan informasi akun pengguna dalam satu form yang utuh.')
                ->icon('heroicon-o-user')
                ->iconColor('primary')
                ->extraAttributes(['class' => 'fi-user-section-profile'])
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'xl' => 2,
                    ])->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(30)
                            ->nullable()
                            ->mutateStateForValidationUsing(fn (?string $state): ?string => app(NormalizeWhatsappNumberAction::class)->execute($state))
                            ->dehydrateStateUsing(fn (?string $state): ?string => app(NormalizeWhatsappNumberAction::class)->execute($state))
                            ->unique(ignoreRecord: true),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Diverifikasi Pada')
                            ->displayFormat('d M Y H:i')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('referral_source')
                            ->label('Sumber Referral')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('referrerEpiChannel.epic_code')
                            ->label('Referrer Terkunci')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
