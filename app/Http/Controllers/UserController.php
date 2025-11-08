<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Branch; // <-- IMPORT BRANCH
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // <-- IMPORT AUTH

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user (sesuai cabang admin).
     */
    public function index()
    {
        $user = Auth::user();
        $query = User::with(['division', 'branch']); // Eager load relasi

        // LOGIKA BARU: Filter user berdasarkan cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            // Jika Admin Cabang, hanya tampilkan user dari cabangnya
            $query->where('branch_id', $user->branch_id);
        }
        // Jika Super Admin (branch_id == null), tampilkan semua

        $users = $query->latest()->get();
        return view('users.user_index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $user = Auth::user();

        // LOGIKA BARU: Filter data dropdown berdasarkan cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            // Admin Cabang hanya bisa lihat divisi & cabang miliknya
            $branches = Branch::where('id', $user->branch_id)->get();
            $divisions = Division::where('branch_id', $user->branch_id)->get();
        } else {
            // Super Admin bisa lihat semua
            $branches = Branch::all();
            $divisions = Division::all();
        }

        return view('users.user_create', compact('divisions', 'branches'));
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        // LOGIKA BARU: Tambah validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa', // <-- 'leader' ditambahkan
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id', // <-- Wajib diisi KECUALI dia admin (Super Admin)
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $data = $request->all();
        $user = Auth::user();

        // LOGIKA BARU: Paksa isi branch_id jika Admin Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }

        // Jika role-nya Super Admin, branch_id-nya NULL
        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'branch_id' => $data['branch_id'], // <-- Diambil dari data
            'division_id' => $data['division_id'],
            'qr_code_value' => (string) Str::uuid(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        $auth_user = Auth::user();

        // LOGIKA BARU: Sama seperti 'create'
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $branches = Branch::where('id', $auth_user->branch_id)->get();
            $divisions = Division::where('branch_id', $auth_user->branch_id)->get();
        } else {
            $branches = Branch::all();
            $divisions = Division::all();
        }

        return view('users.user_edit', compact('user', 'divisions', 'branches'));
    }

    /**
     * Update data user di database.
     */
    public function update(Request $request, User $user)
    {
        // LOGIKA BARU: Update validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa', // <-- 'leader' ditambahkan
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id', // <-- Wajib diisi KECUALI dia admin (Super Admin)
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $auth_user = Auth::user();
        $data = $request->all();

        // LOGIKA BARU: Paksa isi branch_id jika Admin Cabang
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $data['branch_id'] = $auth_user->branch_id;
        }

        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
        }

        // HANYA update password JIKA diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // Hapus password dari array jika kosong
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
                ->with('error', 'Gagal menghapus user.');
        }
    }

    public function show(User $user)
    {
        return redirect()->route('users.edit', $user->id);
    }
}
