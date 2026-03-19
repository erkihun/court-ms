<x-admin-layout title="{{ __('app.Notifications') }}">
    @section('page_header', __('app.Notifications'))

    <div class="enterprise-page">
        <div class="enterprise-header">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="enterprise-title">{{ __('app.admin_notifications.your_notifications') }}</h2>
                    <p class="enterprise-subtitle">{{ __('app.Notifications') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                    @csrf
                    <button class="btn btn-outline">{{ __('app.Mark all as seen') }}</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="enterprise-panel">
                <div class="enterprise-panel-header">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('app.Applicant messages') }}</h3>
                    <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">{{ $msgs->total() }}</span>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($msgs as $m)
                    @php
                    $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
                    $displayBody = trim((string) $m->body) === $legacyApplicantUpdate
                        ? __('cases.notifications.applicant_updated_submission')
                        : (string) $m->body;
                    @endphp
                    <div class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-slate-50">
                        <a class="text-sm flex-1" href="{{ route('cases.show', $m->case_id) }}">
                            <div class="font-medium text-slate-900">{{ $m->case_number }}</div>
                            <div class="text-xs text-slate-600">
                                {{ \Illuminate\Support\Str::limit($displayBody, 120) }}
                                - {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="message">
                            <input type="hidden" name="sourceId" value="{{ $m->id }}">
                            <button class="btn btn-outline !px-3 !py-1.5 !text-xs">{{ __('app.Seen') }}</button>
                        </form>
                    </div>
                    @empty
                    <div class="enterprise-empty m-5">{{ __('app.admin_notifications.no_new_messages') }}</div>
                    @endforelse
                </div>
                @if($msgs->hasPages()) <div class="px-5 py-3">{{ $msgs->withQueryString()->links() }}</div> @endif
            </div>

            <div class="enterprise-panel">
                <div class="enterprise-panel-header">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('app.New cases') }}</h3>
                    <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">{{ $cases->total() }}</span>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($cases as $c)
                    <div class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-slate-50">
                        <a class="text-sm flex-1" href="{{ route('cases.show', $c->id) }}">
                            <div class="font-medium text-slate-900">{{ $c->case_number }}</div>
                            <div class="text-xs text-slate-600">
                                {{ \Illuminate\Support\Str::limit($c->title, 120) }}
                                - {{ \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() }}
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="case">
                            <input type="hidden" name="sourceId" value="{{ $c->id }}">
                            <button class="btn btn-outline !px-3 !py-1.5 !text-xs">{{ __('app.Seen') }}</button>
                        </form>
                    </div>
                    @empty
                    <div class="enterprise-empty m-5">{{ __('app.admin_notifications.no_new_cases') }}</div>
                    @endforelse
                </div>
                @if($cases->hasPages()) <div class="px-5 py-3">{{ $cases->withQueryString()->links() }}</div> @endif
            </div>

            <div class="enterprise-panel">
                <div class="enterprise-panel-header">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('app.Upcoming hearings') }}</h3>
                    <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">{{ $hearings->total() }}</span>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($hearings as $h)
                    <div class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-slate-50">
                        <a class="text-sm flex-1" href="{{ route('cases.show', $h->case_id) }}">
                            <div class="font-medium text-slate-900">
                                {{ $h->case_number }} - {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                            </div>
                            <div class="text-xs text-slate-600">{{ optional($h)->type ?: __('app.Hearing') }} - {{ optional($h)->location ?: '-' }}</div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="hearing">
                            <input type="hidden" name="sourceId" value="{{ $h->id }}">
                            <button class="btn btn-outline !px-3 !py-1.5 !text-xs">{{ __('app.Seen') }}</button>
                        </form>
                    </div>
                    @empty
                    <div class="enterprise-empty m-5">{{ __('app.admin_notifications.no_upcoming_hearings') }}</div>
                    @endforelse
                </div>
                @if($hearings->hasPages()) <div class="px-5 py-3">{{ $hearings->withQueryString()->links() }}</div> @endif
            </div>

            <div class="enterprise-panel">
                <div class="enterprise-panel-header">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('app.admin_notifications.respondent_views') }}</h3>
                    <span class="enterprise-pill border-slate-200 bg-slate-100 text-slate-700">{{ $respondentViews->total() }}</span>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($respondentViews as $v)
                    <div class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-slate-50">
                        <a class="text-sm flex-1" href="{{ route('cases.show', $v->case_id) }}">
                            <div class="font-medium text-slate-900">{{ $v->case_number }}</div>
                            <div class="text-xs text-slate-600">
                                {{ __('app.admin_notifications.respondent_viewed_case', ['name' => ($v->respondent_name ?: __('app.admin_notifications.respondent_default'))]) }}
                                - {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="respondent_view">
                            <input type="hidden" name="sourceId" value="{{ $v->id }}">
                            <button class="btn btn-outline !px-3 !py-1.5 !text-xs">{{ __('app.Seen') }}</button>
                        </form>
                    </div>
                    @empty
                    <div class="enterprise-empty m-5">{{ __('app.admin_notifications.no_respondent_views') }}</div>
                    @endforelse
                </div>
                @if($respondentViews->hasPages()) <div class="px-5 py-3">{{ $respondentViews->withQueryString()->links() }}</div> @endif
            </div>
        </div>
    </div>
</x-admin-layout>
