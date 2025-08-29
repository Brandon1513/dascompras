<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if (!$request->expectsJson()) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu cuenta está inactiva. Contacta al administrador.']);
            }

            return response()->json(['message' => 'Cuenta inactiva'], 403);
        }

        return $next($request);
    }
}
