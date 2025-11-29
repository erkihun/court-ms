<x-respondant-layout title="{{ __('respondent.registration_title') }}">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.registration_heading') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('respondent.registration_description') }}</p>
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
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.first_name') }}</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.middle_name') }}</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" required autocomplete="additional-name"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.last_name') }}</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.gender') }}</label>
                    <select name="gender"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <option value="">{{ __('respondent.select_gender') }}</option>
                        <option value="male" @selected(old('gender')==='male')>{{ __('respondent.male') }}</option>
                        <option value="female" @selected(old('gender')==='female')>{{ __('respondent.female') }}</option>
                        <option value="other" @selected(old('gender')==='other')>{{ __('respondent.other') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.position') }}</label>
                    <input type="text" name="position" value="{{ old('position') }}" required autocomplete="organization-title"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.organization_name') }}</label>
                    <input type="text" name="organization_name" value="{{ old('organization_name') }}" required autocomplete="organization"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="national_id">{{ __('respondent.national_id') }}</label>
                    <input id="national_id" type="text" name="national_id" value="{{ old('national_id') }}" required
                        pattern="\d{4}( \d{4}){3}"
                        title="{{ __('auth.national_id_format') }}"
                        placeholder="{{ __('auth.national_id_placeholder') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    <p class="text-xs text-gray-500 mt-1">{{ __('auth.national_id_hint') }}</p>
                    @error('national_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.phone') }}</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('respondent.address') }}</label>
                <input type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.confirm_email') }}</label>
                    <input type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" required autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.password') }}</label>
                    <div class="relative">
                        <input type="password" name="password" required autocomplete="new-password"
                            data-password-target
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <button type="button" data-password-toggle
                            class="absolute inset-y-0 right-2 flex items-center text-sm text-slate-500 hover:text-slate-700 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" data-visible-icon class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.5 12c1.5 3 4.5 5.5 7.5 5.5s6-2.5 7.5-5.5c-1.5-3-4.5-5.5-7.5-5.5s-6 2.5-7.5 5.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9.5a2.5 2.5 0 100 5 2.5 2.5 0 000-5z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" data-hidden-icon class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.confirm_password') }}</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                            data-password-target
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <button type="button" data-password-toggle
                            class="absolute inset-y-0 right-2 flex items-center text-sm text-slate-500 hover:text-slate-700 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" data-visible-icon class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.5 12c1.5 3 4.5 5.5 7.5 5.5s6-2.5 7.5-5.5c-1.5-3-4.5-5.5-7.5-5.5s-6 2.5-7.5 5.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9.5a2.5 2.5 0 100 5 2.5 2.5 0 000-5z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" data-hidden-icon class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    {{ __('respondent.register_action') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    const container = toggle.closest('.relative');
                    if (!container) return;
                    const target = container.querySelector('[data-password-target]');
                    if (!target) return;

                    const isHidden = target.type === 'password';
                    target.type = isHidden ? 'text' : 'password';

                    const visibleIcon = toggle.querySelector('[data-visible-icon]');
                    const hiddenIcon = toggle.querySelector('[data-hidden-icon]');

                    if (visibleIcon && hiddenIcon) {
                        visibleIcon.classList.toggle('hidden', !isHidden);
                        hiddenIcon.classList.toggle('hidden', isHidden);
                    }
                });
            });

            const nationalIdInput = document.getElementById('national_id');
            if (nationalIdInput) {
                nationalIdInput.addEventListener('input', function () {
                    let digits = this.value.replace(/\D/g, '').slice(0, 16);
                    this.value = digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
                });

                nationalIdInput.form?.addEventListener('submit', function () {
                    nationalIdInput.value = nationalIdInput.value.replace(/\D/g, '');
                });
            }
        });
    </script>
</x-respondant-layout>
