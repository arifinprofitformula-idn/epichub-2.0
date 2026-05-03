<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\AffiliateCommissionType;
use App\Enums\ContributorCommissionBase;
use App\Enums\ContributorCommissionType;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /* ── Identitas + Thumbnail (satu section, full-width) ── */
                Section::make('Identitas Produk')
                    ->description('Informasi utama, tipe, visibilitas, dan gambar cover')
                    ->icon('heroicon-o-tag')
                    ->iconColor('primary')
                    ->extraAttributes(['class' => 'fi-prod-section-identity'])
                    ->schema([
                        Grid::make(3)->schema([

                            /* Kiri 2/3: field-field identitas */
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Judul Produk')
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
                                        ->prefix('/')
                                        ->helperText('Digunakan untuk URL produk'),

                                    Select::make('product_category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->nullable(),

                                    Select::make('product_type')
                                        ->label('Tipe Produk')
                                        ->options(collect(ProductType::cases())
                                            ->mapWithKeys(fn (ProductType $t) => [$t->value => $t->label()])
                                            ->all())
                                        ->required()
                                        ->live()
                                        ->native(false),

                                    Select::make('access_type')
                                        ->label('Tipe Akses')
                                        ->options(collect(ProductAccessType::cases())
                                            ->mapWithKeys(fn (ProductAccessType $a) => [$a->value => $a->label()])
                                            ->all())
                                        ->default(ProductAccessType::InstantAccess->value)
                                        ->required()
                                        ->native(false),

                                    Select::make('status')
                                        ->label('Status Publish')
                                        ->options(collect(ProductStatus::cases())
                                            ->mapWithKeys(fn (ProductStatus $s) => [$s->value => $s->label()])
                                            ->all())
                                        ->default(ProductStatus::Draft->value)
                                        ->required()
                                        ->native(false),

                                    Select::make('visibility')
                                        ->label('Visibilitas')
                                        ->options(collect(ProductVisibility::cases())
                                            ->mapWithKeys(fn (ProductVisibility $v) => [$v->value => $v->label()])
                                            ->all())
                                        ->default(ProductVisibility::Public->value)
                                        ->required()
                                        ->native(false),

                                    Toggle::make('is_featured')
                                        ->label('Produk Unggulan')
                                        ->helperText('Tampilkan di bagian unggulan')
                                        ->default(false)
                                        ->columnSpanFull(),
                                ])
                                ->columnSpan(2),

                            /* Kanan 1/3: thumbnail */
                            Grid::make(1)
                                ->schema([
                                    FileUpload::make('thumbnail')
                                        ->label('Thumbnail')
                                        ->disk('public')
                                        ->directory('products/thumbnails')
                                        ->image()
                                        ->imageEditor()
                                        ->nullable()
                                        ->getOpenableFileUrlUsing(function (?string $state): ?string {
                                            if (! filled($state)) {
                                                return null;
                                            }

                                            if (Str::startsWith($state, ['http://', 'https://'])) {
                                                return $state;
                                            }

                                            return asset('storage/'.$state);
                                        }),

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

                /* ── Harga ── */
                Section::make('Harga')
                    ->description('Harga normal dan harga promo')
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('success')
                    ->extraAttributes(['class' => 'fi-prod-section-price'])
                    ->schema([
                        Grid::make(5)->schema([
                            TextInput::make('price')
                                ->label('Harga Normal')
                                ->prefix('Rp')
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->live(onBlur: true),

                            TextInput::make('sale_price')
                                ->label('Harga Promo (opsional)')
                                ->prefix('Rp')
                                ->numeric()
                                ->minValue(0)
                                ->nullable()
                                ->helperText('Harus lebih kecil dari harga normal')
                                ->rules([
                                    fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                        if ($value === null || $value === '') {
                                            return;
                                        }
                                        $price = (float) ($get('price') ?? 0);
                                        if ($price > 0 && (float) $value > $price) {
                                            $fail('Harga promo tidak boleh melebihi harga normal (Rp '.number_format($price, 0, ',', '.').').');
                                        }
                                    },
                                ]),

                            TextInput::make('stock')
                                ->label('Stok (opsional)')
                                ->integer()
                                ->minValue(0)
                                ->nullable()
                                ->helperText('Kosongkan jika tidak terbatas'),

                            TextInput::make('quota')
                                ->label('Kuota (opsional)')
                                ->integer()
                                ->minValue(0)
                                ->nullable(),

                            DateTimePicker::make('publish_at')
                                ->label('Jadwal Publish (opsional)')
                                ->seconds(false)
                                ->nullable(),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Deskripsi ── */
                Section::make('Deskripsi')
                    ->description('Teks singkat dan konten lengkap produk')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('info')
                    ->extraAttributes(['class' => 'fi-prod-section-desc'])
                    ->schema([
                        Textarea::make('short_description')
                            ->label('Deskripsi Singkat')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        RichEditor::make('full_description')
                            ->label('Deskripsi Lengkap')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /* ── Affiliate ── */
                Section::make('Pengaturan Affiliate')
                    ->description('Komisi dan program afiliasi untuk produk ini')
                    ->icon('heroicon-o-users')
                    ->iconColor('warning')
                    ->extraAttributes(['class' => 'fi-prod-section-affiliate'])
                    ->schema([
                        Toggle::make('is_affiliate_enabled')
                            ->label('Aktifkan Affiliate')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            Select::make('affiliate_commission_type')
                                ->label('Tipe Komisi')
                                ->options(collect(AffiliateCommissionType::cases())
                                    ->mapWithKeys(fn (AffiliateCommissionType $t) => [$t->value => $t->label()])
                                    ->all())
                                ->required(fn (Get $get): bool => (bool) $get('is_affiliate_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('is_affiliate_enabled'))
                                ->native(false),

                            TextInput::make('affiliate_commission_value')
                                ->label('Nilai Komisi')
                                ->numeric()
                                ->minValue(0)
                                ->required(fn (Get $get): bool => (bool) $get('is_affiliate_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('is_affiliate_enabled')),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Komisi Kontributor ── */
                Section::make('Komisi Kontributor')
                    ->description('Pengaturan komisi untuk author/kontributor produk ini')
                    ->icon('heroicon-o-academic-cap')
                    ->iconColor('violet')
                    ->extraAttributes(['class' => 'fi-prod-section-contributor'])
                    ->schema([
                        Toggle::make('is_contributor_commission_enabled')
                            ->label('Aktifkan Komisi Kontributor')
                            ->helperText('Komisi kontributor akan dihitung saat pembayaran order berhasil/paid.')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            Select::make('contributor_user_id')
                                ->label('Kontributor / Author')
                                ->options(fn () => User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->required(fn (Get $get): bool => (bool) $get('is_contributor_commission_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('is_contributor_commission_enabled')),

                            Select::make('contributor_commission_type')
                                ->label('Tipe Komisi')
                                ->options(collect(ContributorCommissionType::cases())
                                    ->mapWithKeys(fn (ContributorCommissionType $t) => [$t->value => $t->label()])
                                    ->all())
                                ->native(false)
                                ->required(fn (Get $get): bool => (bool) $get('is_contributor_commission_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('is_contributor_commission_enabled'))
                                ->live(),

                            TextInput::make('contributor_commission_value')
                                ->label(fn (Get $get): string => $get('contributor_commission_type') === ContributorCommissionType::Fixed->value
                                    ? 'Nilai Komisi (Rp)'
                                    : 'Nilai Komisi (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn (Get $get): ?int => $get('contributor_commission_type') === ContributorCommissionType::Percent->value ? 100 : null)
                                ->required(fn (Get $get): bool => (bool) $get('is_contributor_commission_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('is_contributor_commission_enabled'))
                                ->rules([
                                    fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                        if (! (bool) $get('is_contributor_commission_enabled')) {
                                            return;
                                        }
                                        if ($value === null || $value === '') {
                                            return;
                                        }
                                        $type = $get('contributor_commission_type');
                                        if ($type === ContributorCommissionType::Percent->value && (float) $value > 100) {
                                            $fail('Nilai persentase tidak boleh melebihi 100%.');
                                        }
                                        if ((float) $value < 0) {
                                            $fail('Nilai komisi tidak boleh negatif.');
                                        }
                                    },
                                ]),

                            Select::make('contributor_commission_base')
                                ->label('Dasar Perhitungan')
                                ->options(collect(ContributorCommissionBase::cases())
                                    ->mapWithKeys(fn (ContributorCommissionBase $b) => [$b->value => $b->label()])
                                    ->all())
                                ->default(ContributorCommissionBase::Gross->value)
                                ->native(false)
                                ->required(fn (Get $get): bool => (bool) $get('is_contributor_commission_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('is_contributor_commission_enabled')),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Bundle ── */
                Section::make('Produk Bundle')
                    ->description('Daftar produk yang digabungkan dalam bundle ini')
                    ->icon('heroicon-o-rectangle-stack')
                    ->iconColor('primary')
                    ->hidden(fn (Get $get): bool => ($get('product_type') ?? null) !== ProductType::Bundle->value)
                    ->schema([
                        Select::make('bundledProducts')
                            ->label('Produk dalam Bundle')
                            ->multiple()
                            ->relationship('bundledProducts', 'title')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /* ── Landing Page ── */
                Section::make('Landing Page Penawaran')
                    ->description('Halaman penawaran khusus dengan ZIP custom atau meta SEO')
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('info')
                    ->extraAttributes(['class' => 'fi-prod-section-landing'])
                    ->collapsed()
                    ->schema([
                        Toggle::make('landing_page_enabled')
                            ->label('Aktifkan Landing Page')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('landing_page_meta_title')
                                ->label('Meta Title')
                                ->maxLength(255)
                                ->nullable()
                                ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                            TextInput::make('landing_page_preview_url')
                                ->label('Preview URL')
                                ->readOnly()
                                ->dehydrated(false)
                                ->formatStateUsing(fn (?Product $record, Get $get): string => filled($get('slug'))
                                    ? route('offer.show', ['product' => $get('slug')], absolute: true)
                                    : 'Isi slug produk terlebih dahulu.')
                                ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled'))
                                ->suffixAction(
                                    Action::make('open_preview')
                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                        ->tooltip('Buka landing page')
                                        ->url(fn (Get $get): string => filled($get('slug'))
                                            ? route('offer.show', ['product' => $get('slug')], absolute: true)
                                            : '#')
                                        ->openUrlInNewTab()
                                ),

                            Textarea::make('landing_page_meta_description')
                                ->label('Meta Description')
                                ->rows(3)
                                ->nullable()
                                ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled'))
                                ->columnSpanFull(),

                            FileUpload::make('landing_page_zip_path')
                                ->label('ZIP Landing Page')
                                ->disk('local')
                                ->directory('product-landings/zips')
                                ->acceptedFileTypes([
                                    'application/zip',
                                    'application/x-zip-compressed',
                                    'multipart/x-zip',
                                ])
                                ->maxSize(20480)
                                ->downloadable()
                                ->helperText('Upload ZIP berisi index.html dan folder assets. Akan diekstrak otomatis.')
                                ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                            TextInput::make('landing_page_entry_file')
                                ->label('Entry File')
                                ->default('index.html')
                                ->required(fn (Get $get): bool => (bool) $get('landing_page_enabled'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                            Textarea::make('landing_page_shortcode_help')
                                ->label('Shortcode Tersedia')
                                ->rows(6)
                                ->dehydrated(false)
                                ->default("{{product_name}}\n{{product_title}}\n{{product_slug}}\n{{product_type}}\n{{product_price}}\n{{product_sale_price}}\n{{product_effective_price}}\n{{product_short_description}}\n{{product_description}}\n{{checkout_url}}\n{{catalog_url}}\n{{affiliate_name}}\n{{affiliate_code}}\n{{affiliate_store_name}}\n{{affiliate_referral_link}}")
                                ->readOnly()
                                ->columnSpanFull()
                                ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),
                        ]),
                    ])
                    ->columnSpanFull(),

                /* ── Files Produk ── */
                Section::make('File Produk')
                    ->description('File digital yang dikirim ke pembeli setelah pembelian berhasil')
                    ->icon('heroicon-o-paper-clip')
                    ->iconColor('gray')
                    ->collapsed()
                    ->schema([
                        Repeater::make('files')
                            ->label('')
                            ->relationship()
                            ->defaultItems(0)
                            ->orderable('sort_order')
                            ->addActionLabel('+ Tambah File')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul File')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('file_type')
                                    ->label('Tipe File (opsional)')
                                    ->maxLength(50)
                                    ->nullable(),

                                FileUpload::make('file_path')
                                    ->label('Upload File (opsional)')
                                    ->helperText('Disimpan private, hanya akses user yang punya entitlement aktif.')
                                    ->disk('local')
                                    ->directory('products/files')
                                    ->nullable()
                                    ->columnSpanFull(),

                                TextInput::make('external_url')
                                    ->label('External URL (opsional)')
                                    ->url()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpanFull(),

                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /* ── Metadata ── */
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
