<x-public-layout title="{{ __('auth.reset_password_title') }}">
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border bg-white p-6">

            <h1 class="text-lg font-semibold">{{ __('auth.reset_password_title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                {{ __('auth.reset_password_subtitle') }}
            </p>

            @if ($errors->any())
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('applicant.password.update') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm mb-1 font-medium text-slate-700">{{ __('auth.email') }}</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required
                        class="w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('email')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 font-medium text-slate-700">{{ __('auth.new_password') }}</label>
                    <input type="password" name="password" required
                        class="w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('password')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 font-medium text-slate-700">{{ __('auth.confirm_new_password') }}</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <button class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-colors">
                    {{ __('auth.reset_password_button') }}
                </button>
            </form>

            <div class="mt-4 text-sm text-center">
                <a href="{{ route('applicant.login') }}" class="text-slate-600 hover:text-slate-800">
                    {{ __('auth.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
</x-public-layout>