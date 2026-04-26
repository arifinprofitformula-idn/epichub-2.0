<?php

namespace App\Filament\Resources\Users\Schemas;

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
            Grid::make(2)->schema([
                Section::make('Profil')
                    ->schema([
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
                            ->nullable(),
                    ])
                    ->columnSpan(1),

                Section::make('Akun')
                    ->schema([
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
                    ])
                    ->columnSpan(1),
            ]),
        ]);
    }
}
