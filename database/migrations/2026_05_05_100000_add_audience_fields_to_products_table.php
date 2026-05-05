<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('visibility_mode', 30)->default('public')->after('visibility');
            $table->string('purchase_mode', 30)->default('everyone')->after('visibility_mode');
            $table->string('access_mode', 30)->default('entitlement_only')->after('purchase_mode');

            $table->json('allowed_viewer_types')->nullable()->after('access_mode');
            $table->json('allowed_buyer_types')->nullable()->after('allowed_viewer_types');
            $table->json('allowed_access_types')->nullable()->after('allowed_buyer_types');
            $table->json('allowed_role_ids')->nullable()->after('allowed_access_types');
            $table->json('allowed_user_ids')->nullable()->after('allowed_role_ids');

            $table->text('ineligible_message')->nullable()->after('allowed_user_ids');
            $table->boolean('hidden_from_marketplace')->default(false)->after('ineligible_message');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'visibility_mode',
                'purchase_mode',
                'access_mode',
                'allowed_viewer_types',
                'allowed_buyer_types',
                'allowed_access_types',
                'allowed_role_ids',
                'allowed_user_ids',
                'ineligible_message',
                'hidden_from_marketplace',
            ]);
        });
    }
};
