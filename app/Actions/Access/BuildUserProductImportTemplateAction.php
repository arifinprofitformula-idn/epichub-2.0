<?php

namespace App\Actions\Access;

class BuildUserProductImportTemplateAction
{
    /**
     * @return array{filename: string, content: string}
     */
    public function execute(): array
    {
        $rows = [
            ['email', 'name', 'whatsapp_number', 'product_slug', 'granted_at'],
            ['andi@example.com', 'Andi Pratama', '081234567890', 'ebook-growth-basic', '2026-04-27 09:00:00'],
            ['siti@example.com', 'Siti Rahma', '6281234567891', 'kelas-closing-fundamental', '2026-04-27 09:15:00'],
            ['budi@example.com', 'Budi Saputra', '', 'event-komunitas-premium', '2026-04-27 10:00:00'],
        ];

        $stream = fopen('php://temp', 'r+');

        if (! is_resource($stream)) {
            throw new \RuntimeException('Gagal membuat template CSV.');
        }

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);

        $content = stream_get_contents($stream);

        fclose($stream);

        if (! is_string($content)) {
            throw new \RuntimeException('Gagal membaca template CSV.');
        }

        return [
            'filename' => 'template-import-user-product.csv',
            'content' => $content,
        ];
    }
}
