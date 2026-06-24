@php
    $money = fn ($value) => 'BDT '.number_format((float) $value, 2);
    $badge = fn ($status) => match ($status) {
        'approved', 'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'pending', 'partial' => 'bg-amber-50 text-amber-700 border-amber-200',
        'rejected', 'due', 'cancelled' => 'bg-red-50 text-red-700 border-red-200',
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
<body class="bg-zinc-100 text-zinc-950">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <aside class="bg-zinc-950 text-white lg:sticky lg:top-0 lg:h-screen">
            <div class="border-b border-white/10 p-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3"><span><span class="block font-semibold text-xl">Admin Panel</span></span></a>
            </div>
            <nav class="grid gap-1 px-3 py-4 text-sm">
                @foreach ([['#overview', 'Dashboard', 'layout-dashboard'], ['#branches', 'Branches', 'building-2'], ['#rooms', 'Rooms', 'door-open'], ['#bookings', 'Bookings', 'book-open-check'], ['#changes', 'Room Changes', 'replace'], ['#leaves', 'Leaves', 'calendar-days']] as [$href, $label, $icon])
                    <a href="{{ $href }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-zinc-300 hover:bg-white/10 hover:text-white"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i>{{ $label }}</a>
                @endforeach
                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-zinc-300 hover:bg-white/10 hover:text-white"><i data-lucide="user-round" class="h-4 w-4"></i>Customer View</a>
            </nav>
            <div class="mt-auto p-4">
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-zinc-950">Logout</button></form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 backdrop-blur">
                <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div><h1 class="text-2xl font-bold">Dashboard</h1></div>
                    <a href="{{ route('home') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-100">Front page</a>
                </div>
            </header>

            <main class="flex-1 space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>
                @endif

                <section id="overview" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([['Residents', $stats['residents'], 'users-round'], ['Booked rooms', $stats['bookedRooms'], 'door-open'], ['Pending requests', $stats['pendingRequests'], 'clock'], ['Rent due', $money($stats['rentDue']), 'wallet-cards']] as [$label, $value, $icon])
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><p class="text-sm text-zinc-500">{{ $label }}</p><i data-lucide="{{ $icon }}" class="h-5 w-5 text-teal-700"></i></div><p class="mt-4 text-2xl font-bold">{{ $value }}</p></div>
                    @endforeach
                </section>

                <section class="grid gap-6 xl:grid-cols-2">
                    <div id="branches" class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Branch management</h2>
                        <form method="POST" action="{{ route('admin.branches.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                            @csrf
                            <input name="name" placeholder="Branch name" class="rounded-md border border-zinc-300 px-3 py-2" required>
                            <input name="code" placeholder="Code" class="rounded-md border border-zinc-300 px-3 py-2" required>
                            <input name="phone" placeholder="Phone" class="rounded-md border border-zinc-300 px-3 py-2">
                            <input name="rent_due_day" type="number" min="1" max="28" value="5" class="rounded-md border border-zinc-300 px-3 py-2" required>
                            <textarea name="address" placeholder="Address" class="rounded-md border border-zinc-300 px-3 py-2 sm:col-span-2" required></textarea>
                            <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white sm:col-span-2">Create branch</button>
                        </form>
                    </div>

                    <div id="rooms" class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Room management</h2>
                        <form method="POST" action="{{ route('admin.rooms.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                            @csrf
                            <select name="branch_id" class="rounded-md border border-zinc-300 px-3 py-2" required><option value="">Select branch</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select>
                            <input name="room_number" placeholder="Room number" class="rounded-md border border-zinc-300 px-3 py-2" required>
                            <input name="monthly_rent" type="number" min="0" step="0.01" placeholder="Monthly rent" class="rounded-md border border-zinc-300 px-3 py-2" required>
                            <select name="status" class="rounded-md border border-zinc-300 px-3 py-2" required><option value="available">Available</option><option value="maintenance">Maintenance</option><option value="booked">Booked</option></select>
                            <textarea name="description" placeholder="Description" class="rounded-md border border-zinc-300 px-3 py-2 sm:col-span-2"></textarea>
                            <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white sm:col-span-2">Create room</button>
                        </form>
                    </div>
                </section>

                <section id="bookings" class="rounded-lg border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-200 px-5 py-4"><h2 class="text-lg font-semibold">Room booking approvals</h2></div>
                    <div class="divide-y divide-zinc-100">
                        @forelse ($bookingRequests as $booking)
                            <article class="p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $booking->user->name }}</p>
                                        <p class="mt-1 text-sm text-zinc-500">{{ $booking->branch->name }} / Room {{ $booking->room->room_number }} / {{ $money($booking->monthly_rent) }}</p>
                                        <p class="mt-1 text-sm text-zinc-500">{{ $booking->requested_start_date?->format('M d, Y') }} to {{ $booking->requested_end_date?->format('M d, Y') }} | {{ $booking->total_days }} days | Payable {{ $money($booking->payable_amount) }}</p>
                                        <p class="mt-1 text-sm text-zinc-500">Payment: {{ $booking->payment_method }} @if($booking->transaction_id) | TXN {{ $booking->transaction_id }} @endif</p>
                                        @if ($booking->payment_details)
                                            <p class="mt-1 text-sm text-zinc-500">{{ $booking->payment_details }}</p>
                                        @endif
                                    </div>
                                    <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($booking->status) }}">{{ ucfirst($booking->status) }}</span>
                                </div>
                                @if ($booking->status === 'pending')
                                    <div class="mt-4 flex gap-2">
                                        <form method="POST" action="{{ route('admin.bookings.review', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white">Approve payment</button></form>
                                        <form method="POST" action="{{ route('admin.bookings.review', $booking) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white">Reject</button></form>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="p-5 text-sm text-zinc-500">No room booking requests.</p>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-2">
                    <div id="changes" class="rounded-lg border border-zinc-200 bg-white shadow-sm">
                        <div class="border-b border-zinc-200 px-5 py-4"><h2 class="text-lg font-semibold">Room change approvals</h2></div>
                        <div class="divide-y divide-zinc-100">
                            @forelse ($roomChangeRequests as $change)
                                <article class="p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-semibold">{{ $change->user->name }}</p><p class="mt-1 text-sm text-zinc-500">Booking #{{ $change->room_booking_id }}: {{ $change->currentRoom->branch->name }} / Room {{ $change->currentRoom->room_number }} to {{ $change->requestedRoom->branch->name }} / Room {{ $change->requestedRoom->room_number }}</p><p class="mt-1 text-sm text-zinc-500">Payable {{ $money($change->additional_payable) }} | Extra days {{ $change->extra_days }} | New paid until {{ $change->new_paid_until?->format('M d, Y') }}</p></div><span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($change->status) }}">{{ ucfirst($change->status) }}</span></div>
                                    @if ($change->status === 'pending')
                                        <div class="mt-4 flex gap-2"><form method="POST" action="{{ route('admin.room-change.review', $change) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white">Approve</button></form><form method="POST" action="{{ route('admin.room-change.review', $change) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white">Reject</button></form></div>
                                    @endif
                                </article>
                            @empty
                                <p class="p-5 text-sm text-zinc-500">No room change requests.</p>
                            @endforelse
                        </div>
                    </div>

                    <div id="leaves" class="rounded-lg border border-zinc-200 bg-white shadow-sm">
                        <div class="border-b border-zinc-200 px-5 py-4"><h2 class="text-lg font-semibold">Leave approvals</h2></div>
                        <div class="divide-y divide-zinc-100">
                            @forelse ($leaveRequests as $leave)
                                <article class="p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-semibold">{{ $leave->user->name }}</p><p class="mt-1 text-sm text-zinc-500">Booking #{{ $leave->room_booking_id }} / {{ $leave->roomBooking?->room?->branch?->name }} / Room {{ $leave->roomBooking?->room?->room_number }}</p><p class="mt-1 text-sm text-zinc-500">{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }}</p><p class="mt-1 text-sm text-zinc-500">{{ $leave->reason }}</p></div><span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($leave->status) }}">{{ ucfirst($leave->status) }}</span></div>
                                    @if ($leave->status === 'pending')
                                        <div class="mt-4 flex gap-2"><form method="POST" action="{{ route('admin.leave-requests.review', $leave) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white">Approve</button></form><form method="POST" action="{{ route('admin.leave-requests.review', $leave) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white">Reject</button></form></div>
                                    @endif
                                </article>
                            @empty
                                <p class="p-5 text-sm text-zinc-500">No leave requests.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </main>
            <footer class="border-t border-zinc-200 bg-white px-4 py-4 text-sm text-zinc-500 sm:px-6 lg:px-8">Mini Hostel admin panel</footer>
        </div>
    </div>
    <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
