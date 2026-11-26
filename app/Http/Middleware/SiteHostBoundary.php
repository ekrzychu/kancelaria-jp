<?php

namespace App\Http\Middleware;

use App\Support\SiteHosts;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SiteHostBoundary
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $admin = SiteHosts::isAdmin($request);
        abort_unless($admin || $host === config('hosts.public'), 400);

        $path = rawurldecode($request->getPathInfo());
        // This phase has no file exports, imports, unsigned storage or temporary uploads.
        abort_if(preg_match('#^/+(?:filament|storage)(?:/+|$)#i', $path), 404);

        // Livewire also registers package routes outside the panel's route group.
        if (preg_match('#^/+livewire(?:-[a-f0-9]+)?(?:/+|$)#i', $path)) {
            abort_unless($admin, 404);
            abort_if(preg_match('#/+(?:upload-file|preview-file)(?:/+|$)#i', $path), 404);
        }

        if (! $admin) {
            return app(PublicWebsite::class)->handle($request, $next);
        }

        $origin = SiteHosts::adminOrigin();
        $scheme = parse_url($origin, PHP_URL_SCHEME);
        $port = parse_url($origin, PHP_URL_PORT) ?? ($scheme === 'https' ? 443 : 80);
        abort_unless($request->getScheme() === $scheme && $request->getPort() === $port, 400);
        if (! $request->isMethodSafe() && $request->headers->has('Origin')) {
            abort_unless($request->headers->get('Origin') === $origin, 403);
        }

        return self::protect($next($request));
    }

    public static function protect(Response $response): Response
    {
        $response->headers->add([
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'same-origin',
        ]);

        return $response;
    }
}
