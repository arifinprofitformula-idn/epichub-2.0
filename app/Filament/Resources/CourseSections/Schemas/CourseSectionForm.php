<?php

namespace App\Filament\Resources\CourseSections\Schemas;

use App\Models\Course;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Section')->schema([
                    Select::make('course_id')
                        ->label('Course')
                        ->options(fn () => Course::query()->orderBy('title')->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('Deskripsi (opsional)')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    TextInput::make('sort_order')
                        ->label('Urutan')
                        ->integer()
                        ->minValue(0)
                        ->default(0),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columnSpanFull(),
            ]),
        ]);
    }
}

