<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Mail\OtpMail;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\VoucherAdController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes - Fahlin Store
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK ---

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/firebase/google-login', [AuthController::class, 'firebaseGoogleLogin']);

/**
 * FUNGSI KIRIM OTP REAL KE EMAIL
 */
Route::post('/send-otp', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    $otp = rand(1000, 9999);
    
    try {
        Mail::to($request->email)->send(new OtpMail($otp, 'User Fahlin Store'));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil dikirim ke ' . $request->email,
            'otp' => $otp 
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim email: ' . $e->getMessage()
        ], 500);
    }
});

/**
 * FUNGSI RESET PASSWORD (UPDATE KE DATABASE)
 */
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6', 
    ]);

    // Cari user berdasarkan email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User dengan email tersebut tidak ditemukan.'
        ], 404);
    }

    // Update password user
    $user->update([
        'password' => Hash::make($request->password)
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Password berhasil diperbarui. Silakan login kembali.'
    ], 200);
});

// Produk & Banner
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}/products', [CategoryController::class, 'products']);
Route::get('/banners', [ProductController::class, 'getBanners']);
Route::get('/voucher-ad', [VoucherAdController::class, 'show']);
Route::get('/vouchers', [VoucherController::class, 'index']);

Route::get('/orders', [OrderController::class, 'index']); // Sync orders by email
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/{orderNumber}/complete', [OrderController::class, 'completeOrder']);
Route::post('/shipping/calculate', [ShippingController::class, 'calculate']);

// Midtrans Notification Callback
Route::post('/midtrans/callback', [PaymentController::class, 'handleCallback']);

// Chat System (Flutter ke Laravel)
Route::get('/messages/{userId}', [MessageController::class, 'getMessages']);
Route::post('/messages', [MessageController::class, 'store']);


// --- RUTE TERPROTEKSI (SANCTUM) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
    return $request->user();
    });

    // Logout
    Route::post('/user/status', [AuthController::class, 'updateStatus']);
    Route::post('/pembayaran', [PaymentController::class, 'getSnapToken']);
    Route::get('/user/orders', [OrderController::class, 'index']); // Sync orders auth
    Route::get('/user/vouchers', [VoucherController::class, 'myVouchers']);
    Route::post('/vouchers/{voucher}/claim', [VoucherController::class, 'claim']);
    Route::get('/user/addresses', [UserAddressController::class, 'index']);
    Route::post('/user/addresses', [UserAddressController::class, 'store']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead']);
    Route::post('/user/fcm-token', [NotificationController::class, 'updateFcmToken']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
    
