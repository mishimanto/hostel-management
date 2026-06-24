<?php

namespace App\Services;

use App\Models\ResidentProfile;
use App\Models\Seat;
use App\Models\User;
use Carbon\CarbonInterface;

class HostelBillingService
{
    public function seatChangePreview(User $user, Seat $requestedSeat): array
    {
        $profile = $user->residentProfile()->with(['seat.room.branch'])->firstOrFail();
        $requestedSeat->load('room.branch');

        $currentRent = (float) $profile->seat->monthly_rent;
        $requestedRent = (float) $requestedSeat->monthly_rent;
        $balance = (float) $profile->balance;
        $difference = $requestedRent - $currentRent;
        $net = $difference - $balance;

        return [
            'current_rent' => round($currentRent, 2),
            'requested_rent' => round($requestedRent, 2),
            'rent_difference' => round($difference, 2),
            'balance_before' => round($balance, 2),
            'payable_amount' => round(max(0, $net), 2),
            'credit_to_next_rent' => round(max(0, -$net), 2),
            'type' => $profile->branch_id === $requestedSeat->room->branch_id ? 'same_branch' : 'different_branch',
            'requested_label' => $requestedSeat->room->branch->name.' / Room '.$requestedSeat->room->room_no.' / Seat '.$requestedSeat->label,
        ];
    }

    public function exitSettlementPreview(User $user, CarbonInterface $exitDate): array
    {
        $profile = $user->residentProfile()->with('seat')->firstOrFail();
        $noticeDays = now()->startOfDay()->diffInDays($exitDate->copy()->startOfDay(), false);

        $monthlyRent = (float) $profile->seat->monthly_rent;
        $dailyRent = $monthlyRent / max(1, $exitDate->daysInMonth);
        $usedDays = min($exitDate->day, $exitDate->daysInMonth);
        $rentDue = round($dailyRent * $usedDays, 2);

        $deposit = (float) $profile->deposit_paid;
        $balance = (float) $profile->balance;
        $net = $rentDue - $deposit - $balance;

        return [
            'notice_days' => $noticeDays,
            'notice_valid' => $noticeDays >= 30,
            'rent_due' => round($rentDue, 2),
            'deposit_adjustment' => round($deposit, 2),
            'balance_adjustment' => round($balance, 2),
            'final_payable' => round(max(0, $net), 2),
            'final_refundable' => round(max(0, -$net), 2),
        ];
    }
}
