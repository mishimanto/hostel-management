<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load([
            'activeRoomBooking.branch',
            'activeRoomBooking.room',
            'roomBookings' => fn ($query) => $query->with(['branch', 'room'])->latest()->limit(8),
            'payments' => fn ($query) => $query->latest('billing_month')->limit(8),
            'roomChangeRequests' => fn ($query) => $query->with(['roomBooking.room.branch', 'currentRoom.branch', 'requestedRoom.branch'])->latest()->limit(8),
            'leaveRequests' => fn ($query) => $query->with(['roomBooking.room.branch'])->latest()->limit(8),
            'notifications' => fn ($query) => $query->latest()->limit(8),
        ]);

        $approvedBookings = $request->user()
            ->roomBookings()
            ->with(['branch', 'room'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        $availableRooms = Room::query()
            ->with('branch')
            ->where('status', 'available')
            ->orderBy('monthly_rent')
            ->get();

        $section = $request->query('section', $user->activeRoomBooking ? 'overview' : 'booking');

        return view('customer.dashboard', compact('user', 'availableRooms', 'approvedBookings', 'section'));
    }
}
