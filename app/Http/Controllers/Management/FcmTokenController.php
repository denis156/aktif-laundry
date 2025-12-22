<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    /**
     * Update FCM token for authenticated management user
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:500'],
        ]);

        $user = Auth::guard('web')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully',
        ]);
    }

    /**
     * Remove FCM token for authenticated management user (logout/disable notifications)
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user->update([
            'fcm_token' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully',
        ]);
    }
}
