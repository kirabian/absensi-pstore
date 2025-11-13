<?php

use Illuminate\Support\Facades\Route;

// Import semua controller yang kita butuhkan
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

    // --- Rute Khusus SUPER ADMIN (untuk kelola cabang) ---
    Route::middleware(['role:admin'])->group(function () {
        // Controller akan memfilter lagi apakah ini Super Admin (branch_id == null)
        Route::resource('branches', BranchController::class);
    });

    // --- Rute Khusus ADMIN (Cabang) & AUDIT ---
    Route::middleware(['role:admin,audit'])->group(function () {
        Route::resource('divisions', DivisionController::class);
        Route::resource('users', UserController::class);

        Route::get('/verifikasi-absensi', [AuditController::class, 'showVerificationList'])->name('audit.verify.list');
        Route::put('/verifikasi/setujui/{attendance}', [AuditController::class, 'approve'])->name('audit.approve');
        Route::delete('/verifikasi/tolak/{attendance}', [AuditController::class, 'reject'])->name('audit.reject');

        Route::get('/izin-telat', [AuditController::class, 'showLatePermissions'])->name('audit.late.list');
    });

    // --- Rute Khusus SECURITY ---
    Route::middleware(['role:security'])->group(function () {
        Route::get('/scan', [ScanController::class, 'scanPage'])->name('security.scan');
        Route::post('/scan/store', [ScanController::class, 'storeAttendance'])->name('security.attendance.store');
    });

    // --- Rute Khusus LEADER & USER BIASA ---

    // ======================================================
    // --- INI ADALAH PERBAIKANNYA ---
    // ======================================================
    Route::middleware(['role:user_biasa,leader,audit'])->group(function () {
        // ======================================================

        Route::get('/absen-mandiri', [SelfAttendanceController::class, 'create'])->name('self.attend.create');
        Route::post('/absen-mandiri', [SelfAttendanceController::class, 'store'])->name('self.attend.store');

        Route::get('/tim-saya', [TeamController::class, 'index'])->name('my.team');

        Route::post('/lapor-telat', [SelfAttendanceController::class, 'storeLateStatus'])->name('late.status.store');
        Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');
    });
});
