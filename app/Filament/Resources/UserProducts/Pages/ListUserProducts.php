<?php

namespace App\Filament\Resources\UserProducts\Pages;

use App\Actions\Access\BuildUserProductImportTemplateAction;
use App\Actions\Access\GrantProductAccessAction;
use App\Actions\Access\ImportProductAccessAction;
use App\Enums\AccessLogAction;
use App\Filament\Resources\UserProducts\UserProductResource;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUserProducts extends ListRecords
{
    protected static string $resource = UserProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('grant_manual_access')
                ->label('Grant access')
                ->color('primary')
                ->form([
                    Select::make('user_id')
                        ->label('User')
                        ->options(fn (): array => User::query()->orderBy('email')->pluck('email', 'id')->all())
                        ->searchable()
                        ->required(),
                    Select::make('product_id')
                        ->label('Produk')
                        ->options(fn (): array => Product::query()->orderBy('title')->pluck('title', 'id')->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $actor = auth()->user();

                    if (! $actor instanceof User) {
                        throw new \RuntimeException('Unauthorized.');
                    }

                    $user = User::query()->findOrFail($data['user_id']);
                    $product = Product::query()->findOrFail($data['product_id']);

                    app(GrantProductAccessAction::class)->execute(
                        user: $user,
                        product: $product,
                        actor: $actor,
                        logAction: AccessLogAction::ManualGrant,
                    );
                }),

            Action::make('import_product_access')
                ->label('Import access')
                ->color('gray')
                ->modalSubmitActionLabel('Import CSV')
                ->extraModalFooterActions([
                    Action::make('download_import_template')
                        ->label('Download template CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function () {
                            $template = app(BuildUserProductImportTemplateAction::class)->execute();

                            return response()->streamDownload(
                                function () use ($template): void {
                                    echo $template['content'];
                                },
                                $template['filename'],
                                [
                                    'Content-Type' => 'text/csv; charset=UTF-8',
                                ],
                            );
                        }),
                ])
                ->form([
                    FileUpload::make('import_file')
                        ->label('File CSV')
                        ->disk('local')
                        ->directory('imports/user-product-access')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(10240)
                        ->required(),
                    TextInput::make('source_label')
                        ->label('Sumber import')
                        ->default('Platform sebelumnya')
                        ->maxLength(100)
                        ->required(),
                    Toggle::make('mark_email_verified')
                        ->label('Tandai email user baru sebagai terverifikasi')
                        ->default(true),
                    Toggle::make('update_existing_users')
                        ->label('Perbarui nama/WhatsApp user yang sudah ada')
                        ->default(true),
                    Textarea::make('available_product_slugs')
                        ->label('Daftar product_slug yang bisa dipakai')
                        ->default(fn (): string => self::availableProductSlugList())
                        ->rows(12)
                        ->dehydrated(false)
                        ->readOnly(),
                ])
                ->action(function (array $data): void {
                    $actor = auth()->user();

                    if (! $actor instanceof User) {
                        throw new \RuntimeException('Unauthorized.');
                    }

                    $result = app(ImportProductAccessAction::class)->execute(
                        actor: $actor,
                        disk: 'local',
                        path: (string) $data['import_file'],
                        sourceLabel: (string) $data['source_label'],
                        markEmailVerified: (bool) ($data['mark_email_verified'] ?? true),
                        updateExistingUsers: (bool) ($data['update_existing_users'] ?? true),
                    );

                    Notification::make()
                        ->title($result['error_count'] > 0 ? 'Import selesai dengan catatan' : 'Import akses berhasil')
                        ->body(self::formatImportResult($result))
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @param  array{
     *     total_rows: int,
     *     processed_rows: int,
     *     resolved_products: int,
     *     created_users: int,
     *     updated_users: int,
     *     granted_accesses: int,
     *     reactivated_accesses: int,
     *     skipped_accesses: int,
     *     error_count: int,
     *     errors: array<int, string>
     * }  $result
     */
    protected static function formatImportResult(array $result): string
    {
        $lines = [
            'Baris dibaca: '.$result['total_rows'],
            'Baris diproses: '.$result['processed_rows'],
            'Produk terdeteksi: '.$result['resolved_products'],
            'User baru: '.$result['created_users'],
            'User diperbarui: '.$result['updated_users'],
            'Akses baru: '.$result['granted_accesses'],
            'Akses diaktifkan ulang: '.$result['reactivated_accesses'],
            'Akses sudah aktif: '.$result['skipped_accesses'],
        ];

        if ($result['error_count'] > 0) {
            $lines[] = 'Error: '.$result['error_count'];

            foreach (array_slice($result['errors'], 0, 3) as $error) {
                $lines[] = '- '.$error;
            }
        }

        return implode(PHP_EOL, $lines);
    }

    protected static function availableProductSlugList(): string
    {
        $products = Product::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        if ($products->isEmpty()) {
            return 'Belum ada produk tersedia.';
        }

        return $products
            ->map(fn (Product $product): string => sprintf(
                '%s | %s',
                $product->slug ?: '-',
                $product->title,
            ))
            ->implode(PHP_EOL);
    }
}
