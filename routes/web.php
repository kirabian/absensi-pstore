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
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rute Aplikasi (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- Rute Utama ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // --- Rute Export PDF Dashboard (BARU) ---
    // Ditaruh disini agar semua role bisa akses
    Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export-pdf');

    // --- Rute Search Global ---
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    // === RUTE BROADCAST ===
    Route::prefix('broadcast')->name('broadcast.')->group(function () {
        Route::get('/notifications', [BroadcastController::class, 'getNotifications'])->name('notifications');
        Route::post('/{broadcast}/mark-read', [BroadcastController::class, 'markAsRead'])->name('mark-read');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/', [BroadcastController::class, 'index'])->name('index');
            Route::get('/create', [BroadcastController::class, 'create'])->name('create');
            Route::post('/', [BroadcastController::class, 'store'])->name('store');
            Route::get('/{broadcast}/edit', [BroadcastController::class, 'edit'])->name('edit');
            Route::put('/{broadcast}', [BroadcastController::class, 'update'])->name('update');
            Route::delete('/{broadcast}', [BroadcastController::class, 'destroy'])->name('destroy');
        });
        Route::get('/{broadcast}', [BroadcastController::class, 'show'])->name('show');
    });

    // === RUTE WORK SCHEDULES ===
    Route::prefix('work-schedules')->name('work-schedules.')->group(function () {
        Route::get('/', [WorkScheduleController::class, 'index'])->name('index');
        Route::get('/create', [WorkScheduleController::class, 'create'])->name('create');
        Route::post('/', [WorkScheduleController::class, 'store'])->name('store');
        Route::get('/{workSchedule}/edit', [WorkScheduleController::class, 'edit'])->name('edit');
        Route::put('/{workSchedule}', [WorkScheduleController::class, 'update'])->name('update');
        Route::delete('/{workSchedule}', [WorkScheduleController::class, 'destroy'])->name('destroy');
        Route::patch('/{workSchedule}/toggle-status', [WorkScheduleController::class, 'toggleStatus'])->name('toggle-status');
    })->middleware('role:admin,audit');

    // === RUTE PROFILE ===
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::put('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
        Route::get('/photo/{user}', [ProfileController::class, 'getProfilePhoto'])->name('photo.get');
        Route::put('/ktp', [ProfileController::class, 'updateKtp'])->name('ktp.update');
        Route::get('/ktp/{user}', [ProfileController::class, 'getKtpPhoto'])->name('ktp.get');
        Route::post('/work-history', [WorkHistoryController::class, 'store'])->name('work-history.store');
        Route::delete('/work-history/{history}', [WorkHistoryController::class, 'destroy'])->name('work-history.destroy');
        Route::post('/inventory', [ProfileController::class, 'storeInventory'])->name('inventory.store');
        Route::delete('/inventory/{inventory}', [ProfileController::class, 'destroyInventory'])->name('inventory.destroy');
        Route::get('/inventory', [ProfileController::class, 'showInventory'])->name('inventory.index');
    });

    // === RUTE INVENTORY ===
    Route::prefix('inventory')->name('inventory.')->middleware(['role:admin,audit'])->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
    });

    // === RUTE ADMIN & AUDIT ===
    Route::middleware(['role:admin,audit'])->group(function () {
        Route::resource('branches', BranchController::class);
        Route::post('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');
        Route::resource('divisions', DivisionController::class);
        Route::post('/divisions/{division}/toggle-status', [DivisionController::class, 'toggleStatus'])->name('divisions.toggle-status');
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::prefix('verifikasi')->name('audit.')->group(function () {
            Route::get('/absensi', [AuditController::class, 'showVerificationList'])->name('verify.list');
            Route::put('/setujui/{attendance}', [AuditController::class, 'approve'])->name('approve');
            Route::delete('/tolak/{attendance}', [AuditController::class, 'reject'])->name('reject');
            Route::get('/laporan', [AuditController::class, 'showReports'])->name('reports');
        });

        Route::get('/izin-telat', [AuditController::class, 'showLatePermissions'])->name('audit.late.list');
        Route::post('/izin-telat/{lateNotification}/approve', [AuditController::class, 'approveLatePermission'])->name('late.approve');
        Route::post('/izin-telat/{lateNotification}/reject', [AuditController::class, 'rejectLatePermission'])->name('late.reject');
    });

    // === RUTE SECURITY ===
    Route::middleware(['role:security'])->prefix('security')->name('security.')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan');
        Route::post('/check-user', [ScanController::class, 'checkUser'])->name('check-user');
        Route::post('/store-attendance', [ScanController::class, 'storeAttendance'])->name('store-attendance');
        Route::get('/stats', [ScanController::class, 'getStats'])->name('stats');
        Route::get('/attendance-log', [ScanController::class, 'attendanceLog'])->name('attendance-log');
        Route::get('/today-attendance', [ScanController::class, 'todayAttendance'])->name('today-attendance');
    });

    // === RUTE TEAM ===
    Route::middleware(['role:user_biasa,leader,audit'])->group(function () {
        Route::get('/tim-saya', [TeamController::class, 'index'])->name('my.team');
        Route::get('/tim-saya/{user}', [TeamController::class, 'show'])->name('my.team.show');
        Route::get('/tim-saya/attendance/{user}', [TeamController::class, 'attendance'])->name('my.team.attendance');
    });

    // === RUTE SELF ATTENDANCE ===
    Route::middleware(['role:user_biasa,leader'])->group(function () {
        Route::prefix('absen-mandiri')->name('self.attend.')->group(function () {
            Route::get('/', [SelfAttendanceController::class, 'create'])->name('create');
            Route::post('/', [SelfAttendanceController::class, 'store'])->name('store');
            Route::get('/history', [SelfAttendanceController::class, 'history'])->name('history');
            Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');
        });

        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
            Route::post('/store', [LeaveRequestController::class, 'store'])->name('store');
            Route::get('/history', [LeaveRequestController::class, 'history'])->name('history');
            Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
            Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');
        });
    });

    // === RUTE API ===
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard-stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('recent.activities');
        Route::get('/attendance-chart', [DashboardController::class, 'getAttendanceChart'])->name('attendance.chart');
    });

    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now(), 'environment' => app()->environment()]);
});