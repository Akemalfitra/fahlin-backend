<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\MessageController;
use App\Models\Banner;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

// --- ADMIN REGISTRATION (HIDDEN) ---
Route::get('/register-admin-secret-access', function () {
    return view('auth.admin-register');
})->name('admin.register.hidden');

Route::post('/register-admin-secret-access', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_admin' => true,
    ]);

    return redirect()->route('filament.admin.auth.login')->with('success', 'Admin berhasil didaftarkan. Silakan login.');
});
