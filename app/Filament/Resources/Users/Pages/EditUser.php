<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use RuntimeException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_reset_password')
                ->label('Kirim Reset Password')
                ->icon('heroicon-o-envelope')
                ->visible(fn (): bool => UserResource::canManageUsers())
                ->requiresConfirmation()
                ->action(function (): void {
                    $actor = auth()->user();

                    if (! $actor instanceof \App\Models\User) {
                        throw new RuntimeException('Unauthorized.');
                    }

                    try {
                        UserResource::sendResetPasswordLink($this->getRecord(), $actor);
                    } catch (RuntimeException $exception) {
                        Notification::make()
                            ->title('Gagal mengirim reset password')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Link reset password dikirim')
                        ->success()
                        ->send();
                }),
        ];
    }
}
