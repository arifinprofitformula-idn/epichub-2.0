<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\AffiliateCommissionType;
use App\Enums\ProductAccessType;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Informasi Utama')
                            ->schema([
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

                                Select::make('product_category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make('product_type')
                                    ->label('Tipe Produk')
                                    ->options(collect(ProductType::cases())->mapWithKeys(fn (ProductType $type) => [$type->value => $type->label()])->all())
                                    ->required()
                                    ->live(),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status) => [$status->value => $status->label()])->all())
                                    ->default(ProductStatus::Draft->value)
                                    ->required(),

                                Select::make('visibility')
                                    ->label('Visibilitas')
                                    ->options(collect(ProductVisibility::cases())->mapWithKeys(fn (ProductVisibility $visibility) => [$visibility->value => $visibility->label()])->all())
                                    ->default(ProductVisibility::Public->value)
                                    ->required(),

                                Select::make('access_type')
                                    ->label('Tipe Akses')
                                    ->options(collect(ProductAccessType::cases())->mapWithKeys(fn (ProductAccessType $accessType) => [$accessType->value => $accessType->label()])->all())
                                    ->default(ProductAccessType::InstantAccess->value)
                                    ->required(),

                                Toggle::make('is_featured')
                                    ->label('Produk unggulan')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->columnSpan(1),

                        Section::make('Media & Urutan')
                            ->schema([
                                FileUpload::make('thumbnail')
                                    ->label('Thumbnail')
                                    ->disk('public')
                                    ->directory('products/thumbnails')
                                    ->image()
                                    ->imageEditor()
                                    ->nullable(),

                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0),
                            ])
                            ->columnSpan(1),

                        Section::make('Deskripsi')
                            ->schema([
                                Textarea::make('short_description')
                                    ->label('Deskripsi singkat')
                                    ->rows(3)
                                    ->nullable()
                                    ->columnSpanFull(),

                                RichEditor::make('full_description')
                                    ->label('Deskripsi lengkap')
                                    ->nullable()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Harga')
                            ->schema([
                                TextInput::make('price')
                                    ->label('Harga')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),

                                TextInput::make('sale_price')
                                    ->label('Harga promo (opsional)')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->rule('lte:price')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Stock / Quota / Publish')
                            ->schema([
                                TextInput::make('stock')
                                    ->label('Stock (opsional)')
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable(),

                                TextInput::make('quota')
                                    ->label('Quota (opsional)')
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable(),

                                DateTimePicker::make('publish_at')
                                    ->label('Publish pada (opsional)')
                                    ->seconds(false)
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),

                        Section::make('Affiliate')
                            ->schema([
                                Toggle::make('is_affiliate_enabled')
                                    ->label('Aktifkan affiliate')
                                    ->default(false)
                                    ->live(),

                                Select::make('affiliate_commission_type')
                                    ->label('Tipe komisi')
                                    ->options(collect(AffiliateCommissionType::cases())->mapWithKeys(fn (AffiliateCommissionType $type) => [$type->value => $type->label()])->all())
                                    ->required(fn (Get $get): bool => (bool) $get('is_affiliate_enabled'))
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('is_affiliate_enabled')),

                                TextInput::make('affiliate_commission_value')
                                    ->label('Nilai komisi')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(fn (Get $get): bool => (bool) $get('is_affiliate_enabled'))
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('is_affiliate_enabled')),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),

                        Section::make('Landing Page Penawaran')
                            ->schema([
                                Toggle::make('landing_page_enabled')
                                    ->label('Aktifkan landing page')
                                    ->default(false)
                                    ->live(),

                                TextInput::make('landing_page_meta_title')
                                    ->label('Meta title')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                                Textarea::make('landing_page_meta_description')
                                    ->label('Meta description')
                                    ->rows(3)
                                    ->nullable()
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                                FileUpload::make('landing_page_zip_path')
                                    ->label('ZIP landing page')
                                    ->disk('local')
                                    ->directory('product-landings/zips')
                                    ->acceptedFileTypes([
                                        'application/zip',
                                        'application/x-zip-compressed',
                                        'multipart/x-zip',
                                    ])
                                    ->maxSize(20480)
                                    ->downloadable()
                                    ->helperText('Upload ZIP berisi index.html dan folder assets. File akan diekstrak otomatis dan dirender sebagai landing page produk.')
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                                TextInput::make('landing_page_entry_file')
                                    ->label('Entry file')
                                    ->default('index.html')
                                    ->required(fn (Get $get): bool => (bool) $get('landing_page_enabled'))
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                                TextInput::make('landing_page_preview_url')
                                    ->label('Preview link landing page')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn (?Product $record, Get $get): string => filled($get('slug'))
                                        ? route('offer.show', ['product' => $get('slug')], absolute: true)
                                        : 'Isi slug produk terlebih dahulu.')
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),

                                Textarea::make('landing_page_shortcode_help')
                                    ->label('Shortcode tersedia')
                                    ->rows(6)
                                    ->dehydrated(false)
                                    ->default("{{product_name}}\n{{product_title}}\n{{product_slug}}\n{{product_type}}\n{{product_price}}\n{{product_sale_price}}\n{{product_effective_price}}\n{{product_short_description}}\n{{product_description}}\n{{checkout_url}}\n{{catalog_url}}\n{{affiliate_name}}\n{{affiliate_code}}\n{{affiliate_store_name}}\n{{affiliate_referral_link}}")
                                    ->readOnly()
                                    ->columnSpanFull()
                                    ->hidden(fn (Get $get): bool => ! (bool) $get('landing_page_enabled')),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Files (metadata)')
                            ->schema([
                                Repeater::make('files')
                                    ->label('File produk')
                                    ->relationship()
                                    ->defaultItems(0)
                                    ->orderable('sort_order')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255),

                                        FileUpload::make('file_path')
                                            ->label('File (opsional)')
                                            ->helperText('File delivery disimpan private dan hanya bisa diakses user yang memiliki entitlement aktif.')
                                            ->disk('local')
                                            ->directory('products/files')
                                            ->nullable(),

                                        TextInput::make('external_url')
                                            ->label('External URL (opsional)')
                                            ->url()
                                            ->maxLength(255)
                                            ->nullable(),

                                        TextInput::make('file_type')
                                            ->label('Tipe file (opsional)')
                                            ->maxLength(50)
                                            ->nullable(),

                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Bundle')
                            ->schema([
                                Select::make('bundledProducts')
                                    ->label('Produk dalam bundle')
                                    ->multiple()
                                    ->relationship('bundledProducts', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->hidden(fn (Get $get): bool => ($get('product_type') ?? null) !== ProductType::Bundle->value),
                            ])
                            ->columnSpanFull(),

                        Section::make('Metadata (opsional)')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->label('Metadata')
                                    ->addButtonLabel('Tambah item')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->nullable(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
