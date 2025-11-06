<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // Penting untuk validasi update

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user.
     */
    public function index()
    {
        $users = User::with('division')->latest()->get(); // 'with' untuk eager loading
        return view('users.user_index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $divisions = Division::all(); // Ambil semua divisi untuk dropdown
        return view('users.user_create', compact('divisions'));
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,security,user_biasa',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password
            'role' => $request->role,
            'division_id' => ($request->role == 'admin' || $request->role == 'security') ? null : $request->division_id, // Admin/Security tidak punya divisi
            'qr_code_value' => (string) Str::uuid(), // Buat QR Code unik
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        $divisions = Division::all();
        return view('users.user_edit', compact('user', 'divisions'));
    }

    /**
     * Update data user di database.
     */
    public function update(Request $request, User $user)
    {
        // Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Unik, kecuali untuk diri sendiri
            ],
            'password' => 'nullable|string|min:8|confirmed', // Password boleh kosong
            'role' => 'required|string|in:admin,audit,security,user_biasa',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        // Siapkan data untuk diupdate
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'division_id' => ($request->role == 'admin' || $request->role == 'security') ? null : $request->division_id,
        ];

        // HANYA update password JIKA diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        // PENTING: Jangan biarkan admin menghapus dirinya sendiri
        if ($user->id == auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal menghapus user. Mungkin user masih terkait data lain.');
        }
    }

    // Kita tidak pakai 'show', jadi arahkan saja ke 'edit'
    public function show(User $user)
    {
        return redirect()->route('users.edit', $user->id);
    }
}
