<?php

namespace App\Http\Middleware;

use App\Models\LogAktivitas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanAkunAktif
{
    // Dicek tiap request supaya akun yang dinonaktifkan langsung terlempar dari sesi berjalan.
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin');

        if ($user && ! $user->aktif) {
            LogAktivitas::catat('admin.sesi_dihentikan', pelaku: $user, detail: ['alasan' => 'akun dinonaktifkan']);

            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.masuk')
                ->withErrors(['email' => 'Akun ini dinonaktifkan. Hubungi admin.']);
        }

        return $next($request);
    }
}
