<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExitRequest;
use App\Models\HostelNotification;
use App\Models\LeaveApplication;
use App\Models\Payment;
use App\Models\ResidentProfile;
use App\Models\Seat;
use App\Models\SeatChangeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->is_admin, 403);

        return view('admin.dashboard', [
            'stats' => [
                'residents' => User::where('is_admin', false)->count(),
                'activeResidents' => ResidentProfile::where('status', 'active')->count(),
                'pendingRequests' => SeatChangeRequest::where('status', 'pending')->count()
                    + LeaveApplication::where('status', 'pending')->count()
                    + ExitRequest::where('status', 'pending')->count(),
                'rentDue' => Payment::whereIn('status', ['due', 'partial'])->sum('amount_due'),
            ],
            'seatRequests' => SeatChangeRequest::with(['currentSeat.room.branch', 'requestedSeat.room.branch', 'user'])
                ->latest()
                ->limit(12)
                ->get(),
            'leaveApplications' => LeaveApplication::with('user')
                ->latest()
                ->limit(12)
                ->get(),
            'exitRequests' => ExitRequest::with('user')
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function reviewSeatChange(Request $request, SeatChangeRequest $seatChangeRequest): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $seatChangeRequest->load(['currentSeat', 'requestedSeat.room.branch', 'user.residentProfile']);
        $seatChangeRequest->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'approved' && $seatChangeRequest->user->residentProfile) {
            $profile = $seatChangeRequest->user->residentProfile;
            $requestedSeat = $seatChangeRequest->requestedSeat;

            Seat::whereKey($profile->seat_id)->update(['is_available' => true]);
            $requestedSeat->update(['is_available' => false]);
            $profile->update([
                'branch_id' => $requestedSeat->room->branch_id,
                'room_id' => $requestedSeat->room_id,
                'seat_id' => $requestedSeat->id,
                'balance' => $seatChangeRequest->credit_to_next_rent,
            ]);
        }

        HostelNotification::create([
            'user_id' => $seatChangeRequest->user_id,
            'title' => 'Seat change '.$data['status'],
            'body' => 'Your seat change request has been '.$data['status'].'.',
            'type' => 'seat_change',
        ]);

        return back()->with('status', 'Seat change request '.$data['status'].'.');
    }

    public function reviewLeave(Request $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $leaveApplication->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        HostelNotification::create([
            'user_id' => $leaveApplication->user_id,
            'title' => 'Leave '.$data['status'],
            'body' => 'Your leave application has been '.$data['status'].'.',
            'type' => 'leave',
        ]);

        return back()->with('status', 'Leave application '.$data['status'].'.');
    }

    public function reviewExit(Request $request, ExitRequest $exitRequest): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $exitRequest->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'approved') {
            $exitRequest->user->residentProfile?->update(['status' => 'notice']);
        }

        HostelNotification::create([
            'user_id' => $exitRequest->user_id,
            'title' => 'Exit '.$data['status'],
            'body' => 'Your exit request has been '.$data['status'].'.',
            'type' => 'exit',
        ]);

        return back()->with('status', 'Exit request '.$data['status'].'.');
    }
}
