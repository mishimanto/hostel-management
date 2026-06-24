@php
    $profile = $user->residentProfile;
    $money = fn ($value) => 'BDT '.number_format((float) $value, 2);
    $badge = fn ($status) => match ($status) {
        'paid', 'approved', 'settled' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'partial', 'pending', 'notice' => 'bg-amber-50 text-amber-700 border-amber-200',
        'rejected', 'due' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-zinc-50 text-zinc-700 border-zinc-200',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Dashboard - {{ config('app.name', 'Mini Hostel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-zinc-50 text-zinc-950">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600 font-bold text-white">MH</span>
                <span>
                    <span class="block font-semibold">Resident Portal</span>
                    <span class="block text-xs text-zinc-500">{{ $user->name }}</span>
                </span>
            </a>
            <div class="flex items-center gap-2">
                @if ($user->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-100">Admin</a>
                @endif
                <button id="markNotifications" class="rounded-md border border-zinc-300 px-3 py-2 text-sm hover:bg-zinc-100" type="button">
                    <span class="inline-flex items-center gap-2"><i data-lucide="bell" class="h-4 w-4"></i><span id="notificationCount" class="rounded-full bg-teal-600 px-2 py-0.5 text-xs text-white">0</span></span>
                </button>
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

        @unless ($profile)
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-amber-800">
                <h1 class="text-xl font-semibold">Seat assignment pending</h1>
                <p class="mt-2 text-sm">Admin has not assigned your branch, room, and seat yet.</p>
            </section>
        @else
            <section class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
                    <div>
                        <p class="text-sm font-medium text-teal-700">Current stay</p>
                        <h1 class="mt-2 text-3xl font-bold">{{ $profile->branch->name }} / Room {{ $profile->room->room_no }} / Seat {{ $profile->seat->label }}</h1>
                        <p class="mt-2 text-sm text-zinc-500">{{ $profile->branch->address }} | Joined {{ $profile->joined_at->format('M d, Y') }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-md bg-zinc-50 px-4 py-3">
                            <p class="text-xs text-zinc-500">Rent</p>
                            <p class="font-semibold">{{ $money($profile->seat->monthly_rent) }}</p>
                        </div>
                        <div class="rounded-md bg-zinc-50 px-4 py-3">
                            <p class="text-xs text-zinc-500">Balance</p>
                            <p class="font-semibold">{{ $money($profile->balance) }}</p>
                        </div>
                        <div class="rounded-md bg-zinc-50 px-4 py-3">
                            <p class="text-xs text-zinc-500">Deposit</p>
                            <p class="font-semibold">{{ $money($profile->deposit_paid) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="space-y-6">
                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="flex items-center gap-2 text-lg font-semibold"><i data-lucide="user-round" class="h-5 w-5 text-teal-700"></i> Profile</h2>
                            <span class="text-xs text-zinc-500">Editable info</span>
                        </div>
                        <form id="profileForm" data-ajax="patch" action="{{ route('profile.update') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                            @csrf
                            <input name="name" value="{{ $user->name }}" placeholder="Name" class="rounded-md border border-zinc-300 px-3 py-2">
                            <input name="phone" value="{{ $user->phone }}" placeholder="Phone" class="rounded-md border border-zinc-300 px-3 py-2">
                            <input name="nid_number" value="{{ $user->nid_number }}" placeholder="NID/ID" class="rounded-md border border-zinc-300 px-3 py-2">
                            <input name="guardian_phone" value="{{ $profile->guardian_phone }}" placeholder="Guardian phone" class="rounded-md border border-zinc-300 px-3 py-2">
                            <input name="guardian_name" value="{{ $profile->guardian_name }}" placeholder="Guardian name" class="rounded-md border border-zinc-300 px-3 py-2 sm:col-span-2">
                            <textarea name="address" rows="2" placeholder="Address" class="rounded-md border border-zinc-300 px-3 py-2 sm:col-span-2">{{ $user->address }}</textarea>
                            <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700 sm:col-span-2">Update profile</button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="flex items-center gap-2 text-lg font-semibold"><i data-lucide="bed" class="h-5 w-5 text-teal-700"></i> Seat change</h2>
                        <form id="seatChangeForm" data-ajax="post" action="{{ route('seat-change.store') }}" class="mt-4 space-y-3">
                            @csrf
                            <select id="seatSelect" name="seat_id" data-calculate-url="{{ route('seat-change.calculate') }}" class="w-full rounded-md border border-zinc-300 px-3 py-2">
                                <option value="">Select available seat</option>
                                @foreach ($availableSeats as $seat)
                                    <option value="{{ $seat->id }}">{{ $seat->room->branch->name }} - Room {{ $seat->room->room_no }} - Seat {{ $seat->label }} - {{ $money($seat->monthly_rent) }}</option>
                                @endforeach
                            </select>
                            <div id="seatPreview" class="hidden rounded-md border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900"></div>
                            <textarea name="reason" rows="2" placeholder="Why do you want to change seat?" class="w-full rounded-md border border-zinc-300 px-3 py-2"></textarea>
                            <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">Submit request</button>
                        </form>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <h2 class="flex items-center gap-2 font-semibold"><i data-lucide="calendar-days" class="h-5 w-5 text-teal-700"></i> Leave</h2>
                            <form data-ajax="post" action="{{ route('leave.store') }}" class="mt-4 space-y-3">
                                @csrf
                                <input name="start_date" type="date" class="w-full rounded-md border border-zinc-300 px-3 py-2">
                                <input name="end_date" type="date" class="w-full rounded-md border border-zinc-300 px-3 py-2">
                                <textarea name="reason" rows="2" placeholder="Reason" class="w-full rounded-md border border-zinc-300 px-3 py-2"></textarea>
                                <button class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Apply</button>
                            </form>
                        </div>
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <h2 class="flex items-center gap-2 font-semibold"><i data-lucide="door-open" class="h-5 w-5 text-teal-700"></i> Exit</h2>
                            <form id="exitForm" data-ajax="post" action="{{ route('exit.store') }}" class="mt-4 space-y-3">
                                @csrf
                                <input id="exitDate" name="requested_exit_date" data-calculate-url="{{ route('exit.calculate') }}" type="date" class="w-full rounded-md border border-zinc-300 px-3 py-2">
                                <div id="exitPreview" class="hidden rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900"></div>
                                <textarea name="reason" rows="2" placeholder="Reason" class="w-full rounded-md border border-zinc-300 px-3 py-2"></textarea>
                                <button class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700">Request exit</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Rent history</h2>
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-zinc-200 text-xs uppercase text-zinc-500">
                                    <tr><th class="py-2">Month</th><th>Due</th><th>Paid</th><th>Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100">
                                    @forelse ($user->payments as $payment)
                                        <tr>
                                            <td class="py-3 font-medium">{{ $payment->billing_month->format('M Y') }}</td>
                                            <td>{{ $money($payment->amount_due) }}</td>
                                            <td>{{ $money($payment->amount_paid) }}</td>
                                            <td><span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($payment->status) }}">{{ ucfirst($payment->status) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="py-4 text-zinc-500">No payments yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Request timeline</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            @forelse ($user->seatChangeRequests->map(fn ($item) => ['type' => 'Seat change', 'title' => $item->requestedSeat->room->branch->name.' / '.$item->requestedSeat->room->room_no.' / '.$item->requestedSeat->label, 'status' => $item->status, 'meta' => 'Payable '.$money($item->payable_amount)])
                                ->merge($user->leaveApplications->map(fn ($item) => ['type' => 'Leave', 'title' => $item->start_date->format('M d').' to '.$item->end_date->format('M d'), 'status' => $item->status, 'meta' => $item->reason]))
                                ->merge($user->exitRequests->map(fn ($item) => ['type' => 'Exit', 'title' => $item->requested_exit_date->format('M d, Y'), 'status' => $item->status, 'meta' => 'Refund '.$money($item->final_refundable)])) as $item)
                                <div class="rounded-md border border-zinc-200 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-medium">{{ $item['type'] }}: {{ $item['title'] }}</p>
                                        <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($item['status']) }}">{{ ucfirst($item['status']) }}</span>
                                    </div>
                                    <p class="mt-1 text-zinc-500">{{ $item['meta'] }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-zinc-500">No requests submitted yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Notifications</h2>
                        <div id="notificationList" class="mt-4 space-y-3">
                            @foreach ($user->hostelNotifications as $notification)
                                <div class="rounded-md border border-zinc-200 p-3">
                                    <p class="font-medium">{{ $notification->title }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">{{ $notification->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endunless
    </main>

    <script>
        window.hostelRoutes = {
            notifications: @json(route('notifications.live')),
            markNotifications: @json(route('notifications.read')),
        };
    </script>
</body>
</html>
