<?php

use App\Http\Controllers\GlobalSearchController;
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
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\BroadcastController;

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

    // --- Rute Search Global (Hanya untuk Admin) ---
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    // --- Rute untuk SEMUA USER yang login (bisa lihat broadcast) ---
    Route::prefix('broadcast')->name('broadcast.')->group(function () {
        Route::get('/', [BroadcastController::class, 'index'])->name('index');
        Route::get('/{broadcast}', [BroadcastController::class, 'show'])->name('show');
    });

    // --- Rute Khusus ADMIN (untuk mengelola broadcast) ---
    Route::middleware(['role:admin'])->group(function () {
        Route::prefix('broadcast')->name('broadcast.')->group(function () {
            Route::get('/create', [BroadcastController::class, 'create'])->name('create');
            Route::post('/', [BroadcastController::class, 'store'])->name('store');
            Route::get('/{broadcast}/edit', [BroadcastController::class, 'edit'])->name('edit');
            Route::put('/{broadcast}', [BroadcastController::class, 'update'])->name('update');
            Route::delete('/{broadcast}', [BroadcastController::class, 'destroy'])->name('destroy');
        });
    });

    // CATATAN: RUTE BROADCAST DIPINDAHKAN KE BAWAH UNTUK MELINDUNGINYA DENGAN role:admin

    // === RUTE WORK SCHEDULES ===
    Route::prefix('work-schedules')->name('work-schedules.')->group(function () {
        Route::get('/', [WorkScheduleController::class, 'index'])->name('index');
        Route::get('/create', [WorkScheduleController::class, 'create'])->name('create');
        Route::post('/', [WorkScheduleController::class, 'store'])->name('store');
        Route::get('/{workSchedule}/edit', [WorkScheduleController::class, 'edit'])->name('edit');
        Route::put('/{workSchedule}', [WorkScheduleController::class, 'update'])->name('update');
        Route::delete('/{workSchedule}', [WorkScheduleController::class, 'destroy'])->name('destroy');
        Route::patch('/{workSchedule}/toggle-status', [WorkScheduleController::class, 'toggleStatus'])->name('toggle-status');
    })->middleware('can:access_work_schedules');

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

    // --- Rute Khusus ADMIN & AUDIT (Dibuat terpisah agar Admin/Audit dapat mengelola Branch, Division, User, dan Verifikasi) ---
    Route::middleware(['role:admin,audit'])->group(function () {
        Route::resource('branches', BranchController::class);
        Route::resource('divisions', DivisionController::class);
        Route::resource('users', UserController::class);

        Route::get('/verifikasi-absensi', [AuditController::class, 'showVerificationList'])->name('audit.verify.list');
        Route::put('/verifikasi/setujui/{attendance}', [AuditController::class, 'approve'])->name('audit.approve');
        Route::delete('/verifikasi/tolak/{attendance}', [AuditController::class, 'reject'])->name('audit.reject');

        Route::get('/izin-telat', [AuditController::class, 'showLatePermissions'])->name('audit.late.list');
    });

    // // --- Rute Khusus ADMIN (Hanya untuk Admin untuk mengelola Broadcast) ---
    // Route::middleware(['role:admin'])->group(function () {
    //     // === RUTE BROADCAST ===
    //     Route::prefix('broadcast')->name('broadcast.')->group(function () {
    //         Route::get('/', [BroadcastController::class, 'index'])->name('index');
    //         Route::get('/create', [BroadcastController::class, 'create'])->name('create');
    //         Route::post('/', [BroadcastController::class, 'store'])->name('store');
    //         Route::get('/{broadcast}', [BroadcastController::class, 'show'])->name('show');
    //         Route::get('/{broadcast}/edit', [BroadcastController::class, 'edit'])->name('edit');
    //         Route::put('/{broadcast}', [BroadcastController::class, 'update'])->name('update');
    //         Route::delete('/{broadcast}', [BroadcastController::class, 'destroy'])->name('destroy');
    //     });
    // });

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

    // === RUTE SECURITY ===
    Route::middleware(['role:security'])->prefix('security')->name('security.')->group(function () {

        // Halaman utama scanner (GET)
        Route::get('/scan', [ScanController::class, 'index'])->name('scan');

        // Step 1: Validasi QR, kirim balik data user (POST)
        Route::post('/check-user', [ScanController::class, 'checkUser'])->name('check-user');

        // Step 2: Simpan absensi (Masuk/Pulang) + Foto (POST)
        Route::post('/store-attendance', [ScanController::class, 'storeAttendance'])->name('store-attendance');

        // Stats untuk dashboard security
        Route::get('/stats', [ScanController::class, 'getStats'])->name('stats');
    });

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
