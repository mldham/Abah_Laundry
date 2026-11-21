<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanAkunAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (! $user || ! $user->aktif) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.masuk')
                ->withErrors(['email' => 'Akun admin tidak aktif.']);
        }

        return $next($request);
    }
}