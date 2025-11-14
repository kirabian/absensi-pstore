<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        // Middleware untuk authorization
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            
            // Cek apakah user memiliki role yang diizinkan
            if (!in_array($user->role, ['admin', 'audit'])) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
            }
            
            return $next($request);
        });
    }

    /**
     * Menampilkan daftar semua user (sesuai cabang admin).
     */
    public function index()
    {
        $user = Auth::user();
        $query = User::with(['division', 'branch']);

        // Filter user berdasarkan cabang
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

        // Filter data dropdown berdasarkan cabang
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $data = $request->all();
        $user = Auth::user();

        // Paksa isi branch_id jika Admin Cabang
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
            'branch_id' => $data['branch_id'],
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

        // Authorization: Cek apakah user bisa mengedit user ini
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit user ini.');
            }
        }

        // Filter data dropdown berdasarkan cabang
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
        // Authorization: Cek apakah user bisa mengupdate user ini
        $auth_user = Auth::user();
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk mengupdate user ini.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $data = $request->all();

        // Paksa isi branch_id jika Admin Cabang
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
            unset($data['password']);
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
        // Authorization: Cek apakah user bisa menghapus user ini
        $auth_user = Auth::user();
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk menghapus user ini.');
            }
        }

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
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail user.
     */
    public function show(User $user)
    {
        // Authorization: Cek apakah user bisa melihat user ini
        $auth_user = Auth::user();
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk melihat user ini.');
            }
        }

        return redirect()->route('users.edit', $user->id);
    }
}