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
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        // Middleware: Hanya Admin dan Audit yang boleh akses
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'audit'])) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
            }
            return $next($request);
        });
    }

    /**
     * Menampilkan daftar user.
     */
    public function index()
    {
        $user = Auth::user();

        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        // 1. Admin Cabang -> Filter user di cabangnya saja
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        }
        // 2. Audit -> Filter user di SEMUA cabang wilayah auditnya
        elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            // Tampilkan user yang homebase-nya ada di wilayah audit
            $query->whereIn('branch_id', $auditBranchIds);
        }

        $users = $query->latest()->paginate(10);

        return view('users.user_index', compact('users'));
    }

    /**
     * Form tambah user.
     */
    public function create()
    {
        $user = Auth::user();

        // 1. Ambil Data Cabang (Sesuai Logika Role)
        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif ($user->role == 'audit') {
            $branches = $user->branches;
            $allowedRoles = ['audit', 'leader', 'security', 'user_biasa'];
        } else { // Super Admin
            $branches = Branch::all();
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        // 2. Ambil Data Divisi (GLOBAL - SEMUA DIVISI MUNCUL)
        // Karena divisi sekarang master data, ambil saja semua.
        $divisions = Division::all();

        return view('users.user_create', compact('divisions', 'branches', 'allowedRoles'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',

            // Aturan Branch: Wajib diisi KECUALI role yang dibuat adalah 'admin' (Super Admin)
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',

            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        // VALIDASI TAMBAHAN: Cek apakah role yang dipilih diizinkan
        $allowedRoles = $this->getAllowedRoles($user);
        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'Anda tidak memiliki hak akses untuk membuat user dengan role ini.'])->withInput();
        }

        // VALIDASI KHUSUS AUDIT: Pastikan branch_id yang dipilih ada di wilayahnya
        if ($user->role == 'audit') {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();

            // Jika branch_id dipilih, pastikan ID-nya ada di allowedBranchIds
            if ($request->branch_id && !in_array($request->branch_id, $allowedBranchIds)) {
                return back()->withErrors(['branch_id' => 'Anda tidak memiliki hak akses untuk menambahkan user di cabang ini.'])->withInput();
            }

            // Validasi multi_branches juga harus dalam wilayah audit
            if ($request->has('multi_branches')) {
                foreach ($request->multi_branches as $branchId) {
                    if (!in_array($branchId, $allowedBranchIds)) {
                        return back()->withErrors(['multi_branches' => 'Salah satu cabang yang dipilih di luar wilayah audit Anda.'])->withInput();
                    }
                }
            }
        }

        // VALIDASI KHUSUS ADMIN CABANG: Pastikan tidak membuat user di cabang lain
        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($request->branch_id && $request->branch_id != $user->branch_id) {
                return back()->withErrors(['branch_id' => 'Anda hanya dapat menambahkan user di cabang Anda sendiri.'])->withInput();
            }
        }

        // 2. Siapkan Data
        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Fix Division ID (Ambil yg pertama dipilih dari multi select)
        if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
            $data['division_id'] = $request->multi_divisions[0];
        } else {
            $data['division_id'] = null;
        }

        // Force Branch ID untuk Admin Cabang (Override input user)
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }

        // Null Branch ID jika membuat Super Admin
        if ($request->role == 'admin') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();
        $data['hire_date'] = now(); // Set hire_date ke tanggal sekarang

        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // 3. Create User
        $newUser = User::create($data);

        // 4. Sync Pivot Tables
        // Jika membuat user Audit baru -> Simpan Multi Branches
        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }

        // Simpan Multi Divisions untuk semua role (termasuk Leader & User Biasa)
        if ($request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        $user->load(['branches', 'divisions']);
        $auth_user = Auth::user();

        // PROTEKSI EDIT: Audit hanya boleh edit user di wilayahnya
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            // Cek apakah user yg diedit ada di wilayah audit
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }
        }
        // Admin Cabang hanya boleh edit user di cabangnya
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'User ini di luar cabang Anda.');
            }
        }

        // --- FILTER DATA UNTUK DROPDOWN ---

        // 1. Ambil Data Cabang (Sesuai Role)
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $branches = Branch::where('id', $auth_user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif ($auth_user->role == 'audit') {
            $branches = $auth_user->branches;
            $allowedRoles = ['audit', 'leader', 'security', 'user_biasa'];
        } else {
            // Super Admin
            $branches = Branch::all();
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        // 2. Ambil Data Divisi (GLOBAL - UNTUK SEMUA ROLE)
        // Karena divisi sudah tidak punya branch_id, panggil semua saja.
        $divisions = Division::all();

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();

        // PROTEKSI UPDATE
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'User ini di luar cabang Anda.');
            }
        }

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }

            // Cek jika branch diganti ke luar wilayah audit
            if ($request->branch_id && !in_array($request->branch_id, $allowedBranchIds)) {
                return back()->withErrors(['branch_id' => 'Cabang di luar wilayah audit.'])->withInput();
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'multi_branches' => 'nullable|array',
            'multi_divisions' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        // VALIDASI TAMBAHAN: Cek apakah role yang dipilih diizinkan
        $allowedRoles = $this->getAllowedRoles($auth_user);
        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'Anda tidak memiliki hak akses untuk mengubah user menjadi role ini.'])->withInput();
        }

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
            $data['division_id'] = $request->multi_divisions[0];
        } else {
            $data['division_id'] = null;
        }

        // Force Branch ID untuk Admin Cabang (Override input user)
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $data['branch_id'] = $auth_user->branch_id;
        }

        // Null Branch ID jika mengubah menjadi Super Admin
        if ($request->role == 'admin') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo_path')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        $user->update($data);

        // Sync Pivot
        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
            // Untuk audit, tidak perlu divisions
            $user->divisions()->detach();
        } elseif ($request->role == 'leader') {
            $user->divisions()->sync($request->multi_divisions ?? []);
            $user->branches()->detach();
        } else {
            $user->branches()->detach();
            $user->divisions()->sync($request->multi_divisions ?? []);
        }

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        $auth_user = Auth::user();

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'User ini di luar cabang Anda.');
            }
        }

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }
        }

        if ($user->id == auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        try {
            // Hapus foto profil jika ada
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Hapus relasi pivot
            $user->branches()->detach();
            $user->divisions()->detach();

            // Hapus user
            $user->delete();

            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Show user detail (redirect ke edit)
     */
    public function show(User $user)
    {
        return redirect()->route('users.edit', $user->id);
    }

    /**
     * Toggle status user (aktif/nonaktif)
     */
    public function toggleStatus(User $user)
    {
        $auth_user = Auth::user();

        // PROTEKSI: Hanya bisa toggle status user dalam wilayah/cabang sendiri
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'User ini di luar cabang Anda.');
            }
        }

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }
        }

        if ($user->id == auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('users.index')->with('success', "User berhasil $status.");
    }

    /**
     * Reset password user
     */
    public function resetPassword(User $user)
    {
        $auth_user = Auth::user();

        // PROTEKSI: Hanya bisa reset password user dalam wilayah/cabang sendiri
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'User ini di luar cabang Anda.');
            }
        }

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }
        }

        $newPassword = Str::random(8); // Generate random password
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->route('users.index')->with('success', "Password berhasil direset. Password baru: $newPassword");
    }

    /**
     * Helper method untuk mendapatkan roles yang diizinkan
     */
    private function getAllowedRoles($user)
    {
        if ($user->role == 'admin' && $user->branch_id != null) {
            return ['leader', 'security', 'user_biasa'];
        } elseif ($user->role == 'audit') {
            return ['audit', 'leader', 'security', 'user_biasa'];
        } else {
            return ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }
    }

    /**
     * Get user statistics for dashboard
     */
    public function getUserStats()
    {
        $user = Auth::user();
        $query = User::query();

        // Filter berdasarkan role user yang login
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        $totalUsers = $query->count();
        $activeUsers = $query->where('is_active', true)->count();
        $inactiveUsers = $query->where('is_active', false)->count();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers
        ];
    }
}
