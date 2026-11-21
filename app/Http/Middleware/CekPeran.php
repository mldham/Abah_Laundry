<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peranDiizinkan): Response
    {
        $user = Auth::guard('admin')->user();

        if (! $user || ! in_array($user->peran, $peranDiizinkan, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman atau aksi ini.');
        }

        return $next($request);
    }
}