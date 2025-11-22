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
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'audit'])) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        }

        $users = $query->latest()->paginate(10); 
        return view('users.user_index', compact('users'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $divisions = Division::where('branch_id', $user->branch_id)->get();
        } else {
            $branches = Branch::all();
            $divisions = Division::all();
        }

        return view('users.user_create', compact('divisions', 'branches'));
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            
            // Multi Divisions wajib diisi (kecuali admin mungkin opsional)
            'multi_divisions' => 'nullable|array',
            'multi_divisions.*' => 'exists:divisions,id',
            
            'multi_branches' => 'nullable|array',
            'multi_branches.*' => 'exists:branches,id',
            
            'whatsapp' => 'nullable|string|max:20',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Siapkan Data
        // Kita ambil semua request KECUALI array pivot dan password
        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);
        
        // LOGIKA FIX DIVISION ID NULL:
        // Ambil item pertama dari array multi_divisions untuk jadi division_id utama
        if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
            $data['division_id'] = $request->multi_divisions[0];
        } else {
            $data['division_id'] = null;
        }

        // Logika Branch Admin
        $user = Auth::user();
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
        }

        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();

        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // 3. Create User
        $newUser = User::create($data);

        // 4. Sync Pivot Tables
        
        // Sync Multi Branches (Hanya Audit)
        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }

        // Sync Multi Divisions (SEMUA ROLE)
        // Kita gunakan sync agar data pivot terisi
        if ($request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load(['branches', 'divisions']);
        $auth_user = Auth::user();

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit user ini.');
            }
        }

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $branches = Branch::where('id', $auth_user->branch_id)->get();
            $divisions = Division::where('branch_id', $auth_user->branch_id)->get();
        } else {
            $branches = Branch::all();
            $divisions = Division::all();
        }

        return view('users.user_edit', compact('user', 'divisions', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();
        
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
            
            'multi_branches' => 'nullable|array',
            'multi_divisions' => 'nullable|array',

            'whatsapp' => 'nullable|string|max:20',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Siapkan Data
        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        // LOGIKA FIX DIVISION ID NULL (UPDATE):
        // Ambil item pertama dari array multi_divisions
        if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
            $data['division_id'] = $request->multi_divisions[0];
        } else {
            // Jika dihapus semua, set null
            $data['division_id'] = null;
        }

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $data['branch_id'] = $auth_user->branch_id;
        }
        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
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

        // 3. Update Data Utama
        $user->update($data);

        // 4. Update Relasi Pivot
        
        // Sync Branches (Hanya jika Audit, jika tidak detach semua)
        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
        } else {
            $user->branches()->detach(); // Role lain tidak punya multi branch
        }

        // Sync Divisions (Semua Role punya multi divisions)
        // Penting: Gunakan sync untuk menyimpan semua pilihan
        $user->divisions()->sync($request->multi_divisions ?? []);

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // ... (Kode destroy sama seperti sebelumnya)
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
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

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