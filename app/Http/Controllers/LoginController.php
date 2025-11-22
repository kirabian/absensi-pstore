<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginId = $request->login_id;
        $password = $request->password;

        // Cari user berdasarkan login_id
        $user = User::where('login_id', $loginId)->first();

        if (!$user) {
            return back()->withErrors([
                'login_id' => 'ID Login tidak ditemukan.',
            ])->withInput();
        }

        // Check password (case insensitive)
        if (Hash::check(strtolower($password), $user->password) || 
            Hash::check(strtoupper($password), $user->password) ||
            Hash::check($password, $user->password)) {
            
            Auth::login($user, $request->remember);
            
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'password' => 'Password yang dimasukkan salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}