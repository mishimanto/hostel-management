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
            {{ $slot }}
        </div>
    </main>
</body>
</html>
