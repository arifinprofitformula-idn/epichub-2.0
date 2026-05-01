<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'legacy_v1_import_errors',
        'legacy_v1_product_accesses',
        'legacy_v1_sponsor_links',
        'legacy_v1_user_mappings',
        'legacy_v1_users',
        'legacy_v1_payments',
        'legacy_v1_orders',
        'legacy_v1_payouts',
        'legacy_v1_product_mappings',
        'legacy_v1_import_batches',
        'legacy_v1_commission_import_errors',
        'legacy_v1_commissions',
        'legacy_v1_commission_import_batches',
    ];

    public function up(): void
    {
        $this->withoutForeignKeyChecks(function (): void {
            foreach ($this->tables as $table) {
                if (Schema::hasTable($table)) {
                    Schema::dropIfExists($table);
                }
            }
        });
    }

    public function down(): void
    {
        //
    }

    protected function withoutForeignKeyChecks(callable $callback): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            $callback();
        } finally {
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }
};
