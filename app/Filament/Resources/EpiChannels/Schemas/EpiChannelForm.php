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
            Grid::make(2)->schema([
                Section::make('EPI Channel')->schema([
                    Select::make('user_id')
                        ->label('User')
                        ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('epic_code')
                        ->label('EPIC Code')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    TextInput::make('store_name')
                        ->label('Nama store (opsional)')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('sponsor_epic_code')
                        ->label('Sponsor EPIC (opsional)')
                        ->maxLength(50)
                        ->nullable(),

                    TextInput::make('sponsor_name')
                        ->label('Nama sponsor (opsional)')
                        ->maxLength(255)
                        ->nullable(),

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(EpiChannelStatus::cases())->mapWithKeys(fn (EpiChannelStatus $s) => [$s->value => $s->label()])->all())
                        ->required()
                        ->default(EpiChannelStatus::Active->value),

                    TextInput::make('source')
                        ->label('Source')
                        ->maxLength(20)
                        ->default('manual'),

                    DateTimePicker::make('activated_at')
                        ->label('Activated at (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    DateTimePicker::make('suspended_at')
                        ->label('Suspended at (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    KeyValue::make('metadata')
                        ->label('Metadata (opsional)')
                        ->nullable()
                        ->columnSpanFull(),
                ])->columnSpanFull(),
            ]),
        ]);
    }
}

