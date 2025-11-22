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
            $query->whereIn('branch_id', $auditBranchIds);
        }

        $users = $query->latest()->paginate(10); 
        
        // PERBAIKAN NAMA FILE VIEW: 'users.user_index'
        return view('users.user_index', compact('users'));
    }

    /**
     * Form tambah user.
     */
    public function create()
    {
        $user = Auth::user();

        // A. Admin Cabang (Hanya bisa tambah di cabangnya)
        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $divisions = Division::where('branch_id', $user->branch_id)->get();
        } 
        // B. Audit (Hanya bisa tambah di wilayah auditnya)
        elseif ($user->role == 'audit') {
            // Ambil cabang dari relasi pivot (wilayah audit dia)
            $branches = $user->branches; 
            
            // Ambil divisi yang ada di cabang-cabang tersebut
            $branchIds = $branches->pluck('id');
            $divisions = Division::whereIn('branch_id', $branchIds)->get();
        } 
        // C. Super Admin (Bisa semua)
        else {
            $branches = Branch::all();
            $divisions = Division::all();
        }

        // PERBAIKAN NAMA FILE VIEW: 'users.user_create'
        return view('users.user_create', compact('divisions', 'branches'));
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
            
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
        ]);

        // VALIDASI KHUSUS AUDIT: Pastikan branch_id yang dipilih ada di wilayahnya
        if ($user->role == 'audit') {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($request->branch_id && !in_array($request->branch_id, $allowedBranchIds)) {
                return back()->withErrors(['branch_id' => 'Anda tidak memiliki hak akses untuk menambahkan user di cabang ini.'])->withInput();
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

        // Force Branch ID untuk Admin Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        // Null Branch ID untuk Super Admin (jika role admin dipilih)
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
        // Jika membuat user Audit baru -> Simpan Multi Branches
        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }
        // Simpan Multi Divisions untuk semua role
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
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }
        }
        // Admin Cabang hanya boleh edit user di cabangnya
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }

        // FILTER DROPDOWN
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $branches = Branch::where('id', $auth_user->branch_id)->get();
            $divisions = Division::where('branch_id', $auth_user->branch_id)->get();
        } 
        elseif ($auth_user->role == 'audit') {
            $branches = $auth_user->branches; 
            $branchIds = $branches->pluck('id');
            $divisions = Division::whereIn('branch_id', $branchIds)->get();
        } else {
            $branches = Branch::all();
            $divisions = Division::all();
        }

        // PERBAIKAN NAMA FILE VIEW: 'users.user_edit'
        return view('users.user_edit', compact('user', 'divisions', 'branches'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();
        
        // PROTEKSI UPDATE
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403);
            
            // Cek jika branch diganti ke luar wilayah audit
            if ($request->branch_id && !in_array($request->branch_id, $allowedBranchIds)) {
                return back()->withErrors(['branch_id' => 'Cabang di luar wilayah audit.']);
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
        ]);

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
            $data['division_id'] = $request->multi_divisions[0];
        } else {
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

        $user->update($data);

        // Sync Pivot
        if ($request->role == 'audit') {
            $user->branches()->sync($request->multi_branches ?? []);
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

    public function destroy(User $user)
    {
        $auth_user = Auth::user();
        
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403);
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