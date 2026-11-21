<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PeriksaPeranAdmin
{
    public function handle(Request $request, Closure $next, string ...$peranDiizinkan): Response
    {
        $user = Auth::guard('admin')->user();

        if (! $user) {
            return redirect()->route('admin.masuk');
        }

        // Cek status aktif jika kolom 'aktif' atau 'is_active' tersedia di tabel
        if ((isset($user->aktif) && ! $user->aktif) || (isset($user->is_active) && ! $user->is_active)) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.masuk')->withErrors(['email' => 'Akun Anda dinonaktifkan.']);
        }

        // Cek peran (admin / operator) jika parameter peran diberikan
        $peranUser = $user->peran ?? $user->role ?? null;
        if (! empty($peranDiizinkan) && $peranUser && ! in_array($peranUser, $peranDiizinkan)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}