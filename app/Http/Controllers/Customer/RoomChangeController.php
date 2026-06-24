<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Services\RoomChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomChangeController extends Controller
{
    public function calculate(Request $request, RoomChangeService $roomChangeService): JsonResponse
    {
        $data = $request->validate([
            'room_booking_id' => ['required', 'exists:room_bookings,id'],
            'room_id' => ['required', 'exists:rooms,id'],
        ]);

        $booking = RoomBooking::where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->findOrFail($data['room_booking_id']);

        return response()->json($roomChangeService->preview(
            $booking,
            Room::with('branch')->where('status', 'available')->findOrFail($data['room_id']),
        ));
    }

    public function store(Request $request, RoomChangeService $roomChangeService): JsonResponse
    {
        $data = $request->validate([
            'room_booking_id' => ['required', 'exists:room_bookings,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = RoomBooking::where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->findOrFail($data['room_booking_id']);

        if ($request->user()->roomChangeRequests()->where('room_booking_id', $booking->id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'This booking already has a pending room change request.'], 422);
        }

        $room = Room::with('branch')->where('status', 'available')->findOrFail($data['room_id']);
        $preview = $roomChangeService->preview($booking, $room);

        $change = $request->user()->roomChangeRequests()->create([
            'room_booking_id' => $booking->id,
            'current_room_id' => $preview['current_room_id'],
            'requested_room_id' => $preview['requested_room_id'],
            'change_date' => $preview['change_date'],
            'old_monthly_rent' => $preview['old_monthly_rent'],
            'new_monthly_rent' => $preview['new_monthly_rent'],
            'remaining_paid_days' => $preview['remaining_paid_days'],
            'additional_payable' => $preview['additional_payable'],
            'extra_days' => $preview['extra_days'],
            'new_paid_until' => $preview['new_paid_until'],
            'reason' => $data['reason'] ?? null,
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Room change submitted',
            'body' => 'Your room change request is pending admin review.',
            'type' => 'room_change',
        ]);

        return response()->json(['message' => 'Room change request submitted.', 'request' => $change]);
    }
}
