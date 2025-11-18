<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelfAttendanceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\WorkHistoryController;
use App\Http\Controllers\LeaveRequestController;

/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rute Aplikasi (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- Rute Utama ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


    // --- Rute Edit Profil (UNTUK SEMUA ROLE) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('profile.photo.delete');
    Route::put('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::put('/profile/ktp', [ProfileController::class, 'updateKtp'])->name('profile.ktp.update');
    Route::post('/profile/work-history', [WorkHistoryController::class, 'store'])->name('profile.work-history.store');
    Route::delete('/profile/work-history/{history}', [WorkHistoryController::class, 'destroy'])->name('profile.work-history.destroy');

    // --- Rute Inventaris Profil ---
    Route::post('/profile/inventory', [ProfileController::class, 'storeInventory'])->name('profile.inventory.store');
    Route::delete('/profile/inventory/{inventory}', [ProfileController::class, 'destroyInventory'])->name('profile.inventory.destroy');

    // --- Rute Khusus ADMIN ---
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('branches', BranchController::class);
    });

    // --- Rute Khusus ADMIN & AUDIT ---
    Route::middleware(['role:admin,audit'])->group(function () {
        Route::resource('divisions', DivisionController::class);
        Route::resource('users', UserController::class);

        Route::get('/verifikasi-absensi', [AuditController::class, 'showVerificationList'])->name('audit.verify.list');
        Route::put('/verifikasi/setujui/{attendance}', [AuditController::class, 'approve'])->name('audit.approve');
        Route::delete('/verifikasi/tolak/{attendance}', [AuditController::class, 'reject'])->name('audit.reject');

        Route::get('/izin-telat', [AuditController::class, 'showLatePermissions'])->name('audit.late.list');

        // Nanti Anda perlu halaman untuk memverifikasi pengajuan baru
        // Route::get('/verifikasi-izin', [LeaveRequestController::class, 'adminIndex'])->name('leave.admin.index');
    });

    // --- Rute Khusus SECURITY ---
    // Route test (Bisa dihapus jika tidak perlu)
    Route::get('/test-role-middleware', function () {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_branch' => $user->branch_id,
            'message' => 'Middleware test berhasil'
        ]);
    })->middleware(['auth', 'role:security']);

    // === [ UPDATE PENTING DISINI ] ===
    // Menggunakan grup middleware baru yang lebih bersih
    Route::middleware(['role:security'])->prefix('security')->name('security.')->group(function () {
        
        // Halaman utama scanner (GET)
        Route::get('/scan', [ScanController::class, 'index'])->name('scan');
        
        // Step 1: Validasi QR, kirim balik data user (POST)
        Route::post('/check-user', [ScanController::class, 'checkUser'])->name('check-user');
        
        // Step 2: Simpan absensi (Masuk/Pulang/Malam) + Foto (POST)
        Route::post('/store-attendance', [ScanController::class, 'storeAttendance'])->name('store-attendance');
    });
    // === [ AKHIR UPDATE ] ===


    // --- Rute Khusus USER_BIASA, LEADER, & AUDIT ---
    Route::middleware(['role:user_biasa,leader,audit'])->group(function () {
        Route::get('/tim-saya', [TeamController::class, 'index'])->name('my.team');
    });

    // --- Rute Khusus USER_BIASA & LEADER ---
    Route::middleware(['role:user_biasa,leader'])->group(function () {
        Route::get('/absen-mandiri', [SelfAttendanceController::class, 'create'])->name('self.attend.create');
        Route::post('/absen-mandiri', [SelfAttendanceController::class, 'store'])->name('self.attend.store');

        Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');

        // --- Rute Pengajuan Izin/Cuti/Sakit/Telat Baru ---
        Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
        Route::post('/leave/store', [LeaveRequestController::class, 'store'])->name('leave.store');
    });
});