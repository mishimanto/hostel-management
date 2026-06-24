<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->hostelNotifications()
            ->latest()
            ->limit(10)
            ->get(['id', 'title', 'body', 'type', 'read_at', 'created_at']);

        return response()->json([
            'unread_count' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->user()->hostelNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }
}
