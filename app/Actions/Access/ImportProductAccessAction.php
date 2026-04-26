<?php

namespace App\Actions\Access;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Enums\AccessLogAction;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class ImportProductAccessAction
{
    public function __construct(
        protected GrantProductAccessAction $grantProductAccess,
        protected NormalizeWhatsappNumberAction $normalizeWhatsappNumber,
    ) {}

    /**
     * @return array{
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
     * }
     */
    public function execute(
        User $actor,
        string $disk,
        string $path,
        string $sourceLabel = 'Platform sebelumnya',
        bool $markEmailVerified = true,
        bool $updateExistingUsers = true,
    ): array {
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw new RuntimeException('File import tidak ditemukan.');
        }

        $absolutePath = $storage->path($path);

        try {
            $rows = $this->parseRows($absolutePath);
        } finally {
            $storage->delete($path);
        }

        $result = [
            'total_rows' => count($rows),
            'processed_rows' => 0,
            'resolved_products' => 0,
            'created_users' => 0,
            'updated_users' => 0,
            'granted_accesses' => 0,
            'reactivated_accesses' => 0,
            'skipped_accesses' => 0,
            'error_count' => 0,
            'errors' => [],
        ];

        foreach ($rows as $row) {
            try {
                $rowResult = $this->importRow(
                    row: $row,
                    actor: $actor,
                    sourceLabel: $sourceLabel,
                    markEmailVerified: $markEmailVerified,
                    updateExistingUsers: $updateExistingUsers,
                );

                $result['processed_rows']++;
                $result['resolved_products'] += $rowResult['resolved_product'] ? 1 : 0;
                $result['created_users'] += $rowResult['created_user'] ? 1 : 0;
                $result['updated_users'] += $rowResult['updated_user'] ? 1 : 0;
                $result['granted_accesses'] += $rowResult['granted_access'] ? 1 : 0;
                $result['reactivated_accesses'] += $rowResult['reactivated_access'] ? 1 : 0;
                $result['skipped_accesses'] += $rowResult['skipped_access'] ? 1 : 0;
            } catch (Throwable $exception) {
                $result['error_count']++;
                $result['errors'][] = sprintf('Baris %d: %s', $row['line'], $exception->getMessage());
            }
        }

