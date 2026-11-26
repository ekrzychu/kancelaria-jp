<?php

use App\Http\Middleware\SiteHostBoundary;
use App\Http\Middleware\RequireCurrentAccountSession;
use App\Support\SiteHosts;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(using: static function (): void {
        Route::domain(config('hosts.public'))->group(base_path('routes/web.php'));
    })
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->append(SiteHostBoundary::class);
        $middleware->web(append: [RequireCurrentAccountSession::class]);
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        $exceptions->render(static fn (PDOException $exception) => response(
            'Usługa jest tymczasowo niedostępna.',
            503,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        ));
        $exceptions->respond(static function (Response $response, Throwable $exception, Request $request): Response {
            // An invalid Host header may itself have raised the exception.
            try {
                return SiteHosts::isAdmin($request) ? SiteHostBoundary::protect($response) : $response;
            } catch (\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException) {
                return $response;
            }
        });
    })
    ->create();
