<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mini Hostel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-zinc-50 text-zinc-950">
    <header class="sticky top-0 z-40 border-b border-zinc-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600 font-bold text-white">MH</span>
                <span><span class="block font-semibold">Mini Hostel</span><span class="block text-xs text-zinc-500">Room booking portal</span></span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-medium text-zinc-600 md:flex">
                <a href="#booking" class="hover:text-teal-700">Booking</a>
                <a href="#facilities" class="hover:text-teal-700">Facilities</a>
                <a href="#services" class="hover:text-teal-700">Services</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('customer.dashboard') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold hover:bg-zinc-100">Login</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Register</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="bg-white">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:px-8 lg:py-20">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-sm font-medium text-teal-800">
                        <i data-lucide="building-2" class="h-4 w-4"></i>
                        Branch-wise room booking
                    </div>
                    <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-normal sm:text-5xl">Book and manage hostel rooms online</h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-600">Customers book a full room, track rent payments, request room changes, submit leave requests, and receive notifications.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('customer.dashboard', ['section' => 'booking']) }}" class="rounded-md bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-700">Book a room</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-md bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-700">Register to book</a>
                            <a href="{{ route('login') }}" class="rounded-md border border-zinc-300 px-5 py-3 font-semibold hover:bg-zinc-100">Login</a>
                        @endauth
                    </div>
                </div>
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=80" alt="Clean hostel room" class="h-[460px] w-full object-cover">
                </div>
            </div>
        </section>

        <section id="booking" class="border-y border-zinc-200 bg-zinc-50">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase text-teal-700">Book by branch</p>
                    <h2 class="mt-2 text-3xl font-bold">Choose an available room</h2>
                    <p class="mt-4 text-zinc-600">A room can have only one approved booking. Admin approval confirms the booking.</p>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                    @auth
                        <form method="POST" action="{{ route('customer.bookings.store') }}" class="space-y-4">
                            @csrf
                    @else
                        <div class="space-y-4">
                    @endauth
                        <label class="block">
                            <span class="text-sm font-medium">Branch</span>
                            <select id="landingBranchSelect" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2">
                                <option value="">Select branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }} - {{ $branch->address }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium">Available room</span>
                            <select id="landingRoomSelect" name="room_id" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                                <option value="">Select available room</option>
                                @foreach ($branches as $branch)
                                    @foreach ($branch->rooms as $room)
                                        <option value="{{ $room->id }}" data-branch="{{ $branch->id }}">{{ $branch->name }} / Room {{ $room->room_number }} - BDT {{ number_format((float) $room->monthly_rent, 2) }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </label>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-medium">Start date</span>
                                <input name="requested_start_date" type="date" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium">End date</span>
                                <input name="requested_end_date" type="date" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                            </label>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="payment_method" class="rounded-md border border-zinc-300 px-3 py-2" placeholder="Payment method" required>
                            <input name="transaction_id" class="rounded-md border border-zinc-300 px-3 py-2" placeholder="Transaction ID">
                        </div>
                        <textarea name="payment_details" rows="2" class="w-full rounded-md border border-zinc-300 px-3 py-2" placeholder="Payment details"></textarea>
                        <textarea name="note" rows="3" class="w-full rounded-md border border-zinc-300 px-3 py-2" placeholder="Any message for admin"></textarea>
                        @auth
                            <button class="w-full rounded-md bg-teal-600 px-4 py-3 font-semibold text-white hover:bg-teal-700">Submit booking request</button>
                        @else
                            <a href="{{ route('register') }}" class="block rounded-md bg-teal-600 px-4 py-3 text-center font-semibold text-white hover:bg-teal-700">Register to book</a>
                        @endauth
                    @auth
                        </form>
                    @else
                        </div>
                    @endauth
                </div>
            </div>
        </section>

        <section id="facilities" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold">Facilities</h2>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([['wifi', 'High speed Wi-Fi'], ['shield-check', 'Secure building'], ['wallet-cards', 'Rent tracking'], ['bell', 'Notifications']] as [$icon, $title])
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-5"><i data-lucide="{{ $icon }}" class="h-6 w-6 text-teal-700"></i><h3 class="mt-4 font-semibold">{{ $title }}</h3></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="services" class="bg-zinc-900 text-white">
            <div class="mx-auto grid max-w-7xl gap-4 px-4 py-14 sm:px-6 lg:grid-cols-3 lg:px-8">
                @foreach ([['Room booking', 'Book available rooms branch-wise.'], ['Room change', 'Request changes with backend rent calculation.'], ['Leave request', 'Leave does not end room booking.']] as [$title, $body])
                    <div class="rounded-lg border border-white/10 bg-white/5 p-6"><h3 class="text-xl font-semibold">{{ $title }}</h3><p class="mt-3 text-sm text-zinc-300">{{ $body }}</p></div>
                @endforeach
            </div>
        </section>
    </main>

    <script>
        const branchSelect = document.getElementById('landingBranchSelect');
        const roomSelect = document.getElementById('landingRoomSelect');
        const filterRooms = () => {
            const branchId = branchSelect?.value || '';
            Array.from(roomSelect?.options || []).forEach((option) => {
                option.hidden = Boolean(branchId) && option.dataset.branch !== branchId && option.value !== '';
            });
            if (roomSelect?.selectedOptions[0]?.hidden) roomSelect.value = '';
        };
        branchSelect?.addEventListener('change', filterRooms);
        filterRooms();
        if (window.lucide) lucide.createIcons();
    </script>
</body>
</html>
