{{-- resources/views/apply/profile/edit.blade.php --}}
<x-public-layout title="{{ __('auth.my_profile') }}">
    <div class="max-w-6xl mx-auto bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8">

        {{-- Page header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-4 0-7 2-7 4v1h14v-1c0-2-3-4-7-4z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                        {{ __('auth.my_profile') }}
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500">
                        {{ __('auth.profile_subtitle') ?? __('auth.profile') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('applicant.dashboard') }}"
                class="hidden md:inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-400">
                {{ __('auth.back') }}
            </a>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs md:text-sm text-red-800">
            <div class="font-semibold mb-1">{{ __('auth.please_fix_errors') }}</div>
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('applicant.profile.update') }}" class="space-y-8">
            @csrf @method('PATCH')

            {{-- Personal information --}}
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="text-sm font-semibold text-slate-800">
                        {{ __('auth.profile') }}
                    </h2>
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                        {{ __('auth.profile_basic_info') ?? __('auth.basic_information') }}
                    </span>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="first_name">
                            {{ __('auth.first_name') }}
                        </label>
                        <input id="first_name" name="first_name"
                            value="{{ old('first_name', $user->first_name) }}"
                            autocomplete="given-name"
                            @error('first_name') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('first_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="middle_name">
                            {{ __('auth.middle_name') }}
                        </label>
                        <input id="middle_name" name="middle_name"
                            value="{{ old('middle_name', $user->middle_name) }}"
                            autocomplete="additional-name"
                            @error('middle_name') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('middle_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="last_name">
                            {{ __('auth.last_name') }}
                        </label>
                        <input id="last_name" name="last_name"
                            value="{{ old('last_name', $user->last_name) }}"
                            autocomplete="family-name"
                            @error('last_name') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('last_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="gender">
                            {{ __('auth.gender') }}
                        </label>
                        <select id="gender" name="gender"
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900 bg-white
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                            <option value="">— {{ __('auth.select_option') }} —</option>
                            <option value="male" @selected(old('gender', $user->gender)==='male')>{{ __('auth.male') }}</option>
                            <option value="female" @selected(old('gender', $user->gender)==='female')>{{ __('auth.female') }}</option>
                        </select>
                        @error('gender')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="phone">
                            {{ __('auth.phone') }}
                        </label>
                        <input id="phone" name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            autocomplete="tel" inputmode="tel"
                            @error('phone') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="national_id_number">
                            {{ __('auth.national_id') }}
                        </label>
                        <input id="national_id_number" name="national_id_number"
                            value="{{ old('national_id_number', $user->national_id_number) }}"
                            required
                            inputmode="numeric" autocomplete="off"
                            maxlength="19"
                            pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}"
                            title="{{ __('auth.national_id_format') }}"
                            placeholder="{{ __('auth.national_id_placeholder') }}"
                            @error('national_id_number') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('national_id_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ __('auth.national_id_hint') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="email">
                            {{ __('auth.email') }}
                        </label>
                        <input id="email" type="email" name="email"
                            value="{{ old('email', $user->email) }}"
                            autocomplete="email"
                            @error('email') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="address">
                            {{ __('auth.address') }}
                        </label>
                        <input id="address" name="address"
                            value="{{ old('address', $user->address) }}"
                            autocomplete="street-address"
                            @error('address') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('address')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="position">
                            {{ __('auth.position') }}
                        </label>
                        <input id="position" name="position"
                            value="{{ old('position', $user->position) }}"
                            autocomplete="organization-title"
                            @error('position') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('position')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="organization_name">
                            {{ __('auth.organization_name') }}
                        </label>
                        <input id="organization_name" name="organization_name"
                            value="{{ old('organization_name', $user->organization_name) }}"
                            autocomplete="organization"
                            @error('organization_name') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('organization_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-slate-200">

            {{-- Password section --}}
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="h-7 w-7 rounded-full bg-orange-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M8 11V8a4 4 0 1 1 8 0v3m-9 0h10a1 1 0 0 1 1 1v6a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-6a1 1 0 0 1 1-1z" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            {{ __('auth.change_password') }}
                        </h2>
                    </div>
                    <span class="text-[11px] text-slate-500">
                        {{ __('auth.password_optional_hint') ?? __('auth.password_optional') }}
                    </span>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="current_password">
                            {{ __('auth.current_password') }}
                        </label>
                        <input id="current_password" type="password" name="current_password"
                            autocomplete="current-password"
                            @error('current_password') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('current_password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="password">
                            {{ __('auth.new_password') }}
                        </label>
                        <input id="password" type="password" name="password"
                            autocomplete="new-password"
                            @error('password') aria-invalid="true" @enderror
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="password_confirmation">
                            {{ __('auth.confirm_new_password') }}
                        </label>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                            autocomplete="new-password"
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="pt-2 flex flex-wrap items-center gap-2">
                <button
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-orange-500 text-white text-sm font-semibold
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    {{ __('auth.save_changes') }}
                </button>
                <a href="{{ route('applicant.dashboard') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium
                          hover:bg-slate-200 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    {{ __('auth.back') }}
                </a>
            </div>
        </form>
    </div>

    {{-- Minimal, safe auto-formatter for National ID --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idInput = document.getElementById('national_id_number');
            if (!idInput) return;

            const format = (val) => {
                const digits = (val || '').replace(/\D/g, '').slice(0, 16);
                const parts = digits.match(/.{1,4}/g) || [];
                return parts.join(' ');
            };

            // Format prefilled value (from server / old input)
            idInput.value = format(idInput.value);

            idInput.addEventListener('input', (e) => {
                const before = e.target.value;
                const after = format(before);
                e.target.value = after;
            });

            idInput.addEventListener('blur', () => {
                idInput.value = format(idInput.value);
            });
        });
    </script>
</x-public-layout>
