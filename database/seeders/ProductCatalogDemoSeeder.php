<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ebook = ProductCategory::query()->firstOrCreate(
            ['slug' => 'ebook'],
            [
                'name' => 'Ebook',
                'slug' => 'ebook',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $course = ProductCategory::query()->firstOrCreate(
            ['slug' => 'ecourse'],
            [
                'name' => 'Ecourse',
                'slug' => 'ecourse',
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        Product::query()->firstOrCreate(
            ['slug' => 'strategi-growth-30-hari'],
            [
                'product_category_id' => $ebook->id,
                'title' => 'Strategi Growth 30 Hari',
                'slug' => 'strategi-growth-30-hari',
                'short_description' => 'Panduan ringkas untuk membangun ritme growth yang konsisten.',
                'full_description' => '<p>Ini adalah produk demo untuk kebutuhan preview katalog.</p>',
                'product_type' => ProductType::Ebook,
                'price' => 99000,
                'sale_price' => 79000,
                'status' => ProductStatus::Published,
                'visibility' => ProductVisibility::Public,
                'publish_at' => now(),
                'is_featured' => true,
                'sort_order' => 0,
            ],
        );

        Product::query()->firstOrCreate(
            ['slug' => 'fundamental-digital-commerce'],
            [
                'product_category_id' => $course->id,
                'title' => 'Fundamental Digital Commerce',
                'slug' => 'fundamental-digital-commerce',
                'short_description' => 'Belajar fundamental untuk mulai jualan produk digital.',
                'full_description' => '<p>Ini adalah produk demo untuk kebutuhan preview katalog.</p>',
                'product_type' => ProductType::Course,
                'price' => 149000,
                'status' => ProductStatus::Published,
                'visibility' => ProductVisibility::Public,
                'publish_at' => now(),
                'is_featured' => true,
                'sort_order' => 1,
            ],
        );
    }
}
