<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // TAMBAHKAN KODE INI DI SINI
        // ============================================================
        
        // Menjalankan command 'attendance:mark-absent' setiap hari jam 01:00 pagi
        $schedule->command('attendance:mark-absent')
                 ->dailyAt('00:00')
                 ->timezone('Asia/Jakarta'); // Pastikan zona waktu sesuai WIB
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}