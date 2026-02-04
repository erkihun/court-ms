@php
$settings = null;
try {
    $settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
    $settings = null;
}
$bannerPath = $settings?->banner_path ?? null;
@endphp

<x-guest-layout>
    @if ($bannerPath)
    @push('head')
    <style>
        body {
            background: url("{{ asset('storage/'.$bannerPath) }}") center / cover no-repeat,
            #f8fafc;
            backdrop-filter: blur(6px);
        }

        .guest-container {
            background: transparent;
        }

        .lawyer-register-card {
            backdrop-filter: blur(6px);
            background-color: rgba(255, 255, 255, 0.92);
        }
    </style>
    @endpush
    @endif

    <div class="max-w-6xl mx-auto rounded-2xl border border-slate-200 bg-white shadow-lg lawyer-register-card p-6 md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-2xl bg-indigo-50 text-indigo-600 grid place-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 14l3-3 3 3M12 7v7" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Lawyer Portal</p>
                    <h1 class="text-xl font-semibold text-slate-900">Create a lawyer account</h1>
                </div>
            </div>
            <a href="{{ route('applicant.login') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400">
                {{ __('auth.already_have_account') }}
            </a>
        </div>

        @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('lawyer.register.submit') ?? '#' }}" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.first_name') }}</label>
                    <input name="first_name" value="{{ old('first_name') }}" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.middle_name') }}</label>
                    <input name="middle_name" value="{{ old('middle_name') }}" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.last_name') }}</label>
                    <input name="last_name" value="{{ old('last_name') }}" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.gender') }}</label>
                    <select name="gender"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('auth.select_option') }}</option>
                        <option value="male" @selected(old('gender') === 'male')>{{ __('auth.male') }}</option>
                        <option value="female" @selected(old('gender') === 'female')>{{ __('auth.female') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.phone') }}</label>
                    <input name="phone" value="{{ old('phone') }}" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.national_id') }}</label>
                    <input name="national_id_number" value="{{ old('national_id_number') }}" required inputmode="numeric"
                        autocomplete="off" maxlength="19" pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
                        title="Format: XXXX XXXX XXXX XXXX (only numbers allowed)" placeholder="1234 5678 9012 3456"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">{{ __('auth.national_id_hint') }}</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('auth.address') }}</label>
                    <input name="address" value="{{ old('address') }}" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password">{{ __('auth.password') }}</label>
                    <div class="mt-1 relative">
                        <input id="password" type="password" name="password" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                            data-toggle-password="password" aria-label="{{ __('auth.show_password') }}" aria-pressed="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password_confirmation">{{ __('auth.confirm_password') }}</label>
                    <div class="mt-1 relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                            data-toggle-password="password_confirmation" aria-label="{{ __('auth.show_password') }}"
                            aria-pressed="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-dashed border-slate-200 pt-4 text-sm text-slate-600">
                <p>By clicking “Create account” you confirm that the information provided is accurate and that you accept the platform terms.</p>
                <button
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1">
                    {{ __('auth.create_account_button') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formatNationalId = (value) => {
                const digits = (value || '').replace(/\D/g, '').slice(0, 16);
                const segments = digits.match(/.{1,4}/g) || [];
                return segments.join(' ');
            };

            const nationalIdInput = document.querySelector('input[name="national_id_number"]');
            if (nationalIdInput) {
                nationalIdInput.addEventListener('input', (event) => {
                    event.target.value = formatNationalId(event.target.value);
                });
                nationalIdInput.addEventListener('blur', () => {
                    nationalIdInput.value = formatNationalId(nationalIdInput.value);
                });
            }

            document.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
                const targetId = toggle.getAttribute('data-toggle-password');
                const input = document.getElementById(targetId);
                if (!input) {
                    return;
                }
                toggle.addEventListener('click', () => {
                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    toggle.setAttribute('aria-pressed', (!visible).toString());
                });
            });
        });
    </script>
</x-guest-layout>
