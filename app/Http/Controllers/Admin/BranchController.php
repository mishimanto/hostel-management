<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);

        Branch::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'rent_due_day' => ['required', 'integer', 'min:1', 'max:28'],
        ]));

        return back()->with('status', 'Branch created.');
    }
}
