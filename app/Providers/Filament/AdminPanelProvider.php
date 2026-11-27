<?php

namespace App\Providers\Filament;

use App\Auth\PanelLoginResponse;
use App\Filament\Pages\Login;
use App\Filament\Pages\Profile;
use App\Filament\Pages\RequestLocalPasswordReset;
use App\Filament\Pages\ResetLocalPassword;
use App\Filament\Pages\Statistics;
use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\JobOfferResource;
use App\Filament\Resources\SpecializationResource;
use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\UserResource;
use App\Http\Controllers\ArticlePreview;
use App\Http\Controllers\MediaController;
use App\Http\Middleware\RequireCurrentAccountSession;
use App\Support\PanelAvatar;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Route;

final class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        if (config('hosts.admin') !== null) {
            parent::register();
        }
    }

    public function panel(Panel $panel): Panel
    {
        $testRecovery = app()->environment('local', 'testing')
            && config('hosts.test_recovery') && config('mail.default') === 'array';

        return $panel
            ->default()
            ->id('admin')
            ->domain(config('hosts.admin'))
            ->path('')
            ->login(Login::class)
            ->profile(Profile::class, isSimple: false)
            ->userMenuItems(['profile' => fn (Action $action): Action => $action->label('Mój profil')])
            ->passwordReset($testRecovery ? RequestLocalPasswordReset::class : null, ResetLocalPassword::class)
            ->brandName(config('app.name'))
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.35rem')
            ->unsavedChangesAlerts()
            ->font('system-ui', provider: \Filament\FontProviders\LocalFontProvider::class)
            ->defaultAvatarProvider(PanelAvatar::class)
            ->homeUrl(PanelLoginResponse::destination(...))
            ->authenticatedRoutes(function (): void {
                Route::get('/podglad/artykuly/{article}', ArticlePreview::class)->name('articles.preview');
                Route::post('/media-panel/upload/{purpose}', [MediaController::class, 'upload'])
                    ->middleware('throttle:20,1')->name('media.upload');
                Route::get('/media-panel/{media}', [MediaController::class, 'privateImage'])
                    ->where('media', '[a-f0-9]{32}')->name('media.preview');
            })
            ->colors(['primary' => [
                50 => '#f9f0f4', 100 => '#f0e2e9', 200 => '#e0c9d5',
                300 => '#bc97a9', 400 => '#895f73', 500 => '#5f3149',
                600 => '#1d0613', 700 => '#321124', 800 => '#1d0613',
                900 => '#190510', 950 => '#12030b',
            ]])
            ->darkMode(false)
            ->globalSearch(false)
            ->breadcrumbs(false)
            ->sidebarWidth('13.5rem')
            ->navigationGroups([
                NavigationGroup::make('Kontakt')->collapsible(false),
                NavigationGroup::make('Administracja')->collapsible(false),
            ])
            ->maxContentWidth(Width::SevenExtraLarge)
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn () => view('filament.panel-style'))
            ->pages([Statistics::class])
            ->resources([ArticleResource::class, SpecializationResource::class, TeamMemberResource::class, JobOfferResource::class, ContactMessageResource::class, UserResource::class])
            ->middleware(['web', DisableBladeIconComponents::class, DispatchServingFilamentEvent::class])
            ->authMiddleware([
                RequireCurrentAccountSession::class,
                Authenticate::class,
                AuthenticateSession::class,
            ], isPersistent: true);
    }
}
