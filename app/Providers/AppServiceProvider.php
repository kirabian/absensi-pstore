<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
   public function boot()
    {
        // Tambahkan baris ini agar semua asset() dipaksa pakai HTTPS
        if($this->app->environment('production') || $this->app->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
