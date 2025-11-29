<x-respondant-layout title="{{ __('respondent.dashboard_title') }}">
    @php $user = auth('respondent')->user(); @endphp
    <div class="bg-white border border-slate-200 rounded-2xl shadow p-6 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('respondent.welcome_back') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $user?->full_name ?? __('respondent.respondent_label') }}
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    {{ __('respondent.you_are_signed_in') }}
                </p>
            </div>
            <div class="hidden sm:block">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-sm font-medium">
                    {{ __('respondent.respondent_label') }}
                </span>
            </div>
        </div>

        <div class="mt-8 grid md:grid-cols-3 gap-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-500">{{ __('respondent.email') }}</p>
                <p class="text-base font-semibold text-slate-900">{{ $user?->email }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-500">{{ __('respondent.phone') }}</p>
                <p class="text-base font-semibold text-slate-900">{{ $user?->phone }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-500">{{ __('respondent.organization') }}</p>
                <p class="text-base font-semibold text-slate-900">{{ $user?->organization_name }}</p>
            </div>
        </div>
    </div>
</x-respondant-layout>
