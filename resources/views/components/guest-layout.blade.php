<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mini Hostel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-950">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600 font-bold text-white">MH</span>
                <span>
                    <span class="block text-lg font-semibold">Mini Hostel</span>
                    <span class="block text-sm text-zinc-500">Customer portal</span>
                </span>
            </a>

            {{ $slot }}
        </div>
    </main>
</body>
</html>
