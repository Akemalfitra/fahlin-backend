<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->latest('is_default')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $addresses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'full_address' => 'required|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'block' => 'nullable|string|max:120',
            'house_number' => 'nullable|string|max:120',
            'landmark' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $isFirstAddress = ! $user->addresses()->exists();
        $isDefault = (bool) ($validated['is_default'] ?? false) || $isFirstAddress;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            ...$validated,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil disimpan.',
            'data' => $address,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'full_address' => 'required|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'block' => 'nullable|string|max:120',
            'house_number' => 'nullable|string|max:120',
            'landmark' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);

        if ($isDefault && !$address->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update([
            ...$validated,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil diperbarui.',
            'data' => $address,
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        $address->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    public function setDefault(Request $request, $id): JsonResponse
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat utama berhasil diperbarui.',
        ]);
    }
}
