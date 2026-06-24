<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HostelNotification;
use App\Services\HostelBillingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExitRequestController extends Controller
{
    public function calculate(Request $request, HostelBillingService $billing): JsonResponse
    {
        $data = $request->validate([
            'requested_exit_date' => ['required', 'date', 'after:today'],
        ]);

        return response()->json($billing->exitSettlementPreview($request->user(), Carbon::parse($data['requested_exit_date'])));
    }

    public function store(Request $request, HostelBillingService $billing): JsonResponse
    {
        $data = $request->validate([
            'requested_exit_date' => ['required', 'date', 'after:today'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $preview = $billing->exitSettlementPreview($request->user(), Carbon::parse($data['requested_exit_date']));

        if (! $preview['notice_valid']) {
            return response()->json(['message' => 'Exit requires at least 30 days notice.'], 422);
        }

        $exit = $request->user()->exitRequests()->create([
            'requested_exit_date' => $data['requested_exit_date'],
            'notice_days' => $preview['notice_days'],
            'rent_due' => $preview['rent_due'],
            'deposit_adjustment' => $preview['deposit_adjustment'],
            'balance_adjustment' => $preview['balance_adjustment'],
            'final_payable' => $preview['final_payable'],
            'final_refundable' => $preview['final_refundable'],
            'reason' => $data['reason'] ?? null,
        ]);

        HostelNotification::create([
            'user_id' => $request->user()->id,
            'title' => 'Exit request submitted',
            'body' => 'Your final settlement has been calculated and is pending admin review.',
            'type' => 'exit',
        ]);

        return response()->json(['message' => 'Exit request submitted.', 'exit' => $exit]);
    }
}
