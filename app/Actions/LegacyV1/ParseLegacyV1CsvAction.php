<?php

namespace App\Actions\LegacyV1;

use Illuminate\Support\Str;
use RuntimeException;

class ParseLegacyV1CsvAction
{
    /**
     * @param  array<string, array<int, string>>  $aliases
     * @param  array<int, string>  $fallbackOrder
     * @return array{
     *     file_name: string,
     *     file_size: int,
     *     file_hash: string,
     *     rows: array<int, array<string, string|int>>
     * }
     */
    public function execute(string $absolutePath, array $aliases, array $fallbackOrder): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('File CSV tidak ditemukan.');
        }

        $handle = fopen($absolutePath, 'rb');

        if (! is_resource($handle)) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        $delimiter = $this->detectDelimiter($handle);
        rewind($handle);

        $rows = [];
        $line = 0;
        $headerMap = null;
        $checkedHeader = false;

        while (($columns = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            $columns = array_map(
                fn (mixed $value): string => $this->normalizeCell($value),
                $columns,
            );

            if ($this->rowIsEmpty($columns)) {
                continue;
            }

            if (! $checkedHeader) {
                $headerMap = $this->resolveHeaderMap($columns, $aliases);
                $checkedHeader = true;

                if ($headerMap !== null) {
                    continue;
                }

                $headerMap = [];
            }

            $row = ['line' => $line];

            foreach ($fallbackOrder as $fallbackIndex => $field) {
                $row[$field] = $this->columnValue($columns, $headerMap, $field, $fallbackIndex);
            }

            $rows[] = $row;
        }

        fclose($handle);

        if ($rows === []) {
            throw new RuntimeException('File CSV kosong atau tidak berisi data yang bisa diproses.');
        }

        return [
            'file_name' => basename($absolutePath),
            'file_size' => (int) (filesize($absolutePath) ?: 0),
            'file_hash' => (string) hash_file('sha256', $absolutePath),
            'rows' => $rows,
        ];
    }

    /**
     * @param  resource  $handle
     */
    protected function detectDelimiter($handle): string
    {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            $delimiters = [',', ';', "\t", '|'];
            $bestDelimiter = ',';
            $bestScore = -1;

            foreach ($delimiters as $delimiter) {
                $score = substr_count($trimmed, $delimiter);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestDelimiter = $delimiter;
                }
            }

            return $bestDelimiter;
        }

        return ',';
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, array<int, string>>  $aliases
     * @return array<string, int>|null
     */
    protected function resolveHeaderMap(array $columns, array $aliases): ?array
    {
        $recognized = [];

        foreach ($columns as $index => $column) {
            $header = $this->normalizeHeader($column);

            foreach ($aliases as $field => $fieldAliases) {
                $normalizedAliases = array_map(
                    fn (string $alias): string => $this->normalizeHeader($alias),
                    $fieldAliases,
                );

                if (in_array($header, $normalizedAliases, true) && ! array_key_exists($field, $recognized)) {
                    $recognized[$field] = $index;
                    break;
                }
            }
        }

        return count($recognized) > 0 ? $recognized : null;
    }

    protected function normalizeHeader(string $value): string
    {
        $normalized = Str::of($value)
            ->replace("\xEF\xBB\xBF", '')
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->value();

        return preg_replace('/[^a-z0-9_]/', '', $normalized) ?? '';
    }

    protected function normalizeCell(mixed $value): string
    {
        $string = is_scalar($value) ? (string) $value : '';

        return trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $string));
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, int>  $headerMap
     */
    protected function columnValue(array $columns, array $headerMap, string $field, int $fallbackIndex): string
    {
        $index = $headerMap[$field] ?? $fallbackIndex;

        return isset($columns[$index]) ? trim($columns[$index]) : '';
    }

    /**
     * @param  array<int, string>  $columns
     */
    protected function rowIsEmpty(array $columns): bool
    {
        foreach ($columns as $column) {
            if ($column !== '') {
                return false;
            }
        }

        return true;
    }
}
