{{-- resources/views/apply/profile/edit.blade.php --}}
<x-applicant-layout title="{{ __('auth.my_profile') }}">
    @php
        $securityError = $errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation');
    @endphp
    <div class="max-w-6xl mx-auto bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8"
        x-data="{ tab: @js($securityError || session('security_success')) || window.location.hash === '#security' ? 'security' : 'profile' }"
        @hashchange.window="tab = window.location.hash === '#security' ? 'security' : 'profile'">

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
                    <p class="text-xs md: text-slate-500">
                        {{ __('auth.fill_basic_info') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('applicant.dashboard') }}"
                class="hidden md:inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-400">
                {{ __('auth.back') }}
            </a>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-4" aria-label="{{ __('auth.profile_section') }}">
            <a href="#profile" @click="tab = 'profile'"
                :class="tab === 'profile' ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-700'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition hover:border-blue-300">
                {{ __('auth.profile_section') }}
            </a>
            <a href="#security" @click="tab = 'security'"
                :class="tab === 'security' ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-700'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition hover:border-blue-300">
                {{ __('auth.security_section') }}
            </a>
            <a href="{{ route('applicant.profile.sessions.index') }}"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-300">
                {{ __('auth.sessions_title') }}
            </a>
        </nav>

        {{-- Flash messages --}}
        @if(session('success') || session('security_success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3  text-emerald-800">
            {{ session('success') ?? session('security_success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs md: text-red-800">
            <div class="font-semibold mb-1">{{ __('auth.please_fix_errors') }}</div>
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="profile" x-show="tab === 'profile'" x-cloak method="POST" action="{{ route('applicant.profile.update') }}" class="space-y-8"
            enctype="multipart/form-data">
            @csrf @method('PATCH')

            {{-- Personal information --}}
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class=" font-semibold text-slate-800">
                        {{ __('auth.basic_information') }}
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900 bg-white
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
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
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                                      focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('organization_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-slate-200">

            {{-- Lawyer document --}}
            @if($user->is_lawyer)
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold text-slate-800">{{ __('auth.lawyer_document') }}</h2>
                </div>

                @if(!empty($user->lawyer_document_path))
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <span class="inline-flex items-center gap-2 text-sm font-medium text-emerald-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('auth.lawyer_document_uploaded') }}
                    </span>
                    <a href="{{ route('applicant.profile.lawyer-document') }}" target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ __('cases.show.view_lawyer_document') }}
                    </a>
                </div>
                <label class="mt-3 block text-xs font-medium text-slate-600" for="lawyer_document">
                    {{ __('auth.lawyer_document_replace') }}
                </label>
                @else
                <label class="block text-xs font-medium text-slate-600" for="lawyer_document">
                    {{ __('auth.lawyer_document') }}
                </label>
                @endif

                <input id="lawyer_document" type="file" name="lawyer_document" accept=".pdf"
                    class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300 text-slate-900
                           focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                <p class="text-xs text-slate-500 mt-1">{{ __('auth.lawyer_document_hint') }}</p>
                @error('lawyer_document')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </section>
            @endif

            {{-- Actions --}}
            <div class="pt-2 flex flex-wrap items-center gap-2">
                <button
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-orange-500 text-white  font-semibold
                           hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    {{ __('auth.save_changes') }}
                </button>
                <a href="{{ route('applicant.dashboard') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-slate-100 text-slate-700  font-medium
                          hover:bg-slate-200 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    {{ __('auth.back') }}
                </a>
            </div>
        </form>

        <section id="security" x-show="tab === 'security'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('auth.security_section') }}</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">{{ __('auth.profile.password_security_hint') }}</p>
            </div>

            <form method="POST" action="{{ route('applicant.profile.password.update') }}"
                class="rounded-xl border border-slate-200 bg-white p-5">
                @csrf
                @method('PATCH')
                <h3 class="text-base font-semibold text-slate-900">{{ __('auth.change_password') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('auth.password_optional_hint') ?? __('auth.password_optional') }}</p>

                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="security_current_password">{{ __('auth.current_password') }}</label>
                        <input id="security_current_password" type="password" name="current_password" autocomplete="current-password" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="security_password">{{ __('auth.new_password') }}</label>
                        <input id="security_password" type="password" name="password" autocomplete="new-password" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="security_password_confirmation">{{ __('auth.confirm_new_password') }}</label>
                        <input id="security_password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                        {{ __('auth.save_changes') }}
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- Minimal, safe auto-formatter for National ID --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idInput = document.getElementById('national_id_number');
            if (!idInput) return;

            const format = (val) => {
                const cleaned = (val || '')
                    .replace(/[^A-Za-z0-9]/g, '')
                    .slice(0, 16)
                    .toUpperCase();
                const parts = cleaned.match(/.{1,4}/g) || [];
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
</x-applicant-layout>
