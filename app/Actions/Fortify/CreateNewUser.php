<?php

namespace App\Actions\Fortify;

use App\Actions\Affiliates\LockUserReferrerAction;
use App\Actions\Affiliates\ResolveReferralForUserAction;
use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        protected NormalizeWhatsappNumberAction $normalizeWhatsappNumber,
        protected ResolveReferralForUserAction $resolveReferralForUser,
        protected LockUserReferrerAction $lockUserReferrer,
    ) {
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'whatsapp_number' => $this->normalizeWhatsappNumber->execute($input['whatsapp_number'] ?? null),
                'password' => $input['password'],
            ]);

            if (Role::query()->where('name', 'customer')->exists()) {
                $user->assignRole('customer');
            }

            $resolved = $this->resolveReferralForUser->execute($user, request());

            return $this->lockUserReferrer->execute(
                user: $user,
                epiChannel: $resolved['epiChannel'],
                source: $resolved['source'],
            );
        });
    }
}
