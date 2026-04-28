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

            /* ── 1. Identitas Event + Banner ── */
            Section::make('Identitas Event')
                ->description('Judul, slug, produk terkait, status, dan gambar banner')
                ->icon('heroicon-o-calendar-days')
                ->iconColor('primary')
                ->extraAttributes(['class' => 'fi-event-section-identity'])
                ->schema([
                    Grid::make(3)->schema([

                        /* Kiri 2/3 */
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Event')
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
                                        ->where('product_type', ProductType::Event->value)
                                        ->orderBy('title')
                                        ->pluck('title', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Hubungkan ke produk untuk kontrol akses & pembayaran'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(collect(EventStatus::cases())
                                        ->mapWithKeys(fn (EventStatus $s) => [$s->value => $s->label()])
                                        ->all())
                                    ->required()
                                    ->default(EventStatus::Draft->value)
                                    ->native(false),

                                TextInput::make('quota')
                                    ->label('Kuota Peserta (opsional)')
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->helperText('Kosongkan jika tidak terbatas'),

                                TextInput::make('sort_order')
                                    ->label('Urutan Tampil')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('Angka kecil = tampil lebih awal'),

                                Toggle::make('is_featured')
                                    ->label('Event Unggulan')
                                    ->helperText('Tampilkan di bagian unggulan')
                                    ->default(false)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(2),

                        /* Kanan 1/3 */
                        Grid::make(1)
                            ->schema([
                                FileUpload::make('banner')
                                    ->label('Banner Event')
                                    ->disk('public')
                                    ->directory('events/banners')
                                    ->image()
                                    ->imageEditor()
                                    ->nullable(),
                            ])
                            ->columnSpan(1),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 2. Jadwal & Lokasi ── */
            Section::make('Jadwal & Waktu')
                ->description('Tanggal, jam mulai-selesai, dan zona waktu event')
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->extraAttributes(['class' => 'fi-event-section-schedule'])
                ->schema([
                    Grid::make(3)->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Mulai')
                            ->seconds(false)
                            ->nullable(),

                        DateTimePicker::make('ends_at')
                            ->label('Selesai')
                            ->seconds(false)
                            ->nullable()
                            ->after('starts_at'),

                        Select::make('timezone')
                            ->label('Zona Waktu')
                            ->options([
                                'Asia/Jakarta'    => 'WIB — Asia/Jakarta',
                                'Asia/Makassar'   => 'WITA — Asia/Makassar',
                                'Asia/Jayapura'   => 'WIT — Asia/Jayapura',
                                'UTC'             => 'UTC',
                            ])
                            ->default('Asia/Jakarta')
                            ->native(false)
                            ->searchable(),

                        DateTimePicker::make('published_at')
                            ->label('Jadwal Publish (opsional)')
                            ->seconds(false)
                            ->nullable()
                            ->helperText('Kosongkan agar langsung tersedia'),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 3. Speaker ── */
            Section::make('Informasi Speaker')
                ->description('Nama, jabatan, dan bio pembicara event')
                ->icon('heroicon-o-user-circle')
                ->iconColor('info')
                ->extraAttributes(['class' => 'fi-event-section-speaker'])
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('speaker_name')
                            ->label('Nama Speaker')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('speaker_title')
                            ->label('Jabatan / Title Speaker')
                            ->maxLength(255)
                            ->nullable(),

                        Textarea::make('speaker_bio')
                            ->label('Bio Speaker')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 4. Deskripsi ── */
            Section::make('Deskripsi Event')
                ->description('Konten lengkap dan ringkasan event untuk halaman publik')
                ->icon('heroicon-o-document-text')
                ->iconColor('success')
                ->extraAttributes(['class' => 'fi-event-section-desc'])
                ->schema([
                    RichEditor::make('description')
                        ->label('')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            /* ── 5. Link Zoom & Replay (collapsed) ── */
            Section::make('Link Zoom & Replay')
                ->description('Informasi akses meeting dan rekaman setelah event')
                ->icon('heroicon-o-video-camera')
                ->iconColor('info')
                ->extraAttributes(['class' => 'fi-event-section-zoom'])
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        Textarea::make('zoom_url')
                            ->label('Zoom URL')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('zoom_meeting_id')
                            ->label('Meeting ID')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('zoom_passcode')
                            ->label('Passcode')
                            ->maxLength(255)
                            ->nullable(),

                        Textarea::make('replay_url')
                            ->label('URL Replay / Rekaman')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 6. Metadata (collapsed) ── */
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
