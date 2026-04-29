<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\InspectLegacyV1DatabaseAction;
use Throwable;

class LegacyV1InspectCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:inspect';

    protected $description = 'Inspect configured legacy EPIC HUB 1.0 database connection and source tables.';

    public function handle(InspectLegacyV1DatabaseAction $inspect): int
    {
        try {
            $result = $inspect->execute();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Legacy connection: %s (%s / %s)',
            $result['connection'],
            $result['driver'],
            $result['database'],
        ));

        $rows = collect($result['tables'])
            ->map(fn (array $table, string $name): array => [
                $name,
                $table['table'],
                $table['exists'] ? 'yes' : 'no',
                (string) ($table['row_count'] ?? '-'),
                implode(', ', $table['missing_columns']),
            ])
            ->values()
            ->all();

        $this->table(['Source', 'Table', 'Exists', 'Rows', 'Missing Columns'], $rows);

        return self::SUCCESS;
    }
}
