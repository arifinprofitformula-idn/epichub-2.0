<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use App\Enums\ProductType;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Informasi Utama')->schema([
                    Select::make('product_id')
                        ->label('Product (event)')
                        ->options(fn () => Product::query()
                            ->where('product_type', ProductType::Event->value)
                            ->orderBy('title')
                            ->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                            if (filled($get('slug'))) {
                                return;
                            }

                            $set('slug', Str::slug($state ?? ''));
                        }),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    RichEditor::make('description')
                        ->label('Deskripsi (opsional)')
                        ->nullable()
                        ->columnSpanFull(),

                    FileUpload::make('banner')
                        ->label('Banner (opsional)')
                        ->disk('public')
                        ->directory('events/banners')
                        ->image()
                        ->imageEditor()
                        ->nullable(),
                ])->columnSpan(1),

                Section::make('Speaker & Jadwal')->schema([
                    TextInput::make('speaker_name')
                        ->label('Nama speaker (opsional)')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('speaker_title')
                        ->label('Title speaker (opsional)')
                        ->maxLength(255)
                        ->nullable(),

                    Textarea::make('speaker_bio')
                        ->label('Bio speaker (opsional)')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    DateTimePicker::make('starts_at')
                        ->label('Mulai (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    DateTimePicker::make('ends_at')
                        ->label('Selesai (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    TextInput::make('timezone')
                        ->label('Timezone')
                        ->maxLength(60)
                        ->default('Asia/Jakarta'),

                    TextInput::make('quota')
                        ->label('Kuota (opsional)')
                        ->integer()
                        ->minValue(0)
                        ->nullable(),
                ])->columnSpan(1),

                Section::make('Zoom & Replay')->schema([
                    Textarea::make('zoom_url')
                        ->label('Zoom URL (opsional)')
                        ->rows(2)
                        ->nullable(),

                    TextInput::make('zoom_meeting_id')
                        ->label('Zoom Meeting ID (opsional)')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('zoom_passcode')
                        ->label('Zoom Passcode (opsional)')
                        ->maxLength(255)
                        ->nullable(),

                    Textarea::make('replay_url')
                        ->label('Replay URL (opsional)')
                        ->rows(2)
                        ->nullable(),
                ])->columnSpan(1),

                Section::make('Pengaturan')->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(collect(EventStatus::cases())->mapWithKeys(fn (EventStatus $s) => [$s->value => $s->label()])->all())
                        ->required()
                        ->default(EventStatus::Draft->value),

                    Toggle::make('is_featured')
                        ->label('Featured')
                        ->default(false),

                    TextInput::make('sort_order')
                        ->label('Urutan')
                        ->integer()
                        ->minValue(0)
                        ->default(0),

                    DateTimePicker::make('published_at')
                        ->label('Published pada (opsional)')
                        ->seconds(false)
                        ->nullable(),

                    KeyValue::make('metadata')
                        ->label('Metadata (opsional)')
                        ->addButtonLabel('Tambah item')
                        ->keyLabel('Key')
                        ->valueLabel('Value')
                        ->nullable()
                        ->columnSpanFull(),
                ])->columnSpan(1),
            ])->columns(2),
        ]);
    }
}

