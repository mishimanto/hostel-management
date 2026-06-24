<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomBooking;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class RoomChangeService
{
    public function preview(RoomBooking $booking, Room $requestedRoom, ?CarbonInterface $changeDate = null): array
    {
        $booking->loadMissing('room.branch');
        $changeDate = Carbon::parse($changeDate ?? now())->startOfDay();

        if ($booking->status !== 'approved') {
            throw ValidationException::withMessages([
                'room_booking_id' => 'Room change is available only for an approved booking.',
            ]);
        }

        if ($requestedRoom->id === $booking->room_id) {
            throw ValidationException::withMessages([
                'room_id' => 'Please select a different room.',
            ]);
        }

        if ($requestedRoom->status !== 'available') {
            throw ValidationException::withMessages([
                'room_id' => 'Selected room is not available.',
            ]);
        }

        $oldDailyRent = (float) $booking->monthly_rent / 30;
        $newDailyRent = (float) $requestedRoom->monthly_rent / 30;
        $paidUntil = $booking->paid_until?->copy()->startOfDay() ?? $changeDate->copy();
        $remainingPaidDays = max(0, $changeDate->diffInDays($paidUntil, false));
        $additionalPayable = 0;
        $extraDays = 0;
        $newCoveredDays = $remainingPaidDays;

        if ((float) $requestedRoom->monthly_rent > (float) $booking->monthly_rent) {
            $additionalPayable = round(($newDailyRent - $oldDailyRent) * $remainingPaidDays, 2);
        }

        if ((float) $requestedRoom->monthly_rent < (float) $booking->monthly_rent && $newDailyRent > 0) {
            $remainingValue = $remainingPaidDays * $oldDailyRent;
            $newCoveredDays = (int) floor($remainingValue / $newDailyRent);
            $extraDays = max(0, $newCoveredDays - $remainingPaidDays);
        }

        return [
            'room_booking_id' => $booking->id,
            'current_room_id' => $booking->room_id,
            'requested_room_id' => $requestedRoom->id,
            'change_date' => $changeDate->toDateString(),
            'old_monthly_rent' => round((float) $booking->monthly_rent, 2),
            'new_monthly_rent' => round((float) $requestedRoom->monthly_rent, 2),
            'old_daily_rent' => round($oldDailyRent, 2),
            'new_daily_rent' => round($newDailyRent, 2),
            'remaining_paid_days' => $remainingPaidDays,
            'additional_payable' => round($additionalPayable, 2),
            'extra_days' => $extraDays,
            'new_paid_until' => $changeDate->copy()->addDays($newCoveredDays)->toDateString(),
            'requested_label' => $requestedRoom->branch->name.' / Room '.$requestedRoom->room_number,
        ];
    }

    public function apply(RoomBooking $booking, Room $requestedRoom, array $preview): void
    {
        $booking->room->update(['status' => 'available']);
        $requestedRoom->update(['status' => 'booked']);

        $booking->update([
            'branch_id' => $requestedRoom->branch_id,
            'room_id' => $requestedRoom->id,
            'monthly_rent' => $requestedRoom->monthly_rent,
            'paid_until' => $preview['new_paid_until'],
        ]);
    }
}
