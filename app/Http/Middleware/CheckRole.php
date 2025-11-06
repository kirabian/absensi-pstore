<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  ...$roles  // Ini akan menangkap semua role (cth: 'admin', 'security')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Ambil role user yang sedang login
        $userRole = Auth::user()->role; // 'admin', 'security', dll.

        // 3. Cek apakah role user ada di daftar yang diizinkan
        foreach ($roles as $role) {
            if ($userRole == $role) {
                // Jika cocok, izinkan request
                return $next($request);
            }
        }

        // 4. Jika tidak ada yang cocok, tolak akses
        return abort(403, 'Akses Ditolak. Anda tidak memiliki hak.');
    }
}
