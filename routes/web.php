<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\MessageController;
use App\Models\Banner;
use App\Models\Product;
use App\Models\SiteSetting;

// Halaman depan
Route::get('/', function () {
    $products = collect();
    $banners = collect();

    if (Schema::hasTable('products')) {
        $products = Product::query()
            ->latest()
            ->take(8)
            ->get();
    }

    if (Schema::hasTable('banners')) {
        $banners = Banner::query()
            ->where('is_active', true)
            ->latest()
            ->take(3)
            ->get();
    }

    $settings = Schema::hasTable('site_settings')
        ? SiteSetting::query()->first()
        : null;

    return view('welcome', [
        'products' => $products,
        'banners' => $banners,
        'settings' => $settings,
    ]);
});

// Route untuk pancingan login (Filament)
Route::get('/cek-login', function () {
    return redirect()->route('filament.admin.auth.login');
});
