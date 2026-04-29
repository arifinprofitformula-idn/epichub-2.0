<?php

namespace App\Actions\LegacyV1;

use Illuminate\Support\Facades\DB;

class InspectLegacyV1DatabaseAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $connectionName = (string) config('legacy_v1.connection', 'legacy_mysql');
        $connection = DB::connection($connectionName);
        $schema = $connection->getSchemaBuilder();
        $sources = (array) config('legacy_v1.sources', []);

        $tables = [];

        foreach ($sources as $sourceName => $sourceConfig) {
            $table = (string) data_get($sourceConfig, 'table', '');
            $columns = (array) data_get($sourceConfig, 'columns', []);
            $exists = $table !== '' && $schema->hasTable($table);
            $existingColumns = $exists ? $schema->getColumnListing($table) : [];

            $tables[$sourceName] = [
                'table' => $table,
                'exists' => $exists,
                'required_columns' => $columns,
                'missing_columns' => collect($columns)
                    ->filter(fn (mixed $column): bool => is_string($column) && $column !== '' && ! in_array($column, $existingColumns, true))
                    ->values()
                    ->all(),
                'row_count' => $exists ? (int) $connection->table($table)->count() : null,
            ];
        }

        return [
            'connection' => $connectionName,
            'driver' => $connection->getDriverName(),
            'database' => $connection->getDatabaseName(),
            'tables' => $tables,
            'inspected_at' => now()->toIso8601String(),
        ];
    }
}
