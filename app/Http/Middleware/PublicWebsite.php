<?php

namespace App\Http\Middleware;

use App\Support\PublicUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PublicWebsite
{
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->getPathInfo();
        $collapsed = preg_replace('#/+#', '/', $requested);
        $prefix = explode('/', ltrim($collapsed, '/'), 2)[0];

        if (in_array($prefix, PublicUrl::FRAMEWORK_PREFIXES, true)
            || preg_match('#^/artykuly/(?:dodaj|[^/]+/edytuj)/?$#', $collapsed)) {
            return $this->withHeaders($next($request));
        }

        if ($request->isMethod('GET') && ! str_ends_with($requested, '/') && pathinfo($requested, PATHINFO_EXTENSION) === '') {
            return $this->withHeaders(PublicUrl::redirect($request, PublicUrl::path($request)));
        }

        // The former front controller folded repeated slashes before matching a route.
        if ($collapsed !== $requested) {
            $query = $request->server->get('QUERY_STRING', '');
            $request = $request->duplicate(server: array_replace($request->server->all(), [
                'REQUEST_URI' => $request->getBaseUrl().$collapsed.($query === '' ? '' : '?'.$query),
            ]));
        }

        return $this->withHeaders($next($request));
    }

    private function withHeaders(Response $response): Response
    {
        $response->headers->add([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ]);

        return $response;
    }
}
