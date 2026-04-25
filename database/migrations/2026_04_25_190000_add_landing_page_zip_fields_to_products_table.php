<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasEnabled = Schema::hasColumn('products', 'landing_page_enabled');
        $hasMetaTitle = Schema::hasColumn('products', 'landing_page_meta_title');
        $hasMetaDescription = Schema::hasColumn('products', 'landing_page_meta_description');

        Schema::table('products', function (Blueprint $table) use ($hasEnabled, $hasMetaTitle, $hasMetaDescription): void {
            if (! $hasEnabled) {
                $table->boolean('landing_page_enabled')
                    ->default(false)
                    ->after('is_affiliate_enabled');
            }

            if (! Schema::hasColumn('products', 'landing_page_zip_path')) {
                $table->string('landing_page_zip_path')
                    ->nullable()
                    ->after('landing_page_enabled');
            }

            if (! Schema::hasColumn('products', 'landing_page_extract_path')) {
                $table->string('landing_page_extract_path')
                    ->nullable()
                    ->after('landing_page_zip_path');
            }

            if (! Schema::hasColumn('products', 'landing_page_entry_file')) {
                $table->string('landing_page_entry_file')
                    ->default('index.html')
                    ->after('landing_page_extract_path');
            }

            if (! Schema::hasColumn('products', 'landing_page_asset_token')) {
                $table->string('landing_page_asset_token')
                    ->nullable()
                    ->after('landing_page_entry_file');
            }

            if (! Schema::hasColumn('products', 'landing_page_uploaded_at')) {
                $table->timestamp('landing_page_uploaded_at')
                    ->nullable()
                    ->after('landing_page_asset_token');
            }

            if (! $hasMetaTitle) {
                $table->string('landing_page_meta_title')
                    ->nullable()
                    ->after('landing_page_uploaded_at');
            }

            if (! $hasMetaDescription) {
                $table->text('landing_page_meta_description')
                    ->nullable()
                    ->after('landing_page_meta_title');
            }

            if (! Schema::hasColumn('products', 'landing_page_version')) {
                $table->unsignedInteger('landing_page_version')
                    ->default(1)
                    ->after('landing_page_meta_description');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'landing_page_html')) {
                $table->dropColumn('landing_page_html');
            }

            if (Schema::hasColumn('products', 'landing_page_custom_css')) {
                $table->dropColumn('landing_page_custom_css');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->unique('landing_page_asset_token');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['landing_page_asset_token']);
            $table->dropColumn([
                'landing_page_enabled',
                'landing_page_zip_path',
                'landing_page_extract_path',
                'landing_page_entry_file',
                'landing_page_asset_token',
                'landing_page_uploaded_at',
                'landing_page_meta_title',
                'landing_page_meta_description',
                'landing_page_version',
            ]);
        });
    }
};
