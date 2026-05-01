<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_contributor_commission_enabled')->default(false)->after('affiliate_commission_value');
            $table->foreignId('contributor_user_id')->nullable()->constrained('users')->nullOnDelete()->after('is_contributor_commission_enabled');
            $table->string('contributor_commission_type')->nullable()->after('contributor_user_id');
            $table->decimal('contributor_commission_value', 15, 2)->default(0)->after('contributor_commission_type');
            $table->string('contributor_commission_base')->default('gross')->after('contributor_commission_value');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['contributor_user_id']);
            $table->dropColumn([
                'is_contributor_commission_enabled',
                'contributor_user_id',
                'contributor_commission_type',
                'contributor_commission_value',
                'contributor_commission_base',
            ]);
        });
    }
};