        return $result;
    }

    /**
     * @param  array{
     *     line: int,
     *     email: string,
     *     name: string,
     *     whatsapp_number: string,
     *     product_slug: string,
     *     granted_at: string,
     *     product_title: string,
     *     product_id: string
     * }  $row
     * @return array{
     *     resolved_product: bool,
     *     created_user: bool,
     *     updated_user: bool,
     *     granted_access: bool,
     *     reactivated_access: bool,
     *     skipped_access: bool
     * }
     */
    protected function importRow(
        array $row,
        User $actor,
        string $sourceLabel,
        bool $markEmailVerified,
        bool $updateExistingUsers,
    ): array {
        $email = Str::lower(trim($row['email']));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Email tidak valid.');
        }

        $name = trim($row['name']) !== '' ? trim($row['name']) : $this->fallbackNameFromEmail($email);
        $rawWhatsapp = trim($row['whatsapp_number']);
        $normalizedWhatsapp = $rawWhatsapp !== ''
            ? $this->normalizeWhatsappNumber->execute($rawWhatsapp)
            : null;

        if ($rawWhatsapp !== '' && $normalizedWhatsapp === null) {
            throw new RuntimeException('Nomor WhatsApp tidak valid.');
        }

        $product = $this->resolveProductForRow($row);
        $grantedAt = $this->resolveGrantedAtForRow($row);

        $user = User::query()->where('email', $email)->first();

        if ($conflictingWhatsappUser = $this->findUserByWhatsapp($normalizedWhatsapp, $user?->id)) {
            throw new RuntimeException(sprintf(
                'Nomor WhatsApp dipakai oleh akun lain (%s).',
                $conflictingWhatsappUser->email,
            ));
        }

        $createdUser = false;
        $updatedUser = false;

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => $markEmailVerified ? now() : null,
                'password' => Str::random(32),
                'whatsapp_number' => $normalizedWhatsapp,
            ]);

            $createdUser = true;
        } elseif ($updateExistingUsers) {
            $updates = [];

            if ($name !== '' && $name !== $user->name) {
                $updates['name'] = $name;
            }

            $currentWhatsapp = $user->normalizedWhatsappNumber($user->whatsapp_number);

            if ($normalizedWhatsapp !== null && $normalizedWhatsapp !== $currentWhatsapp) {
                $updates['whatsapp_number'] = $normalizedWhatsapp;
            }

            if ($markEmailVerified && $user->email_verified_at === null) {
                $updates['email_verified_at'] = now();
            }

            if ($updates !== []) {
                $user->update($updates);
                $updatedUser = true;
                $user = $user->refresh();
            }
        }

        $this->ensureCustomerRole($user);

        $existingAccess = UserProduct::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->whereNull('order_id')
            ->latest('id')
            ->first();

        $hadActiveAccess = $existingAccess?->isActive() ?? false;
        $hadPreviousAccess = $existingAccess !== null;

        $this->grantProductAccess->execute(
            user: $user,
            product: $product,
            actor: $actor,
            logAction: AccessLogAction::ImportedGrant,
            metadata: [
                'source' => 'import',
                'source_label' => $sourceLabel,
                'line' => $row['line'],
            ],
            grantedAt: $grantedAt,
        );

        return [
            'resolved_product' => true,
            'created_user' => $createdUser,
            'updated_user' => $updatedUser,
            'granted_access' => ! $hadActiveAccess && ! $hadPreviousAccess,
            'reactivated_access' => ! $hadActiveAccess && $hadPreviousAccess,
            'skipped_access' => $hadActiveAccess,
        ];
    }

    /**
     * @return array<int, array{
     *     line: int,
     *     email: string,
     *     name: string,
     *     whatsapp_number: string,
     *     product_slug: string,
     *     granted_at: string,
     *     product_title: string,
     *     product_id: string
     * }>
     */
    protected function parseRows(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'rb');

        if (! is_resource($handle)) {
            throw new RuntimeException('File import tidak dapat dibaca.');
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
                $headerMap = $this->resolveHeaderMap($columns);
                $checkedHeader = true;

                if ($headerMap !== null) {
                    continue;
                }

                $headerMap = [];
            }

            $rows[] = [
                'line' => $line,
                'email' => $this->columnValue($columns, $headerMap, 'email', 0),
                'name' => $this->columnValue($columns, $headerMap, 'name', 1),
                'whatsapp_number' => $this->columnValue($columns, $headerMap, 'whatsapp_number', 2),
                'product_slug' => $this->columnValue($columns, $headerMap, 'product_slug', 3),
                'granted_at' => $this->columnValue($columns, $headerMap, 'granted_at', 4),
                'product_title' => $this->columnValue($columns, $headerMap, 'product_title', 5),
                'product_id' => $this->columnValue($columns, $headerMap, 'product_id', 6),
            ];
        }

        fclose($handle);

        if ($rows === []) {
            throw new RuntimeException('File import kosong atau tidak berisi data yang bisa diproses.');
        }

        return $rows;
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
     * @return array<string, int>|null
     */
    protected function resolveHeaderMap(array $columns): ?array
    {
        $map = [];

        foreach ($columns as $index => $column) {
            $header = $this->normalizeHeader($column);

            $canonical = match ($header) {
                'email', 'e_mail', 'alamat_email' => 'email',
                'name', 'nama', 'full_name', 'fullname', 'nama_lengkap' => 'name',
                'whatsapp', 'whatsapp_number', 'phone', 'phone_number', 'nomor_hp', 'nomorhp', 'no_hp', 'nohp', 'no_wa', 'nowa' => 'whatsapp_number',
                'product_slug', 'slug_produk', 'produk_slug' => 'product_slug',
                'granted_at', 'grantedat', 'active_granted_at', 'access_granted_at', 'tanggal_aktif', 'tanggal_grant', 'waktu_grant' => 'granted_at',
                'product_title', 'product_name', 'nama_produk', 'judul_produk', 'produk', 'product' => 'product_title',
                'product_id', 'produk_id' => 'product_id',
                default => null,
            };

            if ($canonical !== null && ! array_key_exists($canonical, $map)) {
                $map[$canonical] = $index;
            }
        }

        return array_key_exists('email', $map) ? $map : null;
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

    protected function fallbackNameFromEmail(string $email): string
    {
        $localPart = Str::before($email, '@');
        $normalized = str_replace(['.', '_', '-'], ' ', $localPart);
        $normalized = trim($normalized);

        return $normalized !== '' ? Str::title($normalized) : 'Pengguna Import';
    }

    protected function ensureCustomerRole(User $user): void
    {
        if (! Role::query()->where('name', 'customer')->exists()) {
            return;
        }

        if (! $user->hasRole('customer')) {
            $user->assignRole('customer');
        }
    }

    protected function findUserByWhatsapp(?string $normalizedWhatsapp, ?int $ignoreUserId = null): ?User
    {
        if ($normalizedWhatsapp === null) {
            return null;
        }

        return User::query()
            ->whereNotNull('whatsapp_number')
            ->get()
            ->first(fn (User $user): bool => $user->id !== $ignoreUserId
                && $user->normalizedWhatsappNumber($user->whatsapp_number) === $normalizedWhatsapp);
    }

    /**
     * @param  array{
     *     line: int,
     *     email: string,
     *     name: string,
     *     whatsapp_number: string,
     *     product_slug: string,
     *     granted_at: string,
     *     product_title: string,
     *     product_id: string
     * }  $row
     */
    protected function resolveProductForRow(array $row): Product
    {
        $productId = trim($row['product_id']);
        $productSlug = trim($row['product_slug']);
        $productTitle = trim($row['product_title']);

        if ($productId !== '' && ctype_digit($productId)) {
            $product = Product::query()->find((int) $productId);

            if ($product) {
                return $product;
            }
        }

        if ($productSlug !== '') {
            $product = Product::query()->where('slug', $productSlug)->first();

            if ($product) {
                return $product;
            }
        }

        if ($productTitle !== '') {
            $product = Product::query()->where('title', $productTitle)->first();

            if ($product) {
                return $product;
            }

            $product = Product::query()
                ->get()
                ->first(fn (Product $product): bool => Str::lower($product->title) === Str::lower($productTitle));

            if ($product) {
                return $product;
            }
        }

        throw new RuntimeException('Produk tidak ditemukan. Gunakan product_slug, product_id, atau product_title yang valid.');
    }

    /**
     * @param  array{
     *     line: int,
     *     email: string,
     *     name: string,
     *     whatsapp_number: string,
     *     product_slug: string,
     *     granted_at: string,
     *     product_title: string,
     *     product_id: string
     * }  $row
     */
    protected function resolveGrantedAtForRow(array $row): ?Carbon
    {
        $grantedAt = trim($row['granted_at']);

        if ($grantedAt === '') {
            return null;
        }

        try {
            return Carbon::parse($grantedAt);
        } catch (Throwable) {
            throw new RuntimeException('Format granted_at tidak valid.');
        }
    }
}
