<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index(): JsonResponse
    {
        $vouchers = Voucher::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('quota')
                    ->orWhereColumn('claimed_count', '<', 'quota');
            })
            ->latest()
            ->get()
            ->map(fn (Voucher $voucher) => $this->formatVoucher($voucher));

        return response()->json([
            'status' => 'success',
            'message' => 'Data voucher Fahlin Store',
            'data' => $vouchers,
        ]);
    }

    public function claim(Request $request, Voucher $voucher): JsonResponse
    {
        $user = $request->user();

        if ($user->vouchers()->where('voucher_id', $voucher->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher sudah pernah diklaim.',
            ], 409);
        }

        try {
            $claimedVoucher = DB::transaction(function () use ($user, $voucher) {
                $lockedVoucher = Voucher::query()
                    ->whereKey($voucher->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedVoucher->isClaimable()) {
                    return null;
                }

                $user->vouchers()->attach($lockedVoucher->id, [
                    'claimed_at' => now(),
                ]);

                $lockedVoucher->increment('claimed_count');

                return $lockedVoucher->refresh();
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Voucher sudah pernah diklaim.',
            ], 409);
        }

        if (! $claimedVoucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher tidak tersedia atau sudah habis.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil diklaim.',
            'data' => $this->formatVoucher($claimedVoucher),
        ]);
    }

    public function myVouchers(Request $request): JsonResponse
    {
        $vouchers = $request->user()
            ->vouchers()
            ->latest('user_vouchers.claimed_at')
            ->get()
            ->map(fn (Voucher $voucher) => array_merge(
                $this->formatVoucher($voucher),
                [
                    'claimed_at' => $voucher->pivot->claimed_at
                        ? Carbon::parse($voucher->pivot->claimed_at)->toISOString()
                        : null,
                    'used_at' => $voucher->pivot->used_at
                        ? Carbon::parse($voucher->pivot->used_at)->toISOString()
                        : null,
                ],
            ));

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher yang sudah diklaim',
            'data' => $vouchers,
        ]);
    }

    private function formatVoucher(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'title' => $voucher->title,
            'code' => $voucher->code,
            'description' => $voucher->description,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (float) $voucher->discount_value,
            'min_purchase' => (float) $voucher->min_purchase,
            'max_discount' => $voucher->max_discount === null ? null : (float) $voucher->max_discount,
            'quota' => $voucher->quota,
            'claimed_count' => $voucher->claimed_count,
            'remaining_quota' => $voucher->remaining_quota,
            'starts_at' => $voucher->starts_at?->toISOString(),
            'expires_at' => $voucher->expires_at?->toISOString(),
            'is_active' => $voucher->is_active,
        ];
    }
}
