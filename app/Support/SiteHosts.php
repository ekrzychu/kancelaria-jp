<?php

namespace App\Support;

use Illuminate\Http\Request;
use LogicException;

final class SiteHosts
{
    public static function validate(): void
    {
        $local = app()->environment('local', 'testing');

        foreach (['public', 'admin'] as $site) {
            $host = config("hosts.$site");
            $url = config("hosts.{$site}_url");
            if ($local && $site === 'admin' && $host === null && $url === null) {
                continue;
            }

            $parts = is_string($url) ? parse_url($url) : false;
            if (! is_string($host)
                || ! preg_match('/\A[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?\z/', $host)
                || ! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
                || str_contains($host, '..')
                || ! $parts
                || ($parts['host'] ?? null) !== $host
                || ! in_array($parts['scheme'] ?? null, $local ? ['http', 'https'] : ['https'], true)
                || array_diff(array_keys($parts), ['scheme', 'host', 'port', 'path'])
                || ! in_array($parts['path'] ?? '', ['', '/'], true)
                || ! filter_var($url, FILTER_VALIDATE_URL)) {
                throw new LogicException("Set a valid, matching hostname and origin for the {$site} site.");
            }
        }

        if (config('hosts.public') === config('hosts.admin')) {
            throw new LogicException('Use distinct public and administrative hostnames.');
        }
    }

    public static function isAdmin(Request $request): bool
    {
        return $request->getHost() === config('hosts.admin');
    }

    public static function adminOrigin(): string
    {
        return rtrim((string) config('hosts.admin_url'), '/');
    }
}
