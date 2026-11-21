<?php

use App\Http\Middleware\PastikanAkunAktif;
use App\Http\Middleware\PastikanPeran;
use App\Http\Middleware\TanpaCache;
use App\Models\LogAktivitas;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'akun.aktif'  => PastikanAkunAktif::class,
            'peran'       => PastikanPeran::class,
            'tanpa-cache' => TanpaCache::class,
        ]);

        // Belum masuk lalu buka Cek Pesanan: ke halaman masuk, lalu kembali ke halaman tadi.
        // Halaman di bawah /admin punya pintu masuk sendiri.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.masuk')
            : route('masuk'));

        // Sudah masuk lalu buka halaman masuk atau daftar: langsung ke halaman kerjanya.
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.order.index')
            : route('order.index'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Semua 403 di area admin (abort dari middleware peran maupun penolakan policy) tercatat di satu tempat.
        // Tanpa return, halaman 403 tetap dibuat oleh penanganan bawaan.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && $request->is('admin', 'admin/*')) {
                LogAktivitas::catat('akses.ditolak', detail: [
                    'metode' => $request->method(),
                    'url'    => $request->path(),
                ]);
            }
        });
    })->create();
