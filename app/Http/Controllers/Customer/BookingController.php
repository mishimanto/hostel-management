<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'requested_start_date' => ['required', 'date', 'after_or_equal:today'],
            'requested_end_date' => ['required', 'date', 'after_or_equal:requested_start_date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:150'],
            'payment_details' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->user()->activeRoomBooking()->exists()) {
            return redirect()
                ->route('customer.dashboard', ['section' => 'requests'])
                ->with('status', 'You already have an active room. Use room change instead.');
        }

        if ($request->user()->roomBookings()->where('status', 'pending')->exists()) {
            return back()->with('status', 'You already have a pending booking request.');
        }

        $room = Room::with('branch')->where('status', 'available')->findOrFail($data['room_id']);
        $startDate = Carbon::parse($data['requested_start_date'])->startOfDay();
        $endDate = Carbon::parse($data['requested_end_date'])->startOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $payableAmount = round(((float) $room->monthly_rent / 30) * $totalDays, 2);

        $request->user()->roomBookings()->create([
            'branch_id' => $room->branch_id,
            'room_id' => $room->id,
            'monthly_rent' => $room->monthly_rent,
            'requested_start_date' => $startDate->toDateString(),
            'requested_end_date' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'payable_amount' => $payableAmount,
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'payment_details' => $data['payment_details'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Room booking submitted',
            'body' => 'Your booking and payment details for '.$room->branch->name.' room '.$room->room_number.' are pending admin review.',
            'type' => 'room_booking',
        ]);

        return redirect()->route('customer.dashboard', ['section' => 'booking'])->with('status', 'Room booking request submitted.');
    }
}
