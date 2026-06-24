<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load([
            'residentProfile.branch',
            'residentProfile.room',
            'residentProfile.seat',
            'payments' => fn ($query) => $query->latest('billing_month')->limit(8),
            'seatChangeRequests' => fn ($query) => $query->with(['currentSeat.room.branch', 'requestedSeat.room.branch'])->latest()->limit(5),
            'leaveApplications' => fn ($query) => $query->latest()->limit(5),
            'exitRequests' => fn ($query) => $query->latest()->limit(3),
            'hostelNotifications' => fn ($query) => $query->latest()->limit(8),
        ]);

        $availableSeats = Seat::query()
            ->with('room.branch')
            ->where('is_available', true)
            ->when($user->residentProfile, fn ($query) => $query->whereKeyNot($user->residentProfile->seat_id))
            ->orderBy('monthly_rent')
            ->get();

        return view('dashboard', compact('user', 'availableSeats'));
    }
}
