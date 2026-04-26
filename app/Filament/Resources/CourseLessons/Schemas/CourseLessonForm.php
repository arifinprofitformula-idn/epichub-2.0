<?php

namespace App\Filament\Resources\CourseLessons\Schemas;

use App\Enums\CourseLessonType;
use App\Models\CourseLessonAttachment;
use App\Models\Course;
use App\Models\CourseSection;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseLessonForm
{
    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function getComponents(array $options = []): array
    {
        $courseSelectable = $options['course_selectable'] ?? true;
        $courseId = $options['course_id'] ?? null;
        $sectionSelectable = $options['section_selectable'] ?? true;
        $sectionId = $options['section_id'] ?? null;

        $courseField = $courseSelectable
            ? Select::make('course_id')
                ->label('Course')
                ->options(fn () => Course::query()->orderBy('title')->pluck('title', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->live()
            : Hidden::make('course_id')
                ->default($courseId)
                ->required()
                ->dehydrated();

        $sectionField = $sectionSelectable
            ? Select::make('course_section_id')
                ->label('Section (opsional)')
                ->options(fn (Get $get) => CourseSection::query()
                    ->where('course_id', $courseId ?? $get('course_id'))
                    ->orderBy('sort_order')
                    ->pluck('title', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
            : Hidden::make('course_section_id')
                ->default($sectionId)
                ->dehydrated();

        return [
            Grid::make(2)->schema([
                Section::make('Lesson')->schema([
                    $courseField,
                    $sectionField,

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
                        ->label('Video URL')
                        ->helperText('Bisa gunakan link YouTube, Vimeo, Loom, atau URL embed langsung.')
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
                        ->timezone('Asia/Jakarta')
                        ->helperText('Kosongkan jika materi harus langsung tersedia. Waktu input menggunakan zona Asia/Jakarta.')
                        ->seconds(false)
                        ->nullable(),

                    KeyValue::make('metadata')
                        ->label('Metadata (opsional)')
                        ->addButtonLabel('Tambah item')
                        ->keyLabel('Key')
                        ->valueLabel('Value')
                        ->nullable()
                        ->columnSpanFull(),

                    Repeater::make('attachments')
                        ->label('Resource Materi')
                        ->relationship('attachments')
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->addActionLabel('Tambah Resource Materi')
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->schema([
                            Select::make('source_type')
                                ->label('Sumber Resource')
                                ->options([
                                    CourseLessonAttachment::SOURCE_UPLOAD => 'Upload File',
                                    CourseLessonAttachment::SOURCE_EXTERNAL_URL => 'Link Eksternal',
                                ])
                                ->default(CourseLessonAttachment::SOURCE_UPLOAD)
                                ->required()
                                ->live(),

                            TextInput::make('title')
                                ->label('Judul Resource')
                                ->required()
                                ->maxLength(255),

                            Textarea::make('description')
                                ->label('Deskripsi Resource')
                                ->rows(2)
                                ->nullable()
                                ->columnSpanFull(),

                            FileUpload::make('file_path')
                                ->label('Upload File')
                                ->disk('local')
                                ->directory('courses/lesson-attachments')
                                ->storeFileNamesIn('original_name')
                                ->downloadable()
                                ->openable()
                                ->required(fn (Get $get): bool => ($get('source_type') ?? CourseLessonAttachment::SOURCE_UPLOAD) === CourseLessonAttachment::SOURCE_UPLOAD)
                                ->visible(fn (Get $get): bool => ($get('source_type') ?? CourseLessonAttachment::SOURCE_UPLOAD) === CourseLessonAttachment::SOURCE_UPLOAD)
                                ->columnSpanFull()
                                ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                    if (! is_string($state) || blank($state)) {
                                        return;
                                    }

                                    $disk = $get('disk') ?: 'local';

                                    if (! Storage::disk($disk)->exists($state)) {
                                        return;
                                    }

                                    $set('mime_type', Storage::disk($disk)->mimeType($state));
                                    $set('size', Storage::disk($disk)->size($state));
                                }),

                            TextInput::make('external_url')
                                ->label('Link Eksternal')
                                ->placeholder('https://...')
                                ->required(fn (Get $get): bool => ($get('source_type') ?? null) === CourseLessonAttachment::SOURCE_EXTERNAL_URL)
                                ->visible(fn (Get $get): bool => ($get('source_type') ?? null) === CourseLessonAttachment::SOURCE_EXTERNAL_URL)
                                ->url()
                                ->rule(function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                        if (($get('source_type') ?? null) !== CourseLessonAttachment::SOURCE_EXTERNAL_URL) {
                                            return;
                                        }

                                        if (! is_string($value) || blank($value)) {
                                            $fail('Link eksternal wajib diisi.');

                                            return;
                                        }

                                        if (! filter_var($value, FILTER_VALIDATE_URL)) {
                                            $fail('Link eksternal harus berupa URL yang valid.');

                                            return;
                                        }

                                        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

                                        if (! in_array($scheme, ['http', 'https'], true)) {
                                            $fail('Link eksternal hanya boleh menggunakan skema http atau https.');
                                        }
                                    };
                                })
                                ->helperText('Disarankan menggunakan URL https.')
                                ->columnSpanFull(),

                            TextInput::make('button_label')
                                ->label('Label Tombol')
                                ->nullable()
                                ->maxLength(255),

                            Toggle::make('open_in_new_tab')
                                ->label('Buka di Tab Baru')
                                ->default(true)
                                ->visible(fn (Get $get): bool => ($get('source_type') ?? null) === CourseLessonAttachment::SOURCE_EXTERNAL_URL),

                            Toggle::make('is_downloadable')
                                ->label('Tampilkan di Halaman Materi')
                                ->default(true),

                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true),

                            TextInput::make('sort_order')
                                ->label('Urutan')
                                ->integer()
                                ->minValue(0)
                                ->default(0),

                            Hidden::make('disk')
                                ->default('local')
                                ->dehydrated(),

                            Hidden::make('original_name')
                                ->dehydrated(),

                            Hidden::make('mime_type')
                                ->dehydrated(),

                            Hidden::make('size')
                                ->dehydrated(),
                        ])
                        ->columns(2)
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $sourceType = $data['source_type'] ?? CourseLessonAttachment::SOURCE_UPLOAD;

                            if ($sourceType === CourseLessonAttachment::SOURCE_EXTERNAL_URL) {
                                $data['file_path'] = null;
                                $data['original_name'] = null;
                                $data['mime_type'] = null;
                                $data['size'] = null;
                            } else {
                                $data['external_url'] = null;
                                $data['open_in_new_tab'] = false;
                            }

                            return $data;
                        })
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                            $sourceType = $data['source_type'] ?? CourseLessonAttachment::SOURCE_UPLOAD;

                            if ($sourceType === CourseLessonAttachment::SOURCE_EXTERNAL_URL) {
                                $data['file_path'] = null;
                                $data['original_name'] = null;
                                $data['mime_type'] = null;
                                $data['size'] = null;
                            } else {
                                $data['external_url'] = null;
                                $data['open_in_new_tab'] = false;
                            }

                            return $data;
                        })
                        ->columnSpanFull(),
                ])->columnSpanFull(),
            ]),
        ];
    }

    public static function configure(Schema $schema, array $options = []): Schema
    {
        return $schema->components(static::getComponents($options));
    }
}

