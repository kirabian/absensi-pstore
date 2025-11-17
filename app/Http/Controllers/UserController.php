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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            'hire_date' => 'nullable|date',
            'whatsapp' => 'nullable|string|max:20',
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

        // Generate QR code value
        $qrCodeValue = 'EMP-' . time() . '-' . Str::random(8);

        // Create user
        $newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'branch_id' => $data['branch_id'],
            'division_id' => $data['division_id'],
            'hire_date' => $data['hire_date'],
            'whatsapp' => $data['whatsapp'],
            'qr_code_value' => $qrCodeValue,
        ]);

        // Auto-generate QR code image
        $this->generateQrCodeImage($newUser);

        return redirect()->route('users.index')
            ->with('success', 'User baru berhasil ditambahkan dengan QR Code.');
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
            'hire_date' => 'nullable|date',
            'whatsapp' => 'nullable|string|max:20',
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
            // Hapus file QR code jika ada
            if ($user->qr_code_path && Storage::disk('public')->exists($user->qr_code_path)) {
                Storage::disk('public')->delete($user->qr_code_path);
            }

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

        return view('users.user_show', compact('user'));
    }

    /**
     * Generate QR Code untuk user tertentu
     */
    public function generateQrCode(User $user)
    {
        try {
            $auth_user = Auth::user();
            
            // Authorization
            if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
                if ($user->branch_id != $auth_user->branch_id) {
                    abort(403, 'Anda tidak memiliki akses untuk user ini.');
                }
            }

            // Generate QR code value jika belum ada
            if (!$user->qr_code_value) {
                $user->update([
                    'qr_code_value' => 'EMP-' . $user->id . '-' . Str::random(8)
                ]);
            }

            $this->generateQrCodeImage($user);
            
            return redirect()->route('users.index')
                ->with('success', 'QR Code berhasil digenerate untuk ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Download QR Code
     */
    /**
 * Download QR Code (Alternative Method)
 */
public function downloadQrCode(User $user)
{
    $auth_user = Auth::user();
    
    // Authorization
    if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
        if ($user->branch_id != $auth_user->branch_id) {
            abort(403, 'Anda tidak memiliki akses untuk user ini.');
        }
    }

    if (!$user->qr_code_path) {
        return redirect()->back()->with('error', 'QR Code belum digenerate.');
    }

    // Cek dan regenerate jika file tidak ada
    if (!Storage::disk('public')->exists($user->qr_code_path)) {
        try {
            $this->generateQrCodeImage($user);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File QR Code tidak ditemukan.');
        }
    }

    try {
        $filePath = storage_path('app/public/' . $user->qr_code_path);
        
        // Validasi file exists
        if (!file_exists($filePath)) {
            throw new \Exception('File tidak ditemukan: ' . $filePath);
        }

        // Sanitize filename
        $safeName = str_replace([' ', '/', '\\', ':'], '_', $user->name);
        $fileName = 'qr_code_' . $safeName . '.png';

        // Return file response
        return response()->download($filePath, $fileName, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('QR Code Download Failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Download gagal: ' . $e->getMessage());
    }
}

    /**
     * Generate QR Code untuk semua user yang belum punya
     */
    public function generateAllQrCodes()
    {
        try {
            $auth_user = Auth::user();
            $query = User::whereNull('qr_code_path');

            // Filter berdasarkan cabang untuk admin cabang
            if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
                $query->where('branch_id', $auth_user->branch_id);
            }

            $users = $query->get();
            
            $generatedCount = 0;
            foreach ($users as $user) {
                // Generate QR code value jika belum ada
                if (!$user->qr_code_value) {
                    $user->update([
                        'qr_code_value' => 'EMP-' . $user->id . '-' . Str::random(8)
                    ]);
                }

                $this->generateQrCodeImage($user);
                $generatedCount++;
            }
            
            return redirect()->route('users.index')
                ->with('success', "Berhasil generate QR Code untuk {$generatedCount} user.");
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    /**
     * View QR Code
     */
    public function viewQrCode(User $user)
    {
        $auth_user = Auth::user();
        
        // Authorization
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            if ($user->branch_id != $auth_user->branch_id) {
                abort(403, 'Anda tidak memiliki akses untuk user ini.');
            }
        }

        if (!$user->qr_code_path) {
            return redirect()->back()->with('error', 'QR Code belum digenerate.');
        }

        return view('users.qr_code_view', compact('user'));
    }

    /**
     * Regenerate QR Code (hapus dan buat ulang)
     */
    public function regenerateQrCode(User $user)
    {
        try {
            $auth_user = Auth::user();
            
            // Authorization
            if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
                if ($user->branch_id != $auth_user->branch_id) {
                    abort(403, 'Anda tidak memiliki akses untuk user ini.');
                }
            }

            // Hapus file QR code lama jika ada
            if ($user->qr_code_path && Storage::disk('public')->exists($user->qr_code_path)) {
                Storage::disk('public')->delete($user->qr_code_path);
            }

            // Generate QR code value baru
            $user->update([
                'qr_code_value' => 'EMP-' . $user->id . '-' . Str::random(8) . '-' . time()
            ]);

            $this->generateQrCodeImage($user);
            
            return redirect()->route('users.index')
                ->with('success', 'QR Code berhasil di-regenerate untuk ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal regenerate QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk generate QR code image
     */
    /**
 * Helper method untuk generate QR code image (Improved)
 */
private function generateQrCodeImage(User $user)
{
    try {
        // Pastikan QR code value ada
        if (!$user->qr_code_value) {
            $user->update([
                'qr_code_value' => 'EMP-' . $user->id . '-' . Str::random(8) . '-' . time()
            ]);
            $user->refresh(); // Refresh model
        }

        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($user->qr_code_value);
        
        // Buat folder jika belum ada
        $directory = 'qrcodes';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
        }
        
        // Generate unique filename
        $fileName = $directory . '/user_' . $user->id . '_' . time() . '.png';
        
        // Simpan file
        $saveResult = Storage::disk('public')->put($fileName, $qrCode);
        
        if (!$saveResult) {
            throw new \Exception('Gagal menyimpan file QR code.');
        }

        // Update user record
        $user->update(['qr_code_path' => $fileName]);
        
        // Verify file was created
        if (!Storage::disk('public')->exists($fileName)) {
            throw new \Exception('File QR code tidak terbentuk setelah penyimpanan.');
        }
        
        return true;
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Generate QR Code Image Error: ' . $e->getMessage());
        throw new \Exception('Gagal generate QR code image: ' . $e->getMessage());
    }
}

    /**
     * Bulk action untuk user
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:generate_qr,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $auth_user = Auth::user();
        $userIds = $request->user_ids;

        // Filter user berdasarkan akses cabang
        if ($auth_user->role == 'admin' && $auth_user->branch_id != null) {
            $userIds = User::whereIn('id', $userIds)
                ->where('branch_id', $auth_user->branch_id)
                ->pluck('id')
                ->toArray();
        }

        try {
            switch ($request->action) {
                case 'generate_qr':
                    $count = 0;
                    foreach ($userIds as $userId) {
                        $user = User::find($userId);
                        if ($user && !$user->qr_code_path) {
                            if (!$user->qr_code_value) {
                                $user->update([
                                    'qr_code_value' => 'EMP-' . $user->id . '-' . Str::random(8)
                                ]);
                            }
                            $this->generateQrCodeImage($user);
                            $count++;
                        }
                    }
                    return redirect()->route('users.index')
                        ->with('success', "Berhasil generate QR Code untuk {$count} user.");

                case 'delete':
                    // Prevent self-deletion
                    $userIds = array_diff($userIds, [auth()->id()]);
                    
                    $count = 0;
                    foreach ($userIds as $userId) {
                        $user = User::find($userId);
                        if ($user) {
                            // Hapus file QR code
                            if ($user->qr_code_path && Storage::disk('public')->exists($user->qr_code_path)) {
                                Storage::disk('public')->delete($user->qr_code_path);
                            }
                            $user->delete();
                            $count++;
                        }
                    }
                    return redirect()->route('users.index')
                        ->with('success', "Berhasil menghapus {$count} user.");

                default:
                    return redirect()->route('users.index')
                        ->with('error', 'Aksi tidak valid.');
            }
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal melakukan aksi bulk: ' . $e->getMessage());
        }
    }
}