<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    /**
     * Update FCM token for authenticated pelanggan
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:500'],
        ]);

        $pelanggan = Auth::guard('pelanggan')->user();

        if (! $pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $pelanggan->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully',
        ]);
    }

    /**
     * Remove FCM token for authenticated pelanggan (logout/disable notifications)
     */
    public function destroy(Request $request): JsonResponse
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        if (! $pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $pelanggan->update([
            'fcm_token' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully',
        ]);
    }
}
