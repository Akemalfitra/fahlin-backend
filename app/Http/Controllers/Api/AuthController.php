<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Fungsi Register User Baru
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        // Buat token akses
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Fungsi Login
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
             return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->fresh()
        ]);
    }

    public function firebaseGoogleLogin(Request $request)
    {
        $validated = $request->validate([
            'id_token' => 'required|string',
        ]);

        $firebaseUser = $this->signInFirebaseWithGoogleToken($validated['id_token']);

        if (! $firebaseUser || empty($firebaseUser['email'])) {
            return response()->json([
                'message' => 'Token Google/Firebase tidak valid'
            ], 401);
        }

        $user = User::query()
            ->where('firebase_uid', $firebaseUser['uid'])
            ->orWhere('email', $firebaseUser['email'])
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $firebaseUser['name'] ?: Str::before($firebaseUser['email'], '@'),
                'email' => $firebaseUser['email'],
                'firebase_uid' => $firebaseUser['uid'],
                'auth_provider' => 'google',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => $firebaseUser['email_verified'] ? now() : null,
                'is_online' => true,
                'last_seen_at' => now(),
            ]);
        } else {
            $user->update([
                'name' => $user->name ?: ($firebaseUser['name'] ?: Str::before($firebaseUser['email'], '@')),
                'firebase_uid' => $user->firebase_uid ?: $firebaseUser['uid'],
                'auth_provider' => $user->auth_provider === 'email' ? 'google' : $user->auth_provider,
                'email_verified_at' => $user->email_verified_at ?: ($firebaseUser['email_verified'] ? now() : null),
                'is_online' => true,
                'last_seen_at' => now(),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Google success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->fresh(),
        ]);
    }

    private function signInFirebaseWithGoogleToken(string $googleIdToken): ?array
    {
        $apiKey = config('services.firebase.api_key');

        if (! $apiKey) {
            Log::warning('FIREBASE_API_KEY belum dikonfigurasi.');
            return null;
        }

        try {
            $response = Http::timeout(15)->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:signInWithIdp?key=' . $apiKey,
                [
                    'postBody' => http_build_query([
                        'id_token' => $googleIdToken,
                        'providerId' => 'google.com',
                    ]),
                    'requestUri' => config('app.url', 'http://localhost'),
                    'returnIdpCredential' => true,
                    'returnSecureToken' => true,
                ],
            );
        } catch (ConnectionException $exception) {
            Log::warning('Tidak bisa terhubung ke Firebase Authentication.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Firebase Google login gagal.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        $data = $response->json();

        return [
            'uid' => $data['localId'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['displayName'] ?? null,
            'email_verified' => (bool) ($data['emailVerified'] ?? true),
        ];
    }

    private function verifyFirebaseIdToken(string $idToken): ?array
    {
        $projectId = config('services.firebase.project_id');

        if (! $projectId) {
            Log::warning('FIREBASE_PROJECT_ID belum dikonfigurasi.');
            return null;
        }

        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->decodeJwtPart($encodedHeader);
        $payload = $this->decodeJwtPart($encodedPayload);

        if (! is_array($header) || ! is_array($payload) || ($header['alg'] ?? null) !== 'RS256') {
            return null;
        }

        $kid = $header['kid'] ?? null;
        if (! $kid) {
            return null;
        }

        $certificates = Cache::remember('firebase_public_certificates', now()->addHour(), function () {
            $response = Http::timeout(10)->get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');

            if (! $response->successful()) {
                return [];
            }

            return $response->json();
        });

        $certificate = $certificates[$kid] ?? null;
        if (! $certificate) {
            Cache::forget('firebase_public_certificates');
            return null;
        }

        $signedData = $encodedHeader . '.' . $encodedPayload;
        $signature = $this->base64UrlDecode($encodedSignature);
        $verified = openssl_verify($signedData, $signature, $certificate, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            return null;
        }

        $now = time();
        $issuer = 'https://securetoken.google.com/' . $projectId;

        if (($payload['iss'] ?? null) !== $issuer) {
            return null;
        }

        if (($payload['aud'] ?? null) !== $projectId) {
            return null;
        }

        if (($payload['exp'] ?? 0) < $now || ($payload['iat'] ?? $now + 1) > $now) {
            return null;
        }

        if (empty($payload['sub'])) {
            return null;
        }

        return [
            'uid' => $payload['sub'],
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? null,
            'email_verified' => (bool) ($payload['email_verified'] ?? false),
        ];
    }

    private function decodeJwtPart(string $value): ?array
    {
        $decoded = json_decode($this->base64UrlDecode($value), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4));
    }

    // Fungsi Update Status Online / Offline
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $request->user()->update([
            'is_online' => $validated['is_online'],
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => $validated['is_online'] ? 'Status user online' : 'Status user offline',
            'user' => $request->user()->fresh(),
        ]);
    }

    // Fungsi Logout
    public function logout(Request $request)
    {
        $request->user()->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
