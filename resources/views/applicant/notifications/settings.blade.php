{{-- resources/views/apply/notifications/settings.blade.php --}}
<x-public-layout title="Email preferences">
    <div class="max-w-xl space-y-4">
        <h1 class="text-lg font-semibold text-slate-800">Email preferences</h1>

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
                    <input type="checkbox" name="email_on_status_changes" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_on_status_changes ?? 1) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">Case status changes</div>
                        <div class="text-sm text-slate-600">
                            Receive an email when your case moves between statuses (e.g., Pending → Active).
                        </div>
                    </div>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="email_on_hearings" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_on_hearings ?? 1) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">Hearings</div>
                        <div class="text-sm text-slate-600">
                            Receive an email when a hearing is scheduled or updated for your case.
                        </div>
                    </div>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="email_on_admin_messages" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300"
                        {{ ($prefs->email_on_admin_messages ?? 1) ? 'checked' : '' }}>
                    <div>
                        <div class="font-medium text-slate-800">Court messages</div>
                        <div class="text-sm text-slate-600">
                            Receive an email when court staff sends a message in your case thread.
                        </div>
                    </div>
                </label>
            </div>

            <div class="mt-5">
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Save preferences
                </button>
                <a href="{{ route('applicant.notifications.index') }}"
                    class="ml-2 text-sm text-slate-600 hover:text-slate-800">
                    ← Back to notifications
                </a>
            </div>
        </form>
    </div>
</x-public-layout>