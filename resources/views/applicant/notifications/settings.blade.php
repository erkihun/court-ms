{{-- resources/views/applicant/notifications/settings.blade.php --}}
<x-applicant-layout title="{{ __('app.notification_preferences.title') }}">
    <div class="max-w-xl space-y-4">
        <h1 class="text-lg font-semibold text-slate-800">{{ __('app.notification_preferences.email_title') }}</h1>

        @if(session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('applicant.notifications.settings.update') }}"
            class="rounded-xl border bg-white p-5">
            @csrf

            <div class="space-y-4">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="email_status" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_status ?? 1) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">{{ __('app.notification_preferences.status_label') }}</div>
                        <div class="text-sm text-slate-600">
                            {{ __('app.notification_preferences.status_desc') }}
                        </div>
                    </div>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="email_hearing" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_hearing ?? 1) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">{{ __('app.notification_preferences.hearing_label') }}</div>
                        <div class="text-sm text-slate-600">
                            {{ __('app.notification_preferences.hearing_desc') }}
                        </div>
                    </div>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="email_message" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_message ?? 1) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">{{ __('app.notification_preferences.message_label') }}</div>
                        <div class="text-sm text-slate-600">
                            {{ __('app.notification_preferences.message_desc') }}
                        </div>
                    </div>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="email_weekly_digest" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_weekly_digest ?? 0) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">{{ __('app.notification_preferences.weekly_digest_label') }}</div>
                        <div class="text-sm text-slate-600">
                            {{ __('app.notification_preferences.weekly_digest_desc') }}
                        </div>
                    </div>
                </label>
            </div>

            <div class="mt-5">
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    {{ __('app.notification_preferences.save') }}
                </button>
                <a href="{{ route('applicant.notifications.index') }}"
                    class="ml-2 text-sm text-slate-600 hover:text-slate-800">
                    &larr; {{ __('app.notification_preferences.back') }}
                </a>
            </div>
        </form>
    </div>
</x-applicant-layout>
