<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class PublicUrl
{
    public const FRAMEWORK_PREFIXES = [
        'admin', 'api', 'build', 'filament', 'livewire', 'sanctum', 'storage', 'up',
        'login', 'logout', 'password-reset', 'profile', 'podglad', 'uzytkownicy', 'wiadomosci', 'media', 'media-panel',
    ];

    public const RESERVED_SLUGS = [
        'aktualnosci', 'artykuly', 'assets', 'dokumenty', 'kariera', 'kontakt', 'o-kancelarii', 'specjalizacje',
        'zespol', ...self::FRAMEWORK_PREFIXES,
    ];

    public static function isSlug(mixed $slug): bool
    {
        return is_string($slug) && strlen($slug) <= 191 && preg_match('/\A[a-z0-9-]+\z/', $slug) === 1;
    }

    public static function isArticleSlug(mixed $slug): bool
    {
        return self::isSlug($slug) && ! in_array($slug, self::RESERVED_SLUGS, true);
    }

    public static function availableSlug(string $title, string $model, array $reserved = []): string
    {
        $base = Str::slug($title, '-', 'pl') ?: 'wpis';
        for ($number = 1; ; $number++) {
            $suffix = $number === 1 ? '' : '-'.$number;
            $slug = substr($base, 0, 191 - strlen($suffix)).$suffix;
            if (! in_array($slug, $reserved, true) && ! $model::withTrashed()->where('slug', $slug)->exists()) {
                return $slug;
            }
        }
    }

    public static function path(Request $request): string
    {
        return rtrim(preg_replace('#/+#', '/', $request->getPathInfo()), '/').'/';
    }

    public static function redirect(Request $request, string $path): Response
    {
        $query = $request->server->get('QUERY_STRING', '');

        return response('', 301, ['Location' => $path.($query === '' ? '' : '?'.$query)]);
    }
}
