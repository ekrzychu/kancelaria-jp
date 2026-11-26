<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class RequireCurrentAccountSession
{
    public const SESSION_KEY = 'panel.account_version';

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');
        $user = $guard->user();

        if ($user instanceof User && (
            ! $user->is_active ||
            $request->session()->get(self::SESSION_KEY) !== $user->session_version
        )) {
            $guard->logoutCurrentDevice();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403);
        }

        return $next($request);
    }
}
