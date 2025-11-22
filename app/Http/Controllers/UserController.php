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
        // Middleware: Hanya Admin dan Audit yang boleh akses manajemen user
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
        
        // Load relasi single dan multi (pivot) untuk efisiensi query
        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        // Jika Admin Cabang, filter hanya user di cabangnya
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
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

        // Filter dropdown sesuai hak akses
        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $divisions = Division::where('branch_id', $user->branch_id)->get();
        } else {
            $branches = Branch::all();
            $divisions = Division::all();
        }

        return view('users.user_create', compact('divisions', 'branches'));
    }

    /**
     * Simpan user baru & relasi pivot.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            
            // Validasi Single Selection (Homebase)
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',

            // Validasi Multi Selection (Pivot)
            'multi_branches' => 'nullable|array',
            'multi_branches.*' => 'exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_divisions.*' => 'exists:divisions,id',
            
            // Validasi Data Tambahan
            'whatsapp' => 'nullable|string|max:20',
            'linkedin' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|string',
            'tiktok' => 'nullable|string',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Siapkan Data User Utama (Exclude input array pivot)
        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);
        $user = Auth::user();

        // Logika Otomatis Branch untuk Admin Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        // Super Admin tidak punya homebase branch
        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
        }

        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();

        // Handle Upload Foto
        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // 3. Create User
        $newUser = User::create($data);

        // 4. Handle Relasi Pivot (Many-to-Many)
        
        // Jika Role Audit -> Simpan Cabang Banyak
        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }

        // Jika Role Leader -> Simpan Divisi Banyak
        if ($request->role == 'leader' && $request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        // Load relasi pivot agar terpilih otomatis di form edit
        $user->load(['branches', 'divisions']);

        $auth_user = Auth::user();

        // Proteksi: Admin Cabang tidak boleh edit user cabang lain
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit user ini.');
            }
        }

        // Filter Dropdown
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
     * Update user & relasi pivot.
     */
    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();
        
        // Proteksi Update
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses update.');
            }
        }

        // 1. Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            
            // Validasi Multi Selection
            'multi_branches' => 'nullable|array',
            'multi_divisions' => 'nullable|array',

            'whatsapp' => 'nullable|string|max:20',
            'linkedin' => 'nullable|url',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Siapkan Data Update (Exclude array pivot)
        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // Logika Cabang
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $data['branch_id'] = $auth_user->branch_id;
        }
        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
        }

        // Update Password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle Ganti Foto
        if ($request->hasFile('profile_photo_path')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // 3. Update Data Utama
        $user->update($data);

        // 4. Update Relasi Pivot (Sync / Detach)
        
        // Jika Role Audit -> Sync Cabang, Hapus Divisi
        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
            $user->divisions()->detach(); 
        } 
        // Jika Role Leader -> Sync Divisi, Hapus Cabang
        elseif ($request->role == 'leader') {
            $user->divisions()->sync($request->multi_divisions ?? []);
            $user->branches()->detach();
        } 
        // Role Lain (User Biasa/Security/Admin) -> Hapus semua pivot
        else {
            $user->branches()->detach();
            $user->divisions()->detach();
        }

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $auth_user = Auth::user();
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Akses Ditolak.');
            }
        }

        if ($user->id == auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        try {
            // Hapus foto dari storage
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Detach Relasi Pivot sebelum delete (Best Practice)
            $user->branches()->detach();
            $user->divisions()->detach();

            $user->delete();
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
    
    public function show(User $user)
    {
         return redirect()->route('users.edit', $user->id);
    }
}