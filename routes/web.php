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
use App\Http\Controllers\InventoryController;

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
    
    // --- Rute Riwayat Pekerjaan ---
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
        // Divisi
        Route::resource('divisions', DivisionController::class);
        
        // User Management dengan QR Code
        Route::resource('users', UserController::class);
        
        // QR Code Routes
        Route::post('/users/{user}/generate-qrcode', [UserController::class, 'generateQrCode'])
            ->name('users.generate.qrcode');
        Route::get('/users/{user}/download-qrcode', [UserController::class, 'downloadQrCode'])
            ->name('users.download.qrcode');
        Route::post('/users/generate-all-qrcodes', [UserController::class, 'generateAllQrCodes'])
            ->name('users.generate.all.qrcodes');
        Route::get('/users/{user}/view-qrcode', [UserController::class, 'viewQrCode'])
            ->name('users.view.qrcode');
        Route::post('/users/{user}/regenerate-qrcode', [UserController::class, 'regenerateQrCode'])
            ->name('users.regenerate.qrcode');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])
            ->name('users.bulk.action');

        // Verifikasi Absensi
        Route::get('/verifikasi-absensi', [AuditController::class, 'showVerificationList'])->name('audit.verify.list');
        Route::put('/verifikasi/setujui/{attendance}', [AuditController::class, 'approve'])->name('audit.approve');
        Route::delete('/verifikasi/tolak/{attendance}', [AuditController::class, 'reject'])->name('audit.reject');

        // Izin Telat
        Route::get('/izin-telat', [AuditController::class, 'showLatePermissions'])->name('audit.late.list');

        // Verifikasi Pengajuan Izin/Cuti
        Route::get('/verifikasi-izin', [LeaveRequestController::class, 'adminIndex'])->name('leave.admin.index');
        Route::put('/verifikasi-izin/{leaveRequest}/approve', [LeaveRequestController::class, 'adminApprove'])->name('leave.admin.approve');
        Route::put('/verifikasi-izin/{leaveRequest}/reject', [LeaveRequestController::class, 'adminReject'])->name('leave.admin.reject');
    });

    // --- Rute Khusus SECURITY ---
    Route::middleware(['role:security'])->group(function () {
        // Scan QR Code
        Route::get('/scan', [ScanController::class, 'scanPage'])->name('security.scan');
        Route::post('/scan/check-qr', [ScanController::class, 'getUserByQr'])->name('security.scan.check');
        Route::post('/scan/store', [ScanController::class, 'storeAttendance'])->name('security.attendance.store');
        
        // Riwayat Absensi Hari Ini
        Route::get('/scan/today-attendance', [ScanController::class, 'getTodayAttendance'])->name('security.today.attendance');
        Route::get('/scan/attendance-stats', [ScanController::class, 'getAttendanceStats'])->name('security.attendance.stats');
    });

    // --- Rute Khusus USER_BIASA, LEADER, & AUDIT ---
    Route::middleware(['role:user_biasa,leader,audit'])->group(function () {
        Route::get('/tim-saya', [TeamController::class, 'index'])->name('my.team');
        
        // Riwayat Absensi Tim
        Route::get('/tim-saya/attendance', [TeamController::class, 'teamAttendance'])->name('my.team.attendance');
        Route::get('/tim-saya/attendance/{user}', [TeamController::class, 'userAttendance'])->name('my.team.user.attendance');
    });

    // --- Rute Khusus USER_BIASA & LEADER ---
    Route::middleware(['role:user_biasa,leader'])->group(function () {
        // Absen Mandiri
        Route::get('/absen-mandiri', [SelfAttendanceController::class, 'create'])->name('self.attend.create');
        Route::post('/absen-mandiri', [SelfAttendanceController::class, 'store'])->name('self.attend.store');

        // Laporan Telat
        Route::post('/lapor-telat', [SelfAttendanceController::class, 'storeLateStatus'])->name('late.status.store');
        Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');

        // Pengajuan Izin/Cuti/Sakit/Telat
        Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
        Route::post('/leave/store', [LeaveRequestController::class, 'store'])->name('leave.store');
        Route::get('/leave/history', [LeaveRequestController::class, 'index'])->name('leave.index');
        Route::get('/leave/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');
        Route::delete('/leave/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('leave.destroy');
        
        // Inventory Pribadi
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // --- Rute untuk SEMUA ROLE (Kecuali Security) ---
    Route::middleware(['role:admin,audit,leader,user_biasa'])->group(function () {
        // View QR Code Pribadi
        Route::get('/my-qrcode', [ProfileController::class, 'showQrCode'])->name('profile.qrcode');
        
        // Download QR Code Pribadi
        Route::get('/my-qrcode/download', [ProfileController::class, 'downloadQrCode'])->name('profile.qrcode.download');
        
        // Riwayat Absensi Pribadi
        Route::get('/my-attendance', [SelfAttendanceController::class, 'myAttendance'])->name('my.attendance');
        
        // Export Riwayat Absensi
        Route::get('/my-attendance/export', [SelfAttendanceController::class, 'exportAttendance'])->name('my.attendance.export');
    });

    // --- Rute API untuk Dashboard & Real-time Data ---
    Route::prefix('api')->group(function () {
        // Dashboard Stats
        Route::get('/dashboard-stats', [DashboardController::class, 'getStats'])->name('api.dashboard.stats');
        
        // Recent Activities
        Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('api.recent.activities');
        
        // Attendance Chart Data
        Route::get('/attendance-chart', [DashboardController::class, 'getAttendanceChart'])->name('api.attendance.chart');
        
        // Team Attendance Status
        Route::get('/team-status', [TeamController::class, 'getTeamStatus'])->name('api.team.status');
    });

    // --- Rute Fallback untuk Error 404 ---
    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });
});

/*
|--------------------------------------------------------------------------
| Rute Testing & Development (Hanya untuk local environment)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/test-qrcode', function () {
        return view('test.qrcode-test');
    });
    
    Route::get('/generate-test-qr', function () {
        $user = \App\Models\User::first();
        if ($user) {
            $user->generateQrCode();
            return "QR Code generated for: " . $user->name;
        }
        return "No user found";
    });
}