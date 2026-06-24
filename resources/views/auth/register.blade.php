<x-guest-layout>
    <h1 class="text-2xl font-semibold">Create account</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Name</span>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
        </label>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-sm font-medium">Phone</span>
                <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
            </label>
            <label class="block">
                <span class="text-sm font-medium">NID/ID</span>
                <input name="nid_number" value="{{ old('nid_number') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
            </label>
        </div>
        <label class="block">
            <span class="text-sm font-medium">Address</span>
            <textarea name="address" rows="2" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">{{ old('address') }}</textarea>
        </label>
        <label class="block">
            <span class="text-sm font-medium">Password</span>
            <input name="password" type="password" required class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Confirm password</span>
            <input name="password_confirmation" type="password" required class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 focus:border-teal-600 focus:outline-none">
        </label>
        <button class="w-full rounded-md bg-teal-600 cursor px-4 py-2 font-semibold text-white hover:bg-teal-700">Register</button>
    </form>

    <p class="mt-5 text-center text-sm text-zinc-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Log in</a>
    </p>
</x-guest-layout>
