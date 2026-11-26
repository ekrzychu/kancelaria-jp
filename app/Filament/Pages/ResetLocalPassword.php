<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\PasswordReset\ResetPassword;
use SensitiveParameter;

final class ResetLocalPassword extends ResetPassword
{
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [...parent::getCredentialsFromFormData($data), 'is_active' => true];
    }
}
