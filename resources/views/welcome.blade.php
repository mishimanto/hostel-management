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
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600 font-bold text-white">MH</span>
                <span>
                    <span class="block font-semibold">Mini Hostel</span>
                    <span class="block text-xs text-zinc-500">Smart resident living</span>
                </span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-medium text-zinc-600 md:flex">
                <a href="#facilities" class="hover:text-teal-700">Facilities</a>
                <a href="#services" class="hover:text-teal-700">Services</a>
                <a href="#rooms" class="hover:text-teal-700">Rooms</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold hover:bg-zinc-100">Login</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Apply</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="bg-white">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:px-8 lg:py-20">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-sm font-medium text-teal-800">
                        <i data-lucide="sparkles" class="h-4 w-4"></i>
                        Safe, clean, managed hostel living
                    </div>
                    <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-normal text-zinc-950 sm:text-5xl">Mini Hostel with online rent, leave, seat change and exit management</h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-600">Residents get a clean dashboard for payments and requests. Admin can review applications quickly, keeping hostel operations simple and transparent.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}" class="rounded-md bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-700">Open dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-md bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-700">Book your seat</a>
                            <a href="{{ route('login') }}" class="rounded-md border border-zinc-300 px-5 py-3 font-semibold hover:bg-zinc-100">Resident login</a>
                        @endauth
                    </div>
                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-4">
                        <div>
                            <p class="text-2xl font-bold">24/7</p>
                            <p class="text-sm text-zinc-500">Support</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">Online</p>
                            <p class="text-sm text-zinc-500">Requests</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">Live</p>
                            <p class="text-sm text-zinc-500">Rent status</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=80" alt="Clean shared hostel room" class="h-[460px] w-full object-cover">
                </div>
            </div>
        </section>

        <section id="facilities" class="border-y border-zinc-200 bg-zinc-50">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase text-teal-700">Facilities</p>
                    <h2 class="mt-2 text-3xl font-bold">Everything residents need day to day</h2>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['wifi', 'High speed Wi-Fi', 'Fast internet for study, work, and entertainment.'],
                        ['shield-check', 'Secure building', 'Managed entry and resident records for safer living.'],
                        ['utensils', 'Dining support', 'Simple meal and kitchen-friendly living setup.'],
                        ['washing-machine', 'Laundry area', 'Organized laundry and common utility support.'],
                        ['bed', 'Flexible seats', 'Single, two-seat, and three-seat room options.'],
                        ['wallet-cards', 'Rent tracking', 'Clear monthly status, history, and reminders.'],
                        ['calendar-check', 'Leave management', 'Apply for leave and see approval status online.'],
                        ['bell', 'Notifications', 'Announcements and request updates in one place.'],
                    ] as [$icon, $title, $body])
                        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                            <i data-lucide="{{ $icon }}" class="h-6 w-6 text-teal-700"></i>
                            <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $body }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="services" class="bg-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-3 lg:px-8">
                @foreach ([
                    ['Seat change', 'Change within the same branch or request another branch with automatic rent difference calculation.'],
                    ['Exit settlement', 'Submit notice and see final payable or refundable amount using rent, balance, and deposit.'],
                    ['Admin approval', 'Admin reviews pending requests and users receive instant dashboard notifications.'],
                ] as [$title, $body])
                    <div class="rounded-lg border border-zinc-200 p-6">
                        <h3 class="text-xl font-semibold">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-zinc-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="rooms" class="bg-zinc-900 text-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[0.8fr_1fr] lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase text-teal-300">Room types</p>
                    <h2 class="mt-2 text-3xl font-bold">Choose what fits your budget</h2>
                    <p class="mt-4 text-zinc-300">The system supports multiple branches, rooms, seat capacities, deposits, monthly rents, and availability status.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ([['1 Seat', 'Private space'], ['2 Seat', 'Balanced comfort'], ['3 Seat', 'Budget friendly']] as [$name, $desc])
                        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
                            <p class="text-2xl font-bold">{{ $name }}</p>
                            <p class="mt-2 text-sm text-zinc-300">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
