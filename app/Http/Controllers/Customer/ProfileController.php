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
        ]);

        $request->user()->update($data);

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}
