<?php

namespace App\Auth;

use App\Enums\ContentSection;
use App\Filament\Pages\Career;
use App\Filament\Pages\Messages;
use App\Filament\Pages\Specializations;
use App\Filament\Pages\Team;
use App\Filament\Resources\ArticleResource;
use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PanelLoginResponse implements LoginResponse
{
    public static function destination(): string
    {
        $user = Filament::auth()->user();
        if (! $user instanceof User) {
            return Filament::getLoginUrl();
        }

        foreach (ContentSection::cases() as $section) {
            if ($user->canAccessSection($section)) {
                return match ($section) {
                    ContentSection::Articles => ArticleResource::getUrl(),
                    ContentSection::Specializations => Specializations::getUrl(),
                    ContentSection::Team => Team::getUrl(),
                    ContentSection::Career => Career::getUrl(),
                    ContentSection::Messages => Messages::getUrl(),
                };
            }
        }

        return Filament::getProfileUrl();
    }

    public function toResponse($request): RedirectResponse | Redirector
    {
        $request->session()->forget('url.intended');

        return redirect()->to(self::destination());
    }
}
