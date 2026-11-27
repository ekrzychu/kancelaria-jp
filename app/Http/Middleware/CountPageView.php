<?php

namespace App\Http\Middleware;

use App\Analytics\PageViews;
use App\Support\PublicUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class CountPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('analytics.enabled') || ! $request->isMethod('GET') || $response->getStatusCode() !== 200
            || ! str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        try {
            $page = $this->pageKey($request, $response);
            if ($page !== null && $this->isNavigation($request)) {
                PageViews::increment(PageViews::today()->toDateString(), $page, $this->source($request));
            }
        } catch (Throwable) {
            // A failed counter must not change the public response or log request data.
        }

        return $response;
    }

    private function isNavigation(Request $request): bool
    {
        $agent = $request->userAgent() ?? '';
        $purpose = implode(' ', array_map($request->header(...), ['Sec-Purpose', 'Purpose', 'X-Purpose', 'X-Moz']));

        return $agent !== '' && ! preg_match('/bot|spider|crawler|slurp|headless|facebookexternalhit|meta-external|google-inspectiontool|googleother|whatsapp|preview|monitor|curl|wget|python|go-http-client|undici|^node$/i', $agent)
            && ! preg_match('/prefetch|prerender|preview/i', $purpose)
            && in_array($request->header('Sec-Fetch-Dest'), [null, 'document'], true)
            && in_array($request->header('Sec-Fetch-Mode'), [null, 'navigate'], true)
            && ! $request->headers->has('X-Livewire')
            && ! $request->headers->has('X-Requested-With')
            && ! $request->headers->has('HX-Request')
            && ! $request->headers->has('X-Inertia')
            && ! $request->headers->has('Turbo-Frame');
    }

    private function pageKey(Request $request, Response $response): ?string
    {
        $original = $response instanceof LaravelResponse ? $response->getOriginalContent() : null;
        if (! $original instanceof View) {
            return null;
        }

        $route = $request->route()?->getName();
        if (isset(PageViews::PAGES[$route])) {
            return $route;
        }

        $type = match ($route) {
            'articles.show' => 'article',
            'services.show' => 'service',
            'documents.show' => 'document',
            default => null,
        };

        $slug = $type === null ? null : ($original->getData()[$type]['slug'] ?? null);

        return PublicUrl::isSlug($slug) ? $type.':'.$slug : null;
    }

    private function source(Request $request): string
    {
        $referer = $request->headers->get('Referer', '');
        $scheme = strtolower((string) parse_url($referer, PHP_URL_SCHEME));
        $host = strtolower(rtrim((string) parse_url($referer, PHP_URL_HOST), '.'));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return 'unknown';
        }

        if (in_array($host, [config('hosts.public'), config('hosts.admin')], true)) {
            return 'internal';
        }

        if (preg_match('/(?:^|\.)(?:google\.(?:com|[a-z]{2}|com\.[a-z]{2}|co\.[a-z]{2})|bing\.com|duckduckgo\.com|search\.yahoo\.com|ecosia\.org|brave\.com)$/', $host)) {
            return 'search';
        }

        if (preg_match('/(?:^|\.)(?:facebook\.com|instagram\.com|linkedin\.com|lnkd\.in|x\.com|twitter\.com|t\.co|youtube\.com|reddit\.com|tiktok\.com)$/', $host)) {
            return 'social';
        }

        return 'external';
    }
}
