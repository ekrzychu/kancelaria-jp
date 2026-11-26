<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class RequestLocalPasswordReset extends RequestPasswordReset
{
    protected function getCredentialsFromFormData(array $data): array
    {
        return [...parent::getCredentialsFromFormData($data), 'is_active' => true];
    }

    protected function getFailureNotification(string $status): ?Notification
    {
        $this->form->fill();

        return $this->getSentNotification(Password::RESET_LINK_SENT);
    }

    protected function getSentNotification(string $status): ?Notification
    {
        return Notification::make()->success()->title('Jeśli konto może odzyskać dostęp, utworzono link do zmiany hasła.');
    }
}
