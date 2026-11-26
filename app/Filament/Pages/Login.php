<?php

namespace App\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\Login as FilamentLogin;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;

final class Login extends FilamentLogin
{
    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()->hidden()->dehydrated(false);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        $this->addError('data.email', __('filament-panels::auth/pages/login.notifications.throttled.title')
            .'. '.__('filament-panels::auth/pages/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
            ]));

        return null;
    }
}
