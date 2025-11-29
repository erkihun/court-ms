<x-respondant-layout :title="__('respondent.profile')">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 space-y-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.profile') }}</h1>
                <p class="text-sm text-slate-600">{{ __('respondent.profile_intro') }}</p>
            </div>

            @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('respondent.profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.first_name') }}</label>
                        <input name="first_name" value="{{ old('first_name', $respondent->first_name) }}" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('first_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.middle_name') }}</label>
                        <input name="middle_name" value="{{ old('middle_name', $respondent->middle_name) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('middle_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.last_name') }}</label>
                        <input name="last_name" value="{{ old('last_name', $respondent->last_name) }}" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('last_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.gender') }}</label>
                        <select name="gender"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                            <option value="">{{ __('respondent.select_gender') }}</option>
                            <option value="male" @selected(old('gender', $respondent->gender) === 'male')>{{ __('respondent.male') }}</option>
                            <option value="female" @selected(old('gender', $respondent->gender) === 'female')>{{ __('respondent.female') }}</option>
                            <option value="other" @selected(old('gender', $respondent->gender) === 'other')>{{ __('respondent.other') }}</option>
                        </select>
                        @error('gender') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.position') }}</label>
                        <input name="position" value="{{ old('position', $respondent->position) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('position') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.organization') }}</label>
                        <input name="organization_name" value="{{ old('organization_name', $respondent->organization_name) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('organization_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.address') }}</label>
                        <input name="address" value="{{ old('address', $respondent->address) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.national_id') }}</label>
                        <input name="national_id" value="{{ old('national_id', $respondent->national_id) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('national_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.phone') }}</label>
                        <input name="phone" value="{{ old('phone', $respondent->phone) }}" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.email') }}</label>
                        <input name="email" value="{{ old('email', $respondent->email) }}" required type="email"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                        {{ __('respondent.update_profile') }}
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('respondent.profile.password') }}" class="space-y-4 mt-6">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-semibold text-slate-900">{{ __('respondent.change_password') }}</h2>
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.current_password') }}</label>
                        <input type="password" name="current_password" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">{{ __('respondent.new_password') }}</label>
                        <input type="password" name="password" required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">{{ __('respondent.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
                <div class="text-right">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-700">
                        {{ __('respondent.update_password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-respondant-layout>
