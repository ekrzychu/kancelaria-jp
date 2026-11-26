<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PublicUrl
{
    public const FRAMEWORK_PREFIXES = [
        'admin', 'api', 'build', 'filament', 'livewire', 'sanctum', 'storage', 'up',
    ];

    public const RESERVED_SLUGS = [
        'aktualnosci', 'artykuly', 'assets', 'dokumenty', 'kariera', 'kontakt', 'o-kancelarii', 'specjalizacje',
        'zespol', ...self::FRAMEWORK_PREFIXES,
    ];

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
