@php
    $booking = $user->activeRoomBooking;
    $section = in_array($section, ['overview', 'booking', 'profile', 'rent', 'requests', 'notifications'], true) ? $section : 'overview';
    $money = fn ($value) => 'BDT '.number_format((float) $value, 2);
    $badge = fn ($status) => match ($status) {
        'paid', 'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'partial', 'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'rejected', 'due', 'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-zinc-50 text-zinc-700 border-zinc-200',
    };
    $navClass = fn ($name) => $section === $name
        ? 'bg-teal-50 text-teal-800 border-teal-200'
        : 'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50';
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
<body class="bg-zinc-100 text-zinc-950">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600 font-bold text-white">MH</span>
                <span><span class="block font-semibold">Customer Portal</span><span class="block text-xs text-zinc-500">{{ $user->name }}</span></span>
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

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[240px_1fr] lg:px-8">
        <aside class="space-y-2">
            @foreach ([['overview', 'Overview', 'layout-dashboard'], ['booking', 'Booking', 'book-open-check'], ['profile', 'Profile', 'user-round'], ['rent', 'Rent', 'wallet-cards'], ['requests', 'Requests', 'clipboard-list'], ['notifications', 'Notifications', 'bell']] as [$name, $label, $icon])
                <a href="{{ route('customer.dashboard', ['section' => $name]) }}" class="flex items-center gap-3 rounded-md border px-3 py-2 text-sm font-medium {{ $navClass($name) }}">
                    <i data-lucide="{{ $icon }}" class="h-4 w-4"></i> {{ $label }}
                </a>
            @endforeach
        </aside>

        <section>
            @if (session('status'))
                <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif

            @if ($section === 'overview')
                <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-teal-700">Welcome back</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $user->name }}</h1>
                    @if ($booking)
                        <p class="mt-2 text-sm text-zinc-500">{{ $booking->branch->name }} / Room {{ $booking->room->room_number }}</p>
                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-md bg-zinc-50 p-4"><p class="text-xs text-zinc-500">Monthly rent</p><p class="mt-1 font-semibold">{{ $money($booking->monthly_rent) }}</p></div>
                            <div class="rounded-md bg-zinc-50 p-4"><p class="text-xs text-zinc-500">Paid until</p><p class="mt-1 font-semibold">{{ $booking->paid_until?->format('M d, Y') ?? 'Not set' }}</p></div>
                            <div class="rounded-md bg-zinc-50 p-4"><p class="text-xs text-zinc-500">Status</p><p class="mt-1 font-semibold">{{ ucfirst($booking->status) }}</p></div>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-zinc-500">You do not have an approved room yet. Submit a booking request first.</p>
                        <a href="{{ route('customer.dashboard', ['section' => 'booking']) }}" class="mt-5 inline-flex rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">Book a room</a>
                    @endif
                </div>
            @endif

            @if ($section === 'booking')
                <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Book a room</h2>
                        <p class="mt-1 text-sm text-zinc-500">Choose an available room. Admin approval is required.</p>
                        <form method="POST" action="{{ route('customer.bookings.store') }}" class="mt-4 space-y-3">
                            @csrf
                            <select name="room_id" class="w-full rounded-md border border-zinc-300 px-3 py-2" required>
                                <option value="">Select available room</option>
                                @foreach ($availableRooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->branch->name }} / Room {{ $room->room_number }} - {{ $money($room->monthly_rent) }}</option>
                                @endforeach
                            </select>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="requested_start_date" type="date" class="rounded-md border border-zinc-300 px-3 py-2" required>
                                <input name="requested_end_date" type="date" class="rounded-md border border-zinc-300 px-3 py-2" required>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="payment_method" placeholder="Payment method, e.g. bKash" class="rounded-md border border-zinc-300 px-3 py-2" required>
                                <input name="transaction_id" placeholder="Transaction ID" class="rounded-md border border-zinc-300 px-3 py-2">
                            </div>
                            <textarea name="payment_details" rows="2" class="w-full rounded-md border border-zinc-300 px-3 py-2" placeholder="Payment details / sender number / reference"></textarea>
                            <textarea name="note" rows="3" class="w-full rounded-md border border-zinc-300 px-3 py-2" placeholder="Message for admin"></textarea>
                            <p class="text-xs text-zinc-500">Payable amount will be recalculated on the backend from selected dates using fixed 30-day rent.</p>
                            <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">Submit booking and payment details</button>
                        </form>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Booking history</h2>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->roomBookings as $item)
                                <div class="rounded-md border border-zinc-200 p-3 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-medium">{{ $item->branch->name }} / Room {{ $item->room->room_number }}</p>
                                        <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($item->status) }}">{{ ucfirst($item->status) }}</span>
                                    </div>
                                    <p class="mt-1 text-zinc-500">
                                        {{ $item->requested_start_date?->format('M d, Y') }} to {{ $item->requested_end_date?->format('M d, Y') }}
                                        | Payable {{ $money($item->payable_amount) }}
                                        | {{ $item->payment_method }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-sm text-zinc-500">No booking request yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if ($section === 'profile')
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Profile information</h2>
                    <form id="profileForm" data-ajax="patch" action="{{ route('customer.profile.update') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <input name="name" value="{{ $user->name }}" placeholder="Name" class="rounded-md border border-zinc-300 px-3 py-2">
                        <input name="phone" value="{{ $user->phone }}" placeholder="Phone" class="rounded-md border border-zinc-300 px-3 py-2">
                        <input name="nid_number" value="{{ $user->nid_number }}" placeholder="NID/ID" class="rounded-md border border-zinc-300 px-3 py-2">
                        <textarea name="address" rows="2" placeholder="Address" class="rounded-md border border-zinc-300 px-3 py-2 sm:col-span-2">{{ $user->address }}</textarea>
                        <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700 sm:col-span-2">Update profile</button>
                    </form>
                </div>
            @endif

            @if ($section === 'rent')
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Rent and payment history</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-zinc-200 text-xs uppercase text-zinc-500"><tr><th class="py-2">Month</th><th>Due</th><th>Paid</th><th>Status</th></tr></thead>
                            <tbody class="divide-y divide-zinc-100">
                                @forelse ($user->payments as $payment)
                                    <tr><td class="py-3 font-medium">{{ $payment->billing_month->format('M Y') }}</td><td>{{ $money($payment->amount_due) }}</td><td>{{ $money($payment->amount_paid) }}</td><td><span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($payment->status) }}">{{ ucfirst($payment->status) }}</span></td></tr>
                                @empty
                                    <tr><td colspan="4" class="py-4 text-zinc-500">No payments yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($section === 'requests')
                <div class="space-y-6">
                    <div class="grid gap-6 xl:grid-cols-2">
                        @if ($approvedBookings->isNotEmpty())
                            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                                <h2 class="text-lg font-semibold">Room change</h2>
                                <form id="roomChangeForm" data-ajax="post" action="{{ route('customer.room-change.store') }}" class="mt-4 space-y-3">
                                    @csrf
                                    <select id="roomBookingSelect" name="room_booking_id" class="w-full rounded-md border border-zinc-300 px-3 py-2" required>
                                        <option value="">Select your booked room</option>
                                        @foreach ($approvedBookings as $booked)
                                            <option value="{{ $booked->id }}">
                                                Booking #{{ $booked->id }} - {{ $booked->branch->name }} / Room {{ $booked->room->room_number }} / Paid until {{ $booked->paid_until?->format('M d, Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select id="roomSelect" name="room_id" data-calculate-url="{{ route('customer.room-change.calculate') }}" class="w-full rounded-md border border-zinc-300 px-3 py-2">
                                        <option value="">Select available room</option>
                                        @foreach ($availableRooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->branch->name }} / Room {{ $room->room_number }} - {{ $money($room->monthly_rent) }}</option>
                                        @endforeach
                                    </select>
                                    <div id="roomPreview" class="hidden rounded-md border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900"></div>
                                    <p class="text-xs text-zinc-500">Change date is set by the system from this booking request. Only the room can be changed.</p>
                                    <textarea name="reason" required rows="2" placeholder="Reason" class="w-full rounded-md border border-zinc-300 px-3 py-2"></textarea>
                                    <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">Submit</button>
                                </form>
                            </div>
                        @endif
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <h2 class="text-lg font-semibold">Leave request</h2>
                            <form data-ajax="post" action="{{ route('customer.leave-requests.store') }}" class="mt-4 space-y-3">
                                @csrf
                                <select id="leaveBookingSelect" name="room_booking_id" class="w-full rounded-md border border-zinc-300 px-3 py-2" required>
                                    <option value="">Select your booked room</option>
                                    @foreach ($approvedBookings as $booked)
                                        @php
                                            $leaveStart = ($booked->started_at ?? $booked->requested_start_date)?->toDateString();
                                            $leaveEnd = ($booked->paid_until ?? $booked->requested_end_date)?->toDateString();
                                        @endphp
                                        <option value="{{ $booked->id }}" data-start="{{ $leaveStart }}" data-end="{{ $leaveEnd }}">
                                            Booking #{{ $booked->id }} - {{ $booked->branch->name }} / Room {{ $booked->room->room_number }} / {{ $leaveStart }} to {{ $leaveEnd }}
                                        </option>
                                    @endforeach
                                </select>
                                <input id="leaveDate" name="leave_date" type="date" class="w-full rounded-md border border-zinc-300 px-3 py-2" required>
                                <p id="leaveDateHint" class="text-xs text-zinc-500">Select a booking first. Leave dates must stay inside that booking range.</p>
                                <textarea name="reason" required rows="2" placeholder="Reason" class="w-full rounded-md border border-zinc-300 px-3 py-2"></textarea>
                                <button class="rounded-md bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">Apply</button>
                            </form>
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Request status history</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            @forelse ($user->roomChangeRequests->map(fn ($item) => ['type' => 'Room change', 'title' => 'Booking #'.$item->room_booking_id.' to '.$item->requestedRoom->branch->name.' / Room '.$item->requestedRoom->room_number, 'status' => $item->status, 'meta' => 'Payable '.$money($item->additional_payable).' | Extra days '.$item->extra_days])
                                ->merge($user->leaveRequests->map(fn ($item) => ['type' => 'Leave', 'title' => 'Booking #'.$item->room_booking_id.' / '.$item->roomBooking?->room?->room_number.' - '.$item->start_date->format('M d, Y'), 'status' => $item->status, 'meta' => $item->reason])) as $item)
                                <div class="rounded-md border border-zinc-200 p-3">
                                    <div class="flex items-center justify-between gap-3"><p class="font-medium">{{ $item['type'] }}: {{ $item['title'] }}</p><span class="rounded-full border px-2 py-0.5 text-xs {{ $badge($item['status']) }}">{{ ucfirst($item['status']) }}</span></div>
                                    <p class="mt-1 text-zinc-500">{{ $item['meta'] }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-zinc-500">No requests submitted yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if ($section === 'notifications')
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Notifications</h2>
                    <div id="notificationList" class="mt-4 space-y-3">
                        @foreach ($user->notifications as $notification)
                            <div class="rounded-md border border-zinc-200 p-3"><p class="font-medium">{{ $notification->title }}</p><p class="mt-1 text-sm text-zinc-500">{{ $notification->body }}</p></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </main>

    <script>
        window.hostelRoutes = {
            notifications: @json(route('customer.notifications.live')),
            markNotifications: @json(route('customer.notifications.read')),
        };
    </script>
</body>
</html>
