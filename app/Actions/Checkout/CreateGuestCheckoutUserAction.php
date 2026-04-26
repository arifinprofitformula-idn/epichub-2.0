<?php

namespace App\Actions\Checkout;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateGuestCheckoutUserAction
{
    use PasswordValidationRules;

    public function __construct(
        protected NormalizeWhatsappNumberAction $normalizeWhatsappNumber,
    ) {
    }

    /**
     * @param  array{name: string, email: string, whatsapp_number: string, password: string, password_confirmation: string}  $input
     */
    public function execute(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'password' => $this->passwordRules(),
        ])->validate();

        $normalizedWhatsapp = $this->normalizeWhatsappNumber->execute($input['whatsapp_number']);

        if ($normalizedWhatsapp === null) {
            throw ValidationException::withMessages([
                'whatsapp_number' => 'Nomor WhatsApp belum valid. Gunakan format angka yang dapat dihubungi.',
            ]);
        }

        $emailUser = User::query()
            ->where('email', $input['email'])
            ->first();

        $whatsappUser = User::query()
            ->whereNotNull('whatsapp_number')
            ->get()
            ->first(fn (User $user): bool => $user->normalizedWhatsappNumber($user->whatsapp_number) === $normalizedWhatsapp);

        if ($emailUser && $whatsappUser && $emailUser->id !== $whatsappUser->id) {
            throw ValidationException::withMessages([
                'email' => 'Email atau WhatsApp sudah digunakan oleh akun lain. Silakan login atau hubungi admin.',
                'whatsapp_number' => 'Email atau WhatsApp sudah digunakan oleh akun lain. Silakan login atau hubungi admin.',
            ]);
        }

        if ($emailUser) {
            throw ValidationException::withMessages([
                'email' => 'Email ini sudah terdaftar. Silakan login untuk melanjutkan pembelian dengan akun tersebut.',
            ]);
        }

        if ($whatsappUser) {
            throw ValidationException::withMessages([
                'whatsapp_number' => 'Nomor WhatsApp ini sudah terdaftar. Silakan login menggunakan akun yang terhubung dengan nomor tersebut.',
            ]);
        }

        $user = User::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'whatsapp_number' => $normalizedWhatsapp,
            'password' => Hash::make($input['password']),
        ]);

        if (Role::query()->where('name', 'customer')->exists()) {
            $user->assignRole('customer');
        }

        return $user;
    }
}
