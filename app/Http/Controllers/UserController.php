<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;
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

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = User::with(['division', 'branch', 'branches', 'divisions']);

        // 1. Filter Role/Cabang
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branch_id', $auditBranchIds);
        }

        // 2. Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()
            ->paginate(10)
            ->appends(['search' => $request->search]);

        return view('users.user_index', compact('users'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->role == 'admin' && $user->branch_id != null) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $allowedRoles = ['leader', 'security', 'user_biasa'];
        } elseif ($user->role == 'audit') {
            $branches = $user->branches;
            $allowedRoles = ['audit', 'leader', 'security', 'user_biasa'];
        } else {
            $branches = Branch::all();
            $allowedRoles = ['admin', 'audit', 'leader', 'security', 'user_biasa'];
        }

        $divisions = Division::all();

        return view('users.user_create', compact('divisions', 'branches', 'allowedRoles'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

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

        $allowedRoles = $this->getAllowedRoles($user);
        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'Role tidak diizinkan.'])->withInput();
        }

        $data = $request->except(['password', 'profile_photo_path', 'multi_branches', 'multi_divisions']);

        $data['division_id'] = ($request->has('multi_divisions') && count($request->multi_divisions) > 0) ? $request->multi_divisions[0] : null;

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

        $newUser = User::create($data);

        if ($request->role == 'audit' && $request->has('multi_branches')) {
            $newUser->branches()->sync($request->multi_branches);
        }
        if ($request->has('multi_divisions')) {
            $newUser->divisions()->sync($request->multi_divisions);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load(['branches', 'divisions']);
        $auth_user = Auth::user();

        if ($auth_user->role == 'audit') {
            $allowedBranchIds = $auth_user->branches->pluck('id')->toArray();
            if (!in_array($user->branch_id, $allowedBranchIds)) abort(403);
        }
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
        }

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

    public function update(Request $request, User $user)
    {
        $auth_user = Auth::user();
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) abort(403);
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

    // --- PERBAIKAN DISINI: Menambahkan Logika Block Audit ---
    public function destroy(User $user)
    {
        // 1. Cek jika Audit mencoba menghapus
        if (Auth::user()->role == 'audit') {
            return back()->with('error', 'Akses Ditolak: Role Audit tidak diizinkan menghapus data user.');
        }

        // 2. Cek jika user menghapus diri sendiri
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Tidak bisa hapus akun sendiri.');
        }

        try {
            if ($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
            $user->branches()->detach();
            $user->divisions()->detach();
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus user.');
        }
    }

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

        $user->load(['branch', 'division', 'branches', 'divisions']);
        $stats = $this->getSpecificUserStats($user->id);
        $recentAttendance = Attendance::where('user_id', $user->id)
            ->latest('check_in_time')
            ->take(5)
            ->get();

        return view('users.user_show', compact('user', 'stats', 'recentAttendance'));
    }

    // Helper Statistik User (MENGGUNAKAN VERSI PERBAIKAN SEBELUMNYA)
    private function getSpecificUserStats($user_id)
    {
        // 1. Filter Waktu (Bulan Ini)
        $query = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year);

        // Hitung Total Hadir (Valid):
        $presentCount = (clone $query)
            ->whereIn('status', ['verified', 'present', 'late'])
            ->where(function($q) {
                $q->where('presence_status', '!=', 'Alpha')
                  ->orWhereNull('presence_status');
            })
            ->count();

        // Hitung Alpha
        $alphaCount = (clone $query)
            ->where('presence_status', 'Alpha')
            ->count();

        $late = (clone $query)->where('is_late_checkin', true)->count();
        $early = (clone $query)->where('is_early_checkout', true)->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();

        $onTime = max($presentCount - $late, 0);

        $totalPresentRecords = $presentCount; 

        $onTimePercentage = $totalPresentRecords > 0 ? round(($onTime / $totalPresentRecords) * 100) : 0;
        $latePercentage = $totalPresentRecords > 0 ? round(($late / $totalPresentRecords) * 100) : 0;

        return [
            'total' => $presentCount,
            'present' => $presentCount,
            'alpha' => $alphaCount,
            'late' => $late,
            'early' => $early,
            'pending' => $pending,
            'on_time' => $onTime,
            'on_time_percentage' => $onTimePercentage,
            'late_percentage' => $latePercentage,
            'current_month' => Carbon::now()->translatedFormat('F Y')
        ];
    }

    public function toggleStatus(User $user)
    {
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri saat sedang login.');
        }

        $currentUser = auth()->user();
        if ($currentUser->role == 'admin' && $currentUser->branch_id != null) {
            if ($user->branch_id != $currentUser->branch_id) {
                return back()->with('error', 'Anda tidak memiliki akses ke user ini.');
            }
            if ($user->role == 'admin' && $user->branch_id == null) {
                return back()->with('error', 'Anda tidak dapat menonaktifkan Super Admin.');
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User {$user->name} berhasil {$statusText}.");
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
}