<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // HandleCors sudah otomatis diload oleh Laravel
        // Kita pakai Bearer token (Sanctum), bukan cookie/session
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Kalau unauthenticated (token tidak valid/tidak ada),
        // kembalikan JSON 401 — bukan redirect ke route "login" yang tidak ada
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], 401);
            }
        });
    })->create();
