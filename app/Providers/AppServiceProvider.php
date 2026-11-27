<?php

namespace App\Providers;

use App\Auth\PanelLoginResponse;
use App\Http\Controllers\PublicPages;
use App\Http\Middleware\RequireCurrentAccountSession;
use App\Models\User;
use App\Support\SiteHosts;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, PanelLoginResponse::class);
    }

    public function boot(): void
    {
        SiteHosts::validate();
        Password::defaults(fn (): Password => Password::min(12)->rules([
            fn (string $attribute, mixed $value, \Closure $fail) => is_string($value) && strlen($value) > 72
                ? $fail('Hasło może mieć najwyżej 72 bajty.')
                : null,
        ]));

        if (config('hosts.admin') !== null && ! $this->app->routesAreCached()) {
            Livewire::setUpdateRoute(fn ($handle, string $path) => Route::domain(config('hosts.admin'))
                ->post($path, $handle)
                ->middleware('web')
                ->name('panel.'));
        }

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip())
            ->response(fn (Request $request, array $headers) => app(PublicPages::class)
                ->contact(429, ['formError' => 'Zbyt wiele prób. Odczekaj minutę i spróbuj ponownie.'])
                ->withHeaders($headers)));

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User && $event->user->is_active && request()->hasSession()) {
                request()->session()->put(RequireCurrentAccountSession::SESSION_KEY, $event->user->session_version);
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            if ($event->user instanceof User) {
                User::whereKey($event->user->getKey())->increment('session_version');
            }
        });
    }
}
