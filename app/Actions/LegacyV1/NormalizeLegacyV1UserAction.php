<?php

namespace App\Actions\LegacyV1;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use Illuminate\Support\Str;

class NormalizeLegacyV1UserAction
{
    public function __construct(
        protected NormalizeWhatsappNumberAction $normalizeWhatsappNumber,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @return array{
     *     name: ?string,
     *     epic_id: ?string,
     *     email: ?string,
     *     whatsapp: ?string,
     *     sponsor_epic_id: ?string,
     *     city: ?string
     * }
     */
    public function execute(array $row): array
    {
        $email = $this->nullableString($row['email'] ?? null);
        $email = $email ? Str::lower($email) : null;

        $epicId = $this->normalizeEpicId($row['epic_id'] ?? null);

        $name = $this->nullableString($row['name'] ?? null);

        if ($name === null && $email !== null) {
            $name = $this->fallbackNameFromEmail($email);
        }

        if ($name === null && $epicId !== null) {
            $name = 'Legacy '.$epicId;
        }

        return [
            'name' => $name,
            'epic_id' => $epicId,
            'email' => $email,
            'whatsapp' => $this->normalizeWhatsappNumber->execute($this->nullableString($row['whatsapp'] ?? null)),
            'sponsor_epic_id' => $this->normalizeEpicId($row['sponsor_epic_id'] ?? null),
            'city' => $this->nullableString($row['city'] ?? null),
        ];
    }

    protected function normalizeEpicId(mixed $value): ?string
    {
        $value = Str::upper($this->nullableString($value) ?? '');

        return $value !== '' ? $value : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    protected function fallbackNameFromEmail(string $email): string
    {
        $localPart = Str::before($email, '@');
        $normalized = str_replace(['.', '_', '-'], ' ', $localPart);

        return Str::title(trim($normalized));
    }
}
