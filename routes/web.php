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
| Rute Publik (Tidak Perlu Login)
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

    // --- Rute Search Global (Hanya untuk Admin) ---
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    // === RUTE BROADCAST ===
Route::prefix('broadcast')->name('broadcast.')->group(function () {
    
    // -------------------------------------------------------------------
    // 1. TARUH ROUTE UMUM (NOTIFIKASI) DI PALING ATAS (SEBELUM ADMIN)
    // -------------------------------------------------------------------
    // Rute ini harus bisa diakses semua user yang login (Auth)
    Route::get('/notifications', [BroadcastController::class, 'getNotifications'])->name('notifications');
    Route::post('/{broadcast}/mark-read', [BroadcastController::class, 'markAsRead'])->name('mark-read');

    // -------------------------------------------------------------------
    // 2. BARU KEMUDIAN ROUTE ADMIN
    // -------------------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/', [BroadcastController::class, 'index'])->name('index');
        Route::get('/create', [BroadcastController::class, 'create'])->name('create');
        Route::post('/', [BroadcastController::class, 'store'])->name('store');
        
        // Route Edit & Delete
        Route::get('/{broadcast}/edit', [BroadcastController::class, 'edit'])->name('edit');
        Route::put('/{broadcast}', [BroadcastController::class, 'update'])->name('update');
        Route::delete('/{broadcast}', [BroadcastController::class, 'destroy'])->name('destroy');
    });

    // -------------------------------------------------------------------
    // 3. ROUTE SHOW (DETAIL) - PERLU AKSES PUBLIK?
    // -------------------------------------------------------------------
    // Jika route 'show' ditaruh di dalam grup admin, user biasa tidak bisa
    // melihat detail pesan saat diklik. Sebaiknya ditaruh di luar grup admin.
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

    // === RUTE PROFILE (UNTUK SEMUA ROLE) ===
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');

        // Photo Management
        Route::delete('/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::put('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
        Route::get('/photo/{user}', [ProfileController::class, 'getProfilePhoto'])->name('photo.get');

        // KTP Management
        Route::put('/ktp', [ProfileController::class, 'updateKtp'])->name('ktp.update');
        Route::get('/ktp/{user}', [ProfileController::class, 'getKtpPhoto'])->name('ktp.get');

        // Work History
        Route::post('/work-history', [WorkHistoryController::class, 'store'])->name('work-history.store');
        Route::delete('/work-history/{history}', [WorkHistoryController::class, 'destroy'])->name('work-history.destroy');

        // Inventory
        Route::post('/inventory', [ProfileController::class, 'storeInventory'])->name('inventory.store');
        Route::delete('/inventory/{inventory}', [ProfileController::class, 'destroyInventory'])->name('inventory.destroy');
        Route::get('/inventory', [ProfileController::class, 'showInventory'])->name('inventory.index');
    });

    // === RUTE INVENTORY (Tambahan jika diperlukan) ===
    Route::prefix('inventory')->name('inventory.')->middleware(['role:admin,audit'])->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
    });

    // === RUTE ADMIN & AUDIT MANAGEMENT ===
    Route::middleware(['role:admin,audit'])->group(function () {
        // Branch Management
        Route::resource('branches', BranchController::class);
        Route::post('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');

        // Division Management
        Route::resource('divisions', DivisionController::class);
        Route::post('/divisions/{division}/toggle-status', [DivisionController::class, 'toggleStatus'])->name('divisions.toggle-status');

        // User Management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Attendance Verification
        Route::prefix('verifikasi')->name('audit.')->group(function () {
            Route::get('/absensi', [AuditController::class, 'showVerificationList'])->name('verify.list');
            Route::put('/setujui/{attendance}', [AuditController::class, 'approve'])->name('approve');
            Route::delete('/tolak/{attendance}', [AuditController::class, 'reject'])->name('reject');
            Route::get('/laporan', [AuditController::class, 'showReports'])->name('reports');
        });

        // Late Permissions
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

    // === RUTE TEAM MANAGEMENT ===
    Route::middleware(['role:user_biasa,leader,audit'])->group(function () {
        Route::get('/tim-saya', [TeamController::class, 'index'])->name('my.team');
        Route::get('/tim-saya/{user}', [TeamController::class, 'show'])->name('my.team.show');
        Route::get('/tim-saya/attendance/{user}', [TeamController::class, 'attendance'])->name('my.team.attendance');
    });

    // === RUTE SELF ATTENDANCE & LEAVE ===
    Route::middleware(['role:user_biasa,leader'])->group(function () {
        // Self Attendance
        Route::prefix('absen-mandiri')->name('self.attend.')->group(function () {
            Route::get('/', [SelfAttendanceController::class, 'create'])->name('create');
            Route::post('/', [SelfAttendanceController::class, 'store'])->name('store');
            Route::get('/history', [SelfAttendanceController::class, 'history'])->name('history');
            Route::post('/hapus-telat', [SelfAttendanceController::class, 'deleteLateStatus'])->name('late.status.delete');
        });

        // Leave Requests
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
            Route::post('/store', [LeaveRequestController::class, 'store'])->name('store');
            Route::get('/history', [LeaveRequestController::class, 'history'])->name('history');
            Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
            Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');
        });
    });

    // === RUTE LAPORAN & ANALYTICS ===
    Route::middleware(['role:admin,audit,leader'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/attendance', [AuditController::class, 'attendanceReport'])->name('attendance');
        Route::get('/performance', [AuditController::class, 'performanceReport'])->name('performance');
        Route::get('/leave', [AuditController::class, 'leaveReport'])->name('leave');
        Route::get('/export/attendance', [AuditController::class, 'exportAttendance'])->name('export.attendance');
    });

    // === RUTE API UNTUK DASHBOARD ===
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard-stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('recent.activities');
        Route::get('/attendance-chart', [DashboardController::class, 'getAttendanceChart'])->name('attendance.chart');
    });

    // === RUTE UTILITY ===
    Route::get('/test-role-middleware', function () {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_branch' => $user->branch->name ?? 'N/A',
            'user_division' => $user->division->name ?? 'N/A',
            'message' => 'Middleware test berhasil - Anda memiliki akses!'
        ]);
    })->middleware(['auth', 'role:admin,audit,security,leader,user_biasa']);

    // === RUTE FALLBACK ===
    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });
});

/*
|--------------------------------------------------------------------------
| Rute Health Check (Untuk Monitoring)
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'environment' => app()->environment()
    ]);
});

/*
|--------------------------------------------------------------------------
| Rute Debug (Hanya untuk Development)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/debug-session', function () {
        return response()->json([
            'session' => session()->all(),
            'user' => auth()->user(),
            'csrf_token' => csrf_token()
        ]);
    });

    Route::get('/debug-routes', function () {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'methods' => $route->methods(),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        });

        return response()->json($routes);
    });
}
