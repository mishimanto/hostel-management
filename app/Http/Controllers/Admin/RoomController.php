<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        Room::create($request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'room_number' => ['required', 'string', 'max:100'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:available,booked,maintenance'],
        ]));

        return back()->with('status', 'Room created.');
    }
}
