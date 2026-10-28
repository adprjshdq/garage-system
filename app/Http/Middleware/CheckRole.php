<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Active account check
        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['login' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.']);
        }

        // Owner always has full access
        if ($user->hasRole('owner')) {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! $user->hasRole($roles)) {
            abort(403, 'Anda tidak memiliki hak akses (Role) untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
