<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HostelNotification;
use App\Models\Seat;
use App\Services\HostelBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatChangeRequestController extends Controller
{
    public function calculate(Request $request, HostelBillingService $billing): JsonResponse
    {
        $data = $request->validate([
            'seat_id' => ['required', 'exists:seats,id'],
        ]);

        return response()->json($billing->seatChangePreview($request->user(), Seat::findOrFail($data['seat_id'])));
    }

    public function store(Request $request, HostelBillingService $billing): JsonResponse
    {
        $data = $request->validate([
            'seat_id' => ['required', 'exists:seats,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user()->load('residentProfile.seat');
        $preview = $billing->seatChangePreview($user, Seat::findOrFail($data['seat_id']));

        $requestModel = $user->seatChangeRequests()->create([
            'current_seat_id' => $user->residentProfile->seat_id,
            'requested_seat_id' => $data['seat_id'],
            'type' => $preview['type'],
            'current_rent' => $preview['current_rent'],
            'requested_rent' => $preview['requested_rent'],
            'balance_before' => $preview['balance_before'],
            'payable_amount' => $preview['payable_amount'],
            'credit_to_next_rent' => $preview['credit_to_next_rent'],
            'reason' => $data['reason'] ?? null,
        ]);

        HostelNotification::create([
            'user_id' => $user->id,
            'title' => 'Seat change request submitted',
            'body' => 'Your request is pending admin review.',
            'type' => 'seat_change',
        ]);

        return response()->json(['message' => 'Seat change request submitted.', 'request' => $requestModel]);
    }
}
