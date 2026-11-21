<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanPeran
{
    // Dipakai setelah auth:admin, contoh: peran:admin atau peran:admin,operator.
    // Penolakan dicatat ke log audit oleh penanganan 403 di bootstrap/app.php.
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user('admin');

        abort_unless($user && $user->punyaPeran(...$peran), 403);

        return $next($request);
    }
}
