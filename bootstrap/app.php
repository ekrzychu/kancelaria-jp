<?php

use App\Http\Controllers\PublicPages;
use App\Http\Middleware\RequireCurrentAccountSession;
use App\Http\Middleware\SiteHostBoundary;
use App\Support\SiteHosts;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(using: static function (): void {
        Route::domain(config('hosts.public'))->group(base_path('routes/web.php'));
    })
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->append(SiteHostBoundary::class);
        $middleware->web(append: [RequireCurrentAccountSession::class]);
        $middleware->trimStrings(except: ['message']);
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        $exceptions->dontReportWhen(static function (Throwable $exception): bool {
            // QueryException can contain interpolated submissions or panel search terms.
            do {
                if ($exception instanceof QueryException && str_contains($exception->getSql(), 'contact_messages')) {
                    return true;
                }
            } while ($exception = $exception->getPrevious());

            return false;
        });
        $exceptions->render(static fn (PDOException $exception) => response(
            'Usługa jest tymczasowo niedostępna.',
            503,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        ));
        $exceptions->render(static function (HttpException $exception, Request $request): ?Response {
            if ($exception->getStatusCode() === 419 && $request->routeIs('contact.submit')) {
                return app(PublicPages::class)->contact(419, ['formError' => 'Sesja formularza wygasła. Otwórz stronę Kontakt ponownie i spróbuj jeszcze raz.']);
            }

            return null;
        });
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
