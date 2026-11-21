<?php

use App\Http\Middleware\CekAkunAktif;
use App\Http\Middleware\CekPeran;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin', 'admin/*', 'pelanggan', 'pelanggan/*')) {
                return route('admin.masuk');
            }

            return route('masuk');
        });

        $middleware->alias([
            'akun.aktif'  => CekAkunAktif::class,
            'peran'       => CekPeran::class,
            'tanpa-cache' => TanpaCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();