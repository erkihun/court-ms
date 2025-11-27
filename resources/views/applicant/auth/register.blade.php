<x-public-layout title="{{ __('auth.registration_title') }}">
    <div class="max-w-6xl mx-auto bg-white rounded-xl border border-slate-300 shadow-lg p-6 md:p-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-orange-600"
                        viewBox="0 0 24 24"
                        fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3M9 7a4 4 0 1 0-8 0 4 4 0 0 0 8 0zm-4 6c-3 0-5 1.5-5 3.5V18h7.5" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                        {{ __('auth.create_account') }}
                    </h1>
                    <p class="text-xs md:text-sm text-slate-600">
                        {{ __('auth.fill_basic_info') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('applicant.login') }}"
                class="hidden md:inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-400">
                {{ __('auth.already_have_account') }}
            </a>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs md:text-sm text-red-800">
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('applicant.register.submit') }}" class="space-y-6">
            @csrf

            {{-- Names --}}
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.first_name') }}
                    </label>
                    <input
                        name="first_name"
                        value="{{ old('first_name') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.middle_name') }}
                    </label>
                    <input
                        name="middle_name"
                        value="{{ old('middle_name') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.last_name') }}
                    </label>
                    <input
                        name="last_name"
                        value="{{ old('last_name') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            {{-- Position / Organization --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.position') }}
                    </label>
                    <input
                        name="position"
                        value="{{ old('position') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.organization_name') }}
                    </label>
                    <input
                        name="organization_name"
                        value="{{ old('organization_name') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            {{-- Gender / Phone / National ID --}}
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.gender') }}
                    </label>
                    <select
                        name="gender"
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900 bg-white
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <option value="">— {{ __('auth.select_option') }} —</option>
                        <option value="male" @selected(old('gender')==='male' )>{{ __('auth.male') }}</option>
                        <option value="female" @selected(old('gender')==='female' )>{{ __('auth.female') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.phone') }}
                    </label>
                    <input
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.national_id') }}
                    </label>
                    <input
                        name="national_id_number"
                        value="{{ old('national_id_number') }}"
                        required
                        inputmode="numeric"
                        autocomplete="off"
                        maxlength="19"
                        pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
                        title="{{ __('auth.national_id_format') }}"
                        placeholder="{{ __('auth.national_id_placeholder') }}"
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    <p class="mt-1 text-[11px] text-slate-500">
                        {{ __('auth.national_id_hint') }}
                    </p>
                </div>
            </div>

            {{-- Email / Address --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.email') }}
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700">
                        {{ __('auth.address') }}
                    </label>
                    <input
                        name="address"
                        value="{{ old('address') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            {{-- Passwords (with show/hide) --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-md font-medium text-slate-700" for="password">
                        {{ __('auth.password') }}
                    </label>
                    <div class="mt-1 relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="w-full pr-10 px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                   focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                            data-toggle-password="password"
                            aria-label="{{ __('auth.show_password') }}"
                            aria-pressed="false">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-md font-medium text-slate-700" for="password_confirmation">
                        {{ __('auth.confirm_password') }}
                    </label>
                    <div class="mt-1 relative">
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            class="w-full pr-10 px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                   focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                            data-toggle-password="password_confirmation"
                            aria-label="{{ __('auth.show_password') }}"
                            aria-pressed="false">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M2.5 12.5S5.5 6 12 6s9.5 6.5 9.5 6.5S18.5 19 12 19 2.5 12.5 2.5 12.5z" />
                                <circle cx="12" cy="12.5" r="2.5" stroke-width="1.6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Bottom actions --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <a href="{{ route('applicant.login') }}"
                    class="inline-flex items-center text-sm text-blue-700 hover:underline">
                    {{ __('auth.already_have_account') }}
                </a>

                <button
                    class="w-full md:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-orange-500 text-white text-sm font-semibold
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    {{ __('auth.create_account_button') }}
                </button>
            </div>
        </form>
    </div>

    {{-- National ID auto-formatter + password toggles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // National ID formatter
            const idInput = document.querySelector('input[name="national_id_number"]');
            if (idInput) {
                const format = (val) => {
                    const digits = (val || '').replace(/\D/g, '').slice(0, 16);
                    const parts = digits.match(/.{1,4}/g) || [];
                    return parts.join(' ');
                };

                idInput.addEventListener('input', (e) => {
                    const before = e.target.value;
                    const after = format(before);
                    e.target.value = after;
                });

                idInput.addEventListener('blur', () => {
                    idInput.value = format(idInput.value);
                });
            }

            // Show / hide password toggles
            document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
                const targetId = btn.getAttribute('data-toggle-password');
                const input = document.getElementById(targetId);
                if (!input) return;

                btn.addEventListener('click', () => {
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                });
            });
        });
    </script>
</x-public-layout>
