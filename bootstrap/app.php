<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(remove: [
            StartSession::class,
            ValidateCsrfToken::class,
            ShareErrorsFromSession::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })
    ->create();
