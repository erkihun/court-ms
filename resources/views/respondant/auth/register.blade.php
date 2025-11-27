<x-respondant-layout title="{{ __('Respondent Registration') }}">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Respondent / Accused Registration') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('Provide your information to request access.') }}</p>
            </div>
        </div>

        @if (session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('respondent.register.submit') }}" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('First Name') }}</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Middle Name') }}</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" required autocomplete="additional-name"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Last Name') }}</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Gender') }}</label>
                    <select name="gender"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <option value="">{{ __('Select') }}</option>
                        <option value="male" @selected(old('gender')==='male')>{{ __('Male') }}</option>
                        <option value="female" @selected(old('gender')==='female')>{{ __('Female') }}</option>
                        <option value="other" @selected(old('gender')==='other')>{{ __('Other') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Position') }}</label>
                    <input type="text" name="position" value="{{ old('position') }}" required autocomplete="organization-title"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Organization Name') }}</label>
                    <input type="text" name="organization_name" value="{{ old('organization_name') }}" required autocomplete="organization"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
                    <input type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Phone Number') }}</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Confirm Email') }}</label>
                    <input type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" required autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    {{ __('Register') }}
                </button>
            </div>
        </form>
    </div>
</x-respondant-layout>
