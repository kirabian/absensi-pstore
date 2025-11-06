<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User; // <-- Pastikan User model di-import

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil untuk user yang sedang login.
     */
    public function edit()
    {
        $user = Auth::user(); // Ambil data user yang sedang login
        return view('profile.edit', compact('user'));
    }

    /**
     * Update data profil user yang sedang login.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */ // <-- PERBAIKAN DI SINI
        $user = Auth::user(); // Ambil user yang sedang login

        // Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Unik, kecuali diri sendiri
            ],
            'password' => 'nullable|string|min:8|confirmed', // Boleh kosong
        ]);

        // Siapkan data
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // HANYA update password JIKA diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data); // <-- Garis merahnya akan hilang

        return redirect()->route('profile.edit')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
