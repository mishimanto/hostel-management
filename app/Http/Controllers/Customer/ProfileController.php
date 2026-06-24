<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nid_number' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:1000'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $request->user()->update($data);

        if ($request->user()->residentProfile) {
            $request->user()->residentProfile->update([
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}
