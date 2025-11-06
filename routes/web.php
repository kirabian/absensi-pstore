<?php

use Illuminate\Support\Facades\Route;

// Import semua controller yang kita butuhkan
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ProfileController; // <-- TAMBAHKAN INI

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

    // --- Rute Khusus ADMIN & AUDIT ---
    Route::middleware(['role:admin,audit'])->group(function () { // <-- DIUBAH DI SINI
        Route::resource('divisions', DivisionController::class);
        Route::resource('users', UserController::class);
    });

    // --- Rute Khusus SECURITY ---
    Route::middleware(['role:security'])->group(function () {
        Route::get('/scan', [ScanController::class, 'scanPage'])->name('security.scan');
        Route::post('/scan/store', [ScanController::class, 'storeAttendance'])->name('security.attendance.store');
    });
});
