<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        // Cek jika user login TAPI is_active == 0 (false)
        if (Auth::check() && !Auth::user()->is_active) {
            
            // Logout paksa
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect ke login dengan pesan error
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi Admin.');
        }

        return $next($request);
    }
}