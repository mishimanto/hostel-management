<x-guest-layout>
    <h1 class="text-2xl font-semibold">Log in</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email', 'test@example.com') }}" required autofocus class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Password</span>
            <input name="password" type="password" required class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
        </label>
        <label class="flex items-center gap-2 text-sm text-zinc-600">
            <input name="remember" type="checkbox" class="rounded border-zinc-300 text-teal-600"> Remember me
        </label>
        <button class="w-full rounded-md bg-teal-600 px-4 py-2 font-semibold text-white hover:bg-teal-700">Log in</button>
    </form>

    <p class="mt-5 text-center text-sm text-zinc-500">
        New resident?
        <a href="{{ route('register') }}" class="font-medium text-teal-700 hover:underline">Create account</a>
    </p>
</x-guest-layout>
