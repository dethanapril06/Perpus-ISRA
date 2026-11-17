<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole()) {
            try {
                if (Schema::hasTable('anggota') && !Schema::hasColumn('anggota', 'rfid_uid')) {
                    Artisan::call('migrate', ['--force' => true]);
                }
            } catch (\Throwable $e) {
                Log::warning('Auto migrate rfid_uid: ' . $e->getMessage());
            }
        }
    }
}
