<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        try {
            $validated = $request->validate([
                'label' => 'required|string|max:120',
                'recipient_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:50',
                'full_address' => 'required|string',
                'province' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'is_default' => 'nullable|boolean',
            ]);

            $user = $request->user();
            $isFirstAddress = ! $user->addresses()->exists();
            $isDefault = (bool) ($validated['is_default'] ?? false) || $isFirstAddress;

            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            // CARA MANUAL: Bypassing mass assignment protection
            $address = new UserAddress();
            $address->user_id = $user->id;
            $address->label = $validated['label'];
            $address->recipient_name = $validated['recipient_name'];
            $address->phone_number = $validated['phone_number'];
            $address->full_address = $validated['full_address'];
            $address->is_default = $isDefault;

            // Simpan kolom opsional HANYA JIKA ADA di database
            $table = $address->getTable();
            foreach (['province', 'city', 'district', 'postal_code', 'latitude', 'longitude', 'note'] as $col) {
                if (isset($validated[$col]) && Schema::hasColumn($table, $col)) {
                    $address->$col = $validated[$col];
                }
            }

            $address->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Alamat berhasil disimpan.',
                'data' => $address,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal simpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $address = UserAddress::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$address) {
                return response()->json(['status' => 'error', 'message' => 'Alamat tidak ditemukan.'], 404);
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
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'is_default' => 'nullable|boolean',
            ]);

            $isDefault = (bool) ($validated['is_default'] ?? false);
            if ($isDefault && !$address->is_default) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            $address->label = $validated['label'];
            $address->recipient_name = $validated['recipient_name'];
            $address->phone_number = $validated['phone_number'];
            $address->full_address = $validated['full_address'];
            $address->is_default = $isDefault;

            $table = $address->getTable();
            foreach (['province', 'city', 'district', 'postal_code', 'latitude', 'longitude', 'note'] as $col) {
                if (isset($validated[$col]) && Schema::hasColumn($table, $col)) {
                    $address->$col = $validated[$col];
                }
            }

            $address->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Alamat berhasil diperbarui.',
                'data' => $address,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal update: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $address = UserAddress::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (!$address) return response()->json(['status' => 'error', 'message' => 'Alamat tidak ditemukan.'], 404);
        $address->delete();
        return response()->json(['status' => 'success', 'message' => 'Alamat berhasil dihapus.']);
    }

    public function setDefault(Request $request, $id): JsonResponse
    {
        $address = UserAddress::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (!$address) return response()->json(['status' => 'error', 'message' => 'Alamat tidak ditemukan.'], 404);
        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return response()->json(['status' => 'success', 'message' => 'Alamat utama berhasil diperbarui.']);
    }
}
