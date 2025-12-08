<x-applicant-layout :title="__('respondent.profile')" :as-respondent-nav="true">
    <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.profile') }}</h1>
            <p class="text-sm text-slate-600">{{ __('respondent.profile_intro') }}</p>
        </div>
        <form method="POST" action="{{ route('respondent.profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.first_name') }}</label>
                    <input name="first_name" value="{{ old('first_name', $respondent->first_name) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.last_name') }}</label>
                    <input name="last_name" value="{{ old('last_name', $respondent->last_name) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.phone') }}</label>
                    <input name="phone" value="{{ old('phone', $respondent->phone) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.email') }}</label>
                    <input name="email" type="email" value="{{ old('email', $respondent->email) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('respondent.address') }}</label>
                <input name="address" value="{{ old('address', $respondent->address) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="text-right">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                    {{ __('respondent.update_profile') }}
                </button>
            </div>
        </form>

        <div class="border-t border-slate-200 pt-4">
            <h2 class="text-lg font-semibold text-slate-900 mb-2">{{ __('respondent.change_password') }}</h2>
            <form method="POST" action="{{ route('respondent.profile.password') }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.current_password') }}</label>
                    <input type="password" name="current_password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.new_password') }}</label>
                    <input type="password" name="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('respondent.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="text-right">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">
                        {{ __('respondent.update_password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-applicant-layout>
