<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')
                ->nullable()
                ->constrained('product_categories')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();

            $table->string('product_type', 30);
            $table->string('thumbnail')->nullable();

            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->nullable();

            $table->string('status', 20)->default('draft');
            $table->string('visibility', 20)->default('public');
            $table->string('access_type', 30)->default('instant_access');

            $table->integer('stock')->nullable();
            $table->integer('quota')->nullable();

            $table->timestamp('publish_at')->nullable();

            $table->boolean('is_featured')->default(false);

            $table->boolean('is_affiliate_enabled')->default(false);
            $table->string('affiliate_commission_type', 20)->nullable();
            $table->decimal('affiliate_commission_value', 15, 2)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibility']);
            $table->index(['is_featured', 'status', 'visibility']);
            $table->index('publish_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
