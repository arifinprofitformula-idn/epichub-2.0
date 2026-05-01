<?php

namespace App\Concerns;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'whatsapp_number' => $this->whatsappNumberRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate WhatsApp numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function whatsappNumberRules(?int $userId = null): array
    {
        return [
            'nullable',
            'string',
            'max:30',
            'regex:/^[0-9+\-\s]+$/',
            function (string $attribute, mixed $value, Closure $fail) use ($userId): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }

                $normalized = app(NormalizeWhatsappNumberAction::class)->execute($value);

                if (! filled($normalized)) {
                    return;
                }

                // Build all possible stored formats for this number so we catch
                // legacy records that were saved before normalization was enforced.
                $localFormat = '0'.substr($normalized, 2);   // 628xxx → 08xxx
                $plusFormat  = '+'.$normalized;              // 628xxx → +628xxx

                $query = User::query()->where(function ($q) use ($normalized, $localFormat, $plusFormat): void {
                    $q->where('whatsapp_number', $normalized)
                        ->orWhere('whatsapp_number', $localFormat)
                        ->orWhere('whatsapp_number', $plusFormat);
                });

                if ($userId !== null) {
                    $query->whereKeyNot($userId);
                }

                if ($query->exists()) {
                    $fail('Nomor WhatsApp sudah terdaftar pada akun lain.');
                }
            },
        ];
    }
}
