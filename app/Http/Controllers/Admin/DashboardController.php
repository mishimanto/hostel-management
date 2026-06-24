<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\RoomChangeRequest;
use App\Models\User;
use App\Services\RoomChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->is_admin, 403);

        return view('admin.dashboard', [
            'stats' => [
                'residents' => User::where('is_admin', false)->count(),
                'bookedRooms' => Room::where('status', 'booked')->count(),
                'pendingRequests' => RoomBooking::where('status', 'pending')->count()
                    + RoomChangeRequest::where('status', 'pending')->count()
                    + LeaveRequest::where('status', 'pending')->count(),
                'rentDue' => Payment::whereIn('status', ['due', 'partial'])->sum('amount_due'),
            ],
            'bookingRequests' => RoomBooking::with(['user', 'branch', 'room'])->latest()->limit(12)->get(),
            'roomChangeRequests' => RoomChangeRequest::with(['user', 'roomBooking.room.branch', 'currentRoom.branch', 'requestedRoom.branch'])->latest()->limit(12)->get(),
            'leaveRequests' => LeaveRequest::with(['user', 'roomBooking.room.branch'])->latest()->limit(12)->get(),
            'branches' => Branch::orderBy('name')->get(),
            'rooms' => Room::with('branch')->orderBy('room_number')->limit(20)->get(),
        ]);
    }

    public function reviewBooking(Request $request, RoomBooking $roomBooking): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $roomBooking->load(['user', 'room', 'branch']);

        if ($data['status'] === 'approved') {
            if ($roomBooking->user->activeRoomBooking()->whereKeyNot($roomBooking->id)->exists()) {
                return back()->with('status', 'Customer already has an active room booking.');
            }

            if ($roomBooking->room->status !== 'available') {
                return back()->with('status', 'Selected room is no longer available.');
            }

            $roomBooking->room->update(['status' => 'booked']);
        }

        $roomBooking->update([
            'status' => $data['status'],
            'started_at' => $data['status'] === 'approved' ? $roomBooking->requested_start_date : $roomBooking->started_at,
            'paid_until' => $data['status'] === 'approved' ? $roomBooking->requested_end_date : $roomBooking->paid_until,
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'approved') {
            Payment::firstOrCreate([
                'invoice_no' => 'BOOK-'.$roomBooking->id,
            ], [
                'user_id' => $roomBooking->user_id,
                'room_booking_id' => $roomBooking->id,
                'room_id' => $roomBooking->room_id,
                'billing_month' => $roomBooking->requested_start_date,
                'due_date' => $roomBooking->requested_start_date,
                'amount_due' => $roomBooking->payable_amount,
                'amount_paid' => $roomBooking->payable_amount,
                'transaction_id' => $roomBooking->transaction_id,
            ]);
        }

        Notification::create([
            'user_id' => $roomBooking->user_id,
            'title' => 'Room booking '.$data['status'],
            'body' => 'Your room booking payment has been checked and the request has been '.$data['status'].'.',
            'type' => 'room_booking',
        ]);

        return back()->with('status', 'Room booking '.$data['status'].'.');
    }

    public function reviewRoomChange(Request $request, RoomChangeRequest $roomChangeRequest, RoomChangeService $roomChangeService): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $roomChangeRequest->load(['user', 'roomBooking.room', 'requestedRoom.branch']);

        if ($data['status'] === 'approved') {
            if ($roomChangeRequest->roomBooking->status !== 'approved') {
                return back()->with('status', 'This room change is not linked to an approved booking.');
            }

            if ($roomChangeRequest->requestedRoom->status !== 'available') {
                return back()->with('status', 'Requested room is no longer available.');
            }

            $preview = $roomChangeService->preview(
                $roomChangeRequest->roomBooking,
                $roomChangeRequest->requestedRoom,
                $roomChangeRequest->change_date,
            );

            $roomChangeRequest->fill([
                'current_room_id' => $preview['current_room_id'],
                'old_monthly_rent' => $preview['old_monthly_rent'],
                'new_monthly_rent' => $preview['new_monthly_rent'],
                'remaining_paid_days' => $preview['remaining_paid_days'],
                'additional_payable' => $preview['additional_payable'],
                'extra_days' => $preview['extra_days'],
                'new_paid_until' => $preview['new_paid_until'],
            ]);

            $roomChangeService->apply($roomChangeRequest->roomBooking, $roomChangeRequest->requestedRoom, $preview);
        }

        $roomChangeRequest->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $roomChangeRequest->user_id,
            'title' => 'Room change '.$data['status'],
            'body' => 'Your room change request has been '.$data['status'].'.',
            'type' => 'room_change',
        ]);

        return back()->with('status', 'Room change request '.$data['status'].'.');
    }

    public function reviewLeave(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $leaveRequest->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $leaveRequest->user_id,
            'title' => 'Leave '.$data['status'],
            'body' => 'Your leave request has been '.$data['status'].'.',
            'type' => 'leave',
        ]);

        return back()->with('status', 'Leave request '.$data['status'].'.');
    }
}
