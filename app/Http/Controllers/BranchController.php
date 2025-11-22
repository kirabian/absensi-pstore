<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Constructor: Cek Login & Role Awal
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && in_array(Auth::user()->role, ['admin', 'audit'])) {
                return $next($request);
            }
            return abort(403, 'Hanya Admin atau Audit yang boleh mengakses halaman ini.');
        });
    }

    /**
     * Menampilkan daftar cabang (Difilter sesuai Role).
     */
    public function index()
    {
        $user = Auth::user();
        $query = Branch::query();

        // 1. Jika Admin Cabang -> Hanya lihat cabangnya sendiri
        if ($user->role == 'admin' && $user->branch_id != null) {
            $query->where('id', $user->branch_id);
        }
        
        // 2. Jika Audit -> Hanya lihat cabang wilayah auditnya (dari tabel pivot)
        elseif ($user->role == 'audit') {
            $auditBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('id', $auditBranchIds);
        }
        
        // 3. Jika Super Admin -> Melihat SEMUA (Tidak ada filter tambahan)

        $branches = $query->latest()->get();
        
        // Pastikan nama view sesuai folder Anda (branch/branch_index)
        return view('branch.branch_index', compact('branches'));
    }

    /**
     * Form tambah cabang (Hanya Super Admin).
     */
    public function create()
    {
        // Proteksi: Hanya Super Admin yang boleh tambah cabang
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403, 'Anda tidak memiliki akses untuk menambah cabang.');
        }

        return view('branch.branch_create');
    }

    /**
     * Simpan cabang baru.
     */
    public function store(Request $request)
    {
        // Proteksi Back-end
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'nullable|string',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    /**
     * Form edit cabang.
     */
    public function edit(Branch $branch)
    {
        $user = Auth::user();

        // Proteksi: Audit tidak boleh edit data master cabang
        if ($user->role == 'audit') {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data cabang.');
        }

        // Proteksi: Admin Cabang hanya boleh edit cabangnya sendiri
        if ($user->role == 'admin' && $user->branch_id != null) {
            if ($branch->id != $user->branch_id) {
                abort(403, 'Anda tidak bisa mengedit cabang lain.');
            }
        }

        return view('branch.branch_edit', compact('branch'));
    }

    /**
     * Update data cabang.
     */
    public function update(Request $request, Branch $branch)
    {
        $user = Auth::user();

        // Proteksi Back-end Update
        if ($user->role == 'audit') abort(403);
        if ($user->role == 'admin' && $user->branch_id != null && $branch->id != $user->branch_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    /**
     * Hapus cabang (Hanya Super Admin).
     */
    public function destroy(Branch $branch)
    {
        // Proteksi: Hanya Super Admin
        if (Auth::user()->role != 'admin' || Auth::user()->branch_id != null) {
            abort(403, 'Akses Ditolak.');
        }

        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada user/divisi yang terhubung ke cabang ini.');
        }
    }

    // Redirect show ke edit (sesuai request)
    public function show(Branch $branch)
    {
        return redirect()->route('branches.edit', $branch->id);
    }
}