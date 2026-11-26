<?php

use App\Http\Middleware\PublicWebsite;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(using: static function (): void {
        Route::group([], base_path('routes/web.php'));
    })
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->prepend(PublicWebsite::class);
    })
    ->withExceptions()
    ->create();
