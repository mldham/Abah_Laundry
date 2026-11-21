<?php

use App\Http\Middleware\PastikanAkunAktif;
use App\Http\Middleware\PastikanPeran;
use App\Http\Middleware\TanpaCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'akun.aktif'  => PastikanAkunAktif::class,
            'peran'       => PastikanPeran::class,
            'tanpa-cache' => TanpaCache::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.masuk');
            }

            return route('masuk');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();