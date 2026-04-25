<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use App\Enums\EventRegistrationStatus;
use App\Models\Event;
use App\Models\User;
use App\Models\UserProduct;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Registrasi')->schema([
                    Select::make('event_id')
                        ->label('Event')
                        ->options(fn () => Event::query()->orderBy('starts_at')->orderBy('title')->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('user_id')
                        ->label('User')
                        ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('user_product_id')
                        ->label('User Product (opsional)')
                        ->options(fn () => UserProduct::query()->latest('granted_at')->limit(100)->pluck('id', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(EventRegistrationStatus::cases())->mapWithKeys(fn (EventRegistrationStatus $s) => [$s->value => $s->label()])->all())
                        ->required()
                        ->default(EventRegistrationStatus::Registered->value),

                    DateTimePicker::make('registered_at')
                        ->label('Registered at (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    DateTimePicker::make('attended_at')
                        ->label('Attended at (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    Textarea::make('notes')
                        ->label('Notes (opsional)')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),
                ])->columnSpanFull(),
            ]),
        ]);
    }
}

