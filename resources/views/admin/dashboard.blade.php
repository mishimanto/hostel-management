@php
    $money = fn ($value) => 'BDT '.number_format((float) $value, 2);
    $badge = fn ($status) => match ($status) {
        'approved', 'paid', 'settled' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'pending', 'partial' => 'bg-amber-50 text-amber-700 border-amber-200',
        'rejected', 'due' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-zinc-50 text-zinc-700 border-zinc-200',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - {{ config('app.name', 'Mini Hostel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-zinc-50 text-zinc-950">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-900 font-bold text-white">AD</span>
                <span>
                    <span class="block font-semibold">Admin Panel</span>
                    <span class="block text-xs text-zinc-500">Basic approval dashboard</span>
                </span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-100">Customer view</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Residents', $stats['residents'], 'users-round'],
                ['Active residents', $stats['activeResidents'], 'bed'],
                ['Pending requests', $stats['pendingRequests'], 'clock'],
                ['Rent due total', $money($stats['rentDue']), 'wallet-cards'],
            ] as [$label, $value, $icon])
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-zinc-500">{{ $label }}</p>
                        <i data-lucide="{{ $icon }}" class="h-5 w-5 text-teal-700"></i>
                    </div>
                    <p class="mt-3 text-2xl font-bold">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Seat change approvals</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($seatRequests as $request)
                        <div class="rounded-md border border-zinc-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium">{{ $request->user->name }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">
                                        {{ $request->currentSeat->room->branch->name }} / {{ $request->currentSeat->room->room_no }} / {{ $request->currentSeat->label }}
                                        to
                                        {{ $request->requestedSeat->room->branch->name }} / {{ $request->requestedSeat->room->room_no }} / {{ $request->requestedSeat->label }}
                                    </p>
                                    <p class="mt-1 text-sm text-zinc-500">Payable {{ $money($request->payable_amount) }} | Credit {{ $money($request->credit_to_next_rent) }}</p>
                                </div>
                                <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($request->status) }}">{{ ucfirst($request->status) }}</span>
                            </div>
                            @if ($request->status === 'pending')
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('admin.seat-change.review', $request) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.seat-change.review', $request) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No seat change requests.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Leave approvals</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($leaveApplications as $leave)
                        <div class="rounded-md border border-zinc-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium">{{ $leave->user->name }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">{{ $leave->reason }}</p>
                                </div>
                                <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($leave->status) }}">{{ ucfirst($leave->status) }}</span>
                            </div>
                            @if ($leave->status === 'pending')
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('admin.leave.review', $leave) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.leave.review', $leave) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No leave applications.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Exit approvals</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($exitRequests as $exit)
                        <div class="rounded-md border border-zinc-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium">{{ $exit->user->name }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">Exit date {{ $exit->requested_exit_date->format('M d, Y') }} | Notice {{ $exit->notice_days }} days</p>
                                    <p class="mt-1 text-sm text-zinc-500">Payable {{ $money($exit->final_payable) }} | Refund {{ $money($exit->final_refundable) }}</p>
                                </div>
                                <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($exit->status) }}">{{ ucfirst($exit->status) }}</span>
                            </div>
                            @if ($exit->status === 'pending')
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('admin.exit.review', $exit) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.exit.review', $exit) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No exit requests.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
