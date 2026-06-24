<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\RoomBooking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_booking_id' => ['required', 'exists:room_bookings,id'],
            'leave_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $booking = RoomBooking::where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->findOrFail($data['room_booking_id']);

        $bookingStart = ($booking->started_at ?? $booking->requested_start_date)?->copy()->startOfDay();
        $bookingEnd = ($booking->paid_until ?? $booking->requested_end_date)?->copy()->startOfDay();
        $leaveDate = Carbon::parse($data['leave_date'])->startOfDay();

        if (! $bookingStart || ! $bookingEnd || $leaveDate->lt($bookingStart) || $leaveDate->gt($bookingEnd)) {
            throw ValidationException::withMessages([
                'leave_date' => 'Leave date must be inside the selected booking date range.',
            ]);
        }

        $leave = $request->user()->leaveRequests()->create([
            'room_booking_id' => $data['room_booking_id'],
            'start_date' => $data['leave_date'],
            'end_date' => $data['leave_date'],
            'reason' => $data['reason'],
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Leave request submitted',
            'body' => 'Your leave request for '.$booking->room->room_number.' is pending approval.',
            'type' => 'leave',
        ]);

        return response()->json(['message' => 'Leave request submitted.', 'leave' => $leave]);
    }
}
