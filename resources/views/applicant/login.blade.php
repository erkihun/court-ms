<x-applicant-layout title="Apply - Login" hide-footer="true">
    <div class="min-h-[70vh] flex items-center justify-center">
        <form method="POST" action="{{ route('applicant.login.submit') }}" class="bg-white p-6 rounded-lg border space-y-4 w-full max-w-md shadow">
            @csrf
            <h1 class="text-2xl font-semibold mb-2 text-center">Applicant Login</h1>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 border rounded" required>
                @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Password</label>
                <input name="password" type="password" class="mt-1 w-full px-3 py-2 border rounded" required>
                @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" class="rounded border-slate-300">
                Remember me
            </label>
            <div class="flex items-center gap-3 pt-2">
                <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Sign in</button>
                <a href="{{ route('applicant.register') }}" class="text-sm text-blue-700 hover:underline">Create an account</a>
            </div>
        </form>
    </div>
</x-applicant-layout>
