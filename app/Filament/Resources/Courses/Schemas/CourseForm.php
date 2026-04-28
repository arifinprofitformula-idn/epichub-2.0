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

            /* ── 1. Identitas + Thumbnail ── */
            Section::make('Identitas Kursus')
                ->description('Judul, slug, produk terkait, status, dan gambar cover')
                ->icon('heroicon-o-academic-cap')
                ->iconColor('primary')
                ->extraAttributes(['class' => 'fi-course-section-identity'])
                ->schema([
                    Grid::make(3)->schema([

                        /* Kiri 2/3 */
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Kursus')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                        if (filled($get('slug'))) {
                                            return;
                                        }
                                        $set('slug', Str::slug($state ?? ''));
                                    })
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->prefix('/'),

                                Select::make('product_id')
                                    ->label('Produk Terkait')
                                    ->options(fn () => Product::query()
                                        ->where('product_type', ProductType::Course->value)
                                        ->orderBy('title')
                                        ->pluck('title', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Hubungkan kursus ini ke produk untuk kontrol akses'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(collect(CourseStatus::cases())
                                        ->mapWithKeys(fn (CourseStatus $s) => [$s->value => $s->label()])
                                        ->all())
                                    ->required()
                                    ->default(CourseStatus::Draft->value)
                                    ->native(false),

                                Select::make('difficulty')
                                    ->label('Level Kesulitan')
                                    ->options([
                                        'pemula'    => 'Pemula',
                                        'menengah'  => 'Menengah',
                                        'mahir'     => 'Mahir',
                                    ])
                                    ->nullable()
                                    ->native(false)
                                    ->placeholder('Pilih level'),

                                TextInput::make('estimated_duration_minutes')
                                    ->label('Estimasi Durasi (menit)')
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->suffix('menit'),
                            ])
                            ->columnSpan(2),

                        /* Kanan 1/3 */
                        Grid::make(1)
                            ->schema([
                                FileUpload::make('thumbnail')
                                    ->label('Thumbnail')
                                    ->disk('public')
                                    ->directory('courses/thumbnails')
                                    ->image()
                                    ->imageEditor()
                                    ->nullable(),

                                Toggle::make('is_featured')
                                    ->label('Kursus Unggulan')
                                    ->helperText('Tampilkan di bagian unggulan')
                                    ->default(false),

                                TextInput::make('sort_order')
                                    ->label('Urutan Tampil')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('Angka kecil = tampil lebih awal'),
                            ])
                            ->columnSpan(1),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 2. Deskripsi ── */
            Section::make('Deskripsi')
                ->description('Ringkasan singkat dan konten lengkap kursus')
                ->icon('heroicon-o-document-text')
                ->iconColor('info')
                ->extraAttributes(['class' => 'fi-course-section-desc'])
                ->schema([
                    Textarea::make('short_description')
                        ->label('Deskripsi Singkat')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    RichEditor::make('description')
                        ->label('Deskripsi Lengkap')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            /* ── 3. Akses & Publish ── */
            Section::make('Akses & Jadwal Publish')
                ->description('Aturan akses materi dan kapan kursus tersedia untuk pelajar')
                ->icon('heroicon-o-lock-open')
                ->iconColor('success')
                ->extraAttributes(['class' => 'fi-course-section-access'])
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('lesson_access_mode')
                            ->label('Mode Akses Materi')
                            ->options([
                                'free'       => 'Bebas — Semua materi langsung terbuka',
                                'sequential' => 'Bertahap — Wajib selesaikan materi sebelumnya',
                            ])
                            ->default('free')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('Jadwal Publish')
                            ->timezone('Asia/Jakarta')
                            ->helperText('Kosongkan agar langsung tersedia. Waktu menggunakan zona WIB.')
                            ->seconds(false)
                            ->nullable(),

                        Toggle::make('show_locked_lessons')
                            ->label('Tampilkan Materi Terkunci')
                            ->helperText('Pelajar tetap melihat judul materi yang belum terbuka')
                            ->default(true),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 4. Metadata ── */
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
