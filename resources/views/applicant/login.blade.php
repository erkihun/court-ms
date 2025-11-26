<x-public-layout title="Apply - Login">
    <h1 class="text-2xl font-semibold mb-4">Applicant Login</h1>

    <form method="POST" action="{{ route('applicant.login.submit') }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-md">
        @csrf
        <div>
            <label class="block text-sm">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 border rounded">
            @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm">Password</label>
            <input name="password" type="password" class="mt-1 w-full px-3 py-2 border rounded">
            @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded border-slate-300"> Remember me
        </label>
        <div class="flex items-center gap-3 pt-2">
            <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Sign in</button>
            <a href="{{ route('applicant.register') }}" class="text-sm text-blue-700 hover:underline">Create an account</a>
        </div>
    </form>
</x-public-layout>