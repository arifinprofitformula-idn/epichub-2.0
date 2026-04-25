<?php

namespace App\Filament\Resources\CourseLessons\Schemas;

use App\Enums\CourseLessonType;
use App\Models\Course;
use App\Models\CourseSection;
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

class CourseLessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Lesson')->schema([
                    Select::make('course_id')
                        ->label('Course')
                        ->options(fn () => Course::query()->orderBy('title')->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),

                    Select::make('course_section_id')
                        ->label('Section (opsional)')
                        ->options(fn (Get $get) => CourseSection::query()
                            ->where('course_id', $get('course_id'))
                            ->orderBy('sort_order')
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
                        ->maxLength(255),

                    Select::make('lesson_type')
                        ->label('Tipe lesson')
                        ->options(collect(CourseLessonType::cases())->mapWithKeys(fn (CourseLessonType $t) => [$t->value => $t->label()])->all())
                        ->required()
                        ->default(CourseLessonType::Article->value)
                        ->live(),

                    Textarea::make('short_description')
                        ->label('Deskripsi singkat (opsional)')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    RichEditor::make('content')
                        ->label('Konten (artikel)')
                        ->nullable()
                        ->hidden(fn (Get $get): bool => ($get('lesson_type') ?? null) !== CourseLessonType::Article->value)
                        ->columnSpanFull(),

                    TextInput::make('video_url')
                        ->label('Video URL (embed)')
                        ->url()
                        ->maxLength(255)
                        ->nullable()
                        ->hidden(fn (Get $get): bool => ($get('lesson_type') ?? null) !== CourseLessonType::VideoEmbed->value),

                    FileUpload::make('attachment_path')
                        ->label('Attachment (private)')
                        ->helperText('File attachment disimpan private dan hanya bisa diunduh oleh user yang memiliki entitlement aktif.')
                        ->disk('local')
                        ->directory('courses/attachments')
                        ->nullable()
                        ->hidden(fn (Get $get): bool => ($get('lesson_type') ?? null) !== CourseLessonType::FileAttachment->value),

                    TextInput::make('external_url')
                        ->label('External URL')
                        ->url()
                        ->maxLength(255)
                        ->nullable()
                        ->hidden(fn (Get $get): bool => ($get('lesson_type') ?? null) !== CourseLessonType::ExternalLink->value),

                    TextInput::make('duration_minutes')
                        ->label('Durasi (menit, opsional)')
                        ->integer()
                        ->minValue(0)
                        ->nullable(),

                    TextInput::make('sort_order')
                        ->label('Urutan')
                        ->integer()
                        ->minValue(0)
                        ->default(0),

                    Toggle::make('is_preview')
                        ->label('Preview')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

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
                ])->columnSpanFull(),
            ]),
        ]);
    }
}

