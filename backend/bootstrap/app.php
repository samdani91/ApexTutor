<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Inject auth_token cookie as Bearer header before Sanctum processes the request
        $middleware->prependToGroup('api', \App\Http\Middleware\CookieTokenMiddleware::class);
        $middleware->alias([
            'role'          => \App\Http\Middleware\EnsureRole::class,
            'active.user'   => \App\Http\Middleware\EnsureUserIsActive::class,
            'verified'      => \App\Http\Middleware\EnsureVerified::class,
            'log.admin'     => \App\Http\Middleware\LogAdminActivity::class,
            'auth.optional' => \App\Http\Middleware\OptionalAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Hide raw DB/PDO errors from API consumers
        $dbHandler = function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service temporarily unavailable. Please try again later.',
                ], 503);
            }
        };
        $exceptions->render(fn (\Illuminate\Database\QueryException $e, $req) => $dbHandler($e, $req));
        $exceptions->render(fn (\PDOException $e, $req) => $dbHandler($e, $req));
    })->create();
