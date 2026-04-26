<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Enums\CourseStatus;
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

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Informasi Utama')->schema([
                    Select::make('product_id')
                        ->label('Product (course)')
                        ->options(fn () => Product::query()
                            ->where('product_type', ProductType::Course->value)
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

                    Textarea::make('short_description')
                        ->label('Deskripsi singkat (opsional)')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    RichEditor::make('description')
                        ->label('Deskripsi (opsional)')
                        ->nullable()
                        ->columnSpanFull(),
                ])->columnSpan(1),

                Section::make('Pengaturan')->schema([
                    FileUpload::make('thumbnail')
                        ->label('Thumbnail (opsional)')
                        ->disk('public')
                        ->directory('courses/thumbnails')
                        ->image()
                        ->imageEditor()
                        ->nullable(),

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(CourseStatus::cases())->mapWithKeys(fn (CourseStatus $s) => [$s->value => $s->label()])->all())
                        ->required()
                        ->default(CourseStatus::Draft->value),

                    TextInput::make('difficulty')
                        ->label('Difficulty (opsional)')
                        ->maxLength(50)
                        ->nullable(),

                    TextInput::make('estimated_duration_minutes')
                        ->label('Durasi estimasi (menit)')
                        ->integer()
                        ->minValue(0)
                        ->nullable(),

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
                        ->timezone('Asia/Jakarta')
                        ->helperText('Kosongkan jika kelas harus langsung tersedia. Waktu input menggunakan zona Asia/Jakarta.')
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

