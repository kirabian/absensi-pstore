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
use Illuminate\Support\Facades\Storage; // <-- Tambah ini untuk upload foto

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
        $query = User::with(['division', 'branch']);

        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        }

        // Ganti get() dengan paginate(10) agar halaman tidak berat
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
        // 1. Validasi diperlengkap
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'login_id' => 'required|string|unique:users', // <-- Wajib unik
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            
            // Field tambahan (Nullable agar tidak error jika kosong)
            'hire_date' => 'nullable|date',
            'whatsapp' => 'nullable|string|max:20',
            'linkedin' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|string',
            'tiktok' => 'nullable|string',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        $data = $request->except(['password', 'profile_photo_path']);
        $user = Auth::user();

        // Logika Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }
        if ($request->role == 'admin' && $request->branch_id == null) {
            $data['branch_id'] = null;
        }

        // Hash Password
        $data['password'] = Hash::make($request->password);
        
        // Generate QR
        $data['qr_code_value'] = (string) Str::uuid();

        // 2. Handle Upload Foto
        if ($request->hasFile('profile_photo_path')) {
            // Simpan ke folder: storage/app/public/profile-photos
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'login_id' => ['required', Rule::unique('users')->ignore($user->id)], // <-- Unik ignore diri sendiri
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,audit,leader,security,user_biasa',
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            
            'hire_date' => 'nullable|date',
            'whatsapp' => 'nullable|string|max:20',
            'linkedin' => 'nullable|url',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['password', 'profile_photo_path']);

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

        // 3. Handle Ganti Foto
        if ($request->hasFile('profile_photo_path')) {
            // Hapus foto lama jika ada (opsional, biar hemat storage)
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        $user->update($data);

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
            // Hapus foto dari storage saat user dihapus
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->delete();
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Gagal menghapus user.');
        }
    }
    
    public function show(User $user)
    {
         return redirect()->route('users.edit', $user->id);
    }
}