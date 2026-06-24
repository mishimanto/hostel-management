<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HostelNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $leave = $request->user()->leaveApplications()->create($data);

        HostelNotification::create([
            'user_id' => $request->user()->id,
            'title' => 'Leave application submitted',
            'body' => 'Your leave application is pending approval.',
            'type' => 'leave',
        ]);

        return response()->json(['message' => 'Leave application submitted.', 'leave' => $leave]);
    }
}
