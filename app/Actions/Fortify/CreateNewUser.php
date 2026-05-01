<?php

namespace App\Actions\Fortify;

use App\Actions\Affiliates\LockUserReferrerAction;
use App\Actions\Affiliates\ResolveReferralForUserAction;
use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\Mailketing\MailketingSubscriberService;
use App\Services\Notifications\EmailNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            $user = $this->lockUserReferrer->execute(
                user: $user,
                epiChannel: $resolved['epiChannel'],
                source: $resolved['source'],
            );

            $this->sendWelcomeEmail($user);
            $this->queueSubscriberAutomation($user);

            return $user;
        });
    }

    private function sendWelcomeEmail(User $user): void
    {
        try {
            app(EmailNotificationService::class)->sendTransactionalEmail(
                recipient: ['email' => $user->email, 'name' => $user->name],
                subject: 'Selamat Datang di EPIC HUB',
                view: 'emails.auth.welcome',
                data: [
                    'userName'      => $user->name,
                    'userEmail'     => $user->email,
                    'dashboardUrl'  => url('/dashboard'),
                    'productsUrl'   => url('/produk-saya'),
                ],
                eventType: 'user_registered',
                metadata: ['notifiable' => $user],
            );
        } catch (\Throwable $e) {
            Log::error('CreateNewUser: gagal kirim welcome email', ['error' => $e->getMessage()]);
        }
    }

    private function queueSubscriberAutomation(User $user): void
    {
        DB::afterCommit(function () use ($user): void {
            try {
                app(MailketingSubscriberService::class)->addUserToDefaultList($user);
            } catch (\Throwable $e) {
                Log::error('CreateNewUser: gagal subscriber automation', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
