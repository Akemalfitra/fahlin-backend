<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Voucher::observe(\App\Observers\VoucherObserver::class);
        \App\Models\Announcement::observe(\App\Observers\AnnouncementObserver::class);
    // Hanya paksa HTTPS jika ENV aplikasi diset ke production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
    
}
