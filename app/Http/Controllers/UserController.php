<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Branch;
use Carbon\Carbon;
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
     * Menampilkan daftar user dengan FITUR SEARCH.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        // 1. Filter Role/Cabang (Logika Pembatasan Akses)
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        }
        elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            // Tampilkan user yang homebase-nya ada di wilayah audit
            $query->whereIn('branch_id', $auditBranchIds);
        }

        // 2. LOGIKA SEARCH (BARU)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            
            // Menggunakan group where (closure) agar tidak merusak filter cabang di atas
            // Query: WHERE (branch_filter) AND (name LIKE %..% OR email LIKE %..% OR login_id LIKE %..%)
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        // 3. Ambil Data (Paginate) & Append parameter search ke link pagination
        $users = $query->latest()
            ->paginate(10)
            ->appends(['search' => $request->search]);

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
            'branch_id' => 'required_unless:role,admin|nullable|exists:branches,id',
            'multi_divisions' => 'nullable|array',
            'multi_branches' => 'nullable|array',
            'profile_photo_path' => 'nullable|image|max:2048',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        // Cek role allowed
        $allowedRoles = $this->getAllowedRoles($user);
        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'Anda tidak memiliki hak akses untuk membuat user dengan role ini.'])->withInput();
        }

        // Validasi Audit
        if ($user->role == 'audit') {
            $allowedBranchIds = $user->branches->pluck('id')->toArray();
            if ($request->branch_id && !in_array($request->branch_id, $allowedBranchIds)) {
                return back()->withErrors(['branch_id' => 'Anda tidak memiliki hak akses untuk menambahkan user di cabang ini.'])->withInput();
            }
            if ($request->has('multi_branches')) {
                foreach ($request->multi_branches as $branchId) {
                    if (!in_array($branchId, $allowedBranchIds)) {
                        return back()->withErrors(['multi_branches' => 'Salah satu cabang yang dipilih di luar wilayah audit Anda.'])->withInput();
                    }
                }
            }
        }

        // Validasi Admin Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($request->branch_id && $request->branch_id != $user->branch_id) {
                return back()->withErrors(['branch_id' => 'Anda hanya dapat menambahkan user di cabang Anda sendiri.'])->withInput();
            }
        }

        // 2. Siapkan Data
        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        if ($request->has('multi_divisions') && count($request->multi_divisions) > 0) {
            $data['division_id'] = $request->multi_divisions[0];
        } else {
            $data['division_id'] = null;
        }

        if ($user->role == 'admin' && $user->branch_id != null) {
            $data['branch_id'] = $user->branch_id;
        }

        if ($request->role == 'admin') {
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        $data['password'] = Hash::make($request->password);
        $data['qr_code_value'] = (string) Str::uuid();
        $data['hire_date'] = now();

        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // 3. Create User
        $newUser = User::create($data);

        // 4. Sync Pivot Tables
        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }

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

        // Proteksi Edit
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) {
                abort(403, 'User ini di luar wilayah audit Anda.');
            }
        }
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'User ini di luar cabang Anda.');
            }
        }

        // Filter Data Dropdown
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $branches = Branch::where('id', $auth_user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif ($auth_user->role == 'audit') {
            $branches = $auth_user->branches;
            $allowedRoles = ['audit', 'leader', 'security', 'user_biasa'];
        } else {
            $branches = Branch::all();
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        $divisions = Division::all();

        return view('users.user_edit', compact('user', 'divisions', 'branches', 'allowedRoles'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();

        // Proteksi Update
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403, 'User ini di luar cabang Anda.');
        }

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403, 'User ini di luar wilayah audit Anda.');
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

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $data['branch_id'] = $auth_user->branch_id;
        }

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

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        $auth_user = Auth::user();

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403, 'User ini di luar cabang Anda.');
        }

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403, 'User ini di luar wilayah audit Anda.');
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

    /**
     * Show user detail.
     */
    public function show(User $user)
    {
        $auth_user = Auth::user();

        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }
        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403);
        }

        $user->load(['branch', 'division', 'branches', 'divisions', 'workHistories']);
        $stats = $this->getSpecificUserStats($user->id);
        $recentAttendance = Attendance::where('user_id', $user->id)
                                    ->latest('check_in_time')
                                    ->take(5)
                                    ->get();

        return view('users.user_show', compact('user', 'stats', 'recentAttendance'));
    }

    /**
     * Helper methods
     */
    private function getSpecificUserStats($user_id)
    {
        $query = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year);
        
        $totalAttendances = (clone $query)->count();
        $late = (clone $query)->where('is_late_checkin', true)->count();
        $early = (clone $query)->where('is_early_checkout', true)->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        $onTime = max($totalAttendances - $late, 0);

        return [
            'total' => $totalAttendances,
            'present' => $totalAttendances,
            'late' => $late,
            'early' => $early,
            'pending' => $pending,
            'on_time' => $onTime,
            'current_month' => Carbon::now()->translatedFormat('F Y')
        ];
    }

    public function toggleStatus(User $user)
    {
        $auth_user = Auth::user();
        if ($user->id == auth()->id()) return redirect()->route('users.index')->with('error', 'Gagal.');
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->route('users.index')->with('success', 'Status user diubah.');
    }

    public function resetPassword(User $user)
    {
        $newPassword = Str::random(8);
        $user->update(['password' => Hash::make($newPassword)]);
        return redirect()->route('users.index')->with('success', "Password baru: $newPassword");
    }

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
    
    public function getUserStats()
    {
        // Method ini opsional, jika tidak dipakai bisa dihapus
        return [];
    }
}