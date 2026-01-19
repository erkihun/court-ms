@php
    use Mews\Purifier\Facades\Purifier;

    $sections = [
        'case-summary' => __('recordes.labels.case_summary'),
        'case-details' => __('recordes.labels.case_details'),
        'respondent-responses' => __('recordes.labels.respondent_responses'),
        'letters' => __('recordes.labels.letters_section'),
        'hearings' => __('recordes.labels.hearings'),
        'bench-notes' => __('recordes.labels.bench_notes'),
        'final-decision' => __('recordes.labels.final_judgment'),
        'other-documents' => __('recordes.labels.other_documents'),
    ];

    $submittedDocuments = collect($case->submitted_documents ?? []);
    $otherDocuments = collect($files ?? []);

    if ($submittedDocuments->isEmpty() && $otherDocuments->isNotEmpty()) {
        $submittedDocuments = $otherDocuments;
        $otherDocuments = collect();
    }

    $sanitize = fn(?string $html) => Purifier::clean((string) ($html ?? ''), 'default');
@endphp

<x-admin-layout :title="__('recordes.titles.record')">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <style>
            .record-content p {
                text-align: justify;
            }
        </style>
        <aside class="bg-white shadow rounded-xl border border-slate-200 p-5 space-y-4" data-record-nav>
            <div>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('recordes.titles.record') }}</h2>
                <p class="text-xs text-slate-500">
                    {{ __('recordes.descriptions.index_intro') }}
                </p>
            </div>
            <nav class="space-y-2" id="record-nav">
                @foreach($sections as $id => $label)
                    <a href="#section-{{ $id }}"
                       class="w-full inline-flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span>{{ $label }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.22 14.78a.75.75 0 001.06 0L13 10.06l4.72 4.72a.75.75 0 101.06-1.06l-5.25-5.25a.75.75 0 00-1.06 0l-5.25 5.25a.75.75 0 000 1.06z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @endforeach
            </nav>
            <div class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-800">
                <p class="font-semibold">{{ $case->case_number ?? 'N/A' }}</p>
                <p>{{ \Illuminate\Support\Str::limit($case->title ?? __('recordes.messages.untitled_case'), 60) }}</p>
                <p class="mt-1">
                    {{ __('recordes.labels.status_label') }}: <span class="font-semibold">{{ ucfirst($case->status ?? 'n/a') }}</span>
                </p>
            </div>
        </aside>

        <div class="lg:col-span-3 space-y-8 record-content">
            {{-- Case Summary --}}
            <section id="section-case-summary" class="record-section bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('recordes.titles.record') }}</p>
                        <h1 class="text-2xl font-semibold text-slate-900">
                            {{ $case->case_number ?? __('recordes.messages.not_available') }} &mdash; {{ $case->title ?? __('recordes.messages.untitled_case') }}
                        </h1>
                        <p class="text-sm text-slate-600 mt-1">
                            {{ __('recordes.labels.filed') }}:
                            {{ optional($case->filing_date)->toFormattedDateString() ?? '—' }}
                            @if(!empty($case->first_hearing_date))
                                &middot; {{ __('recordes.labels.hearing_at') }}:
                                {{ \Illuminate\Support\Carbon::parse($case->first_hearing_date)->toFormattedDateString() }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        {{ __('recordes.labels.generated') }} {{ now()->toDayDateTimeString() }}<br>
                        {{ __('recordes.labels.applicant') }}
                        {{ $case->applicant->name ?? ($case->applicant_name ?? 'N/A') }}
                    </div>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('recordes.labels.applicant_heading') }}</h3>
                        <p><span class="font-medium">{{ __('recordes.labels.name') }}:</span> {{ $case->applicant->name ?? ($case->applicant_name ?? 'N/A') }}</p>
                        <p><span class="font-medium">{{ __('recordes.labels.email') }}:</span> {{ $case->applicant->email ?? '—' }}</p>
                        <p><span class="font-medium">{{ __('recordes.labels.phone') }}:</span> {{ $case->applicant->phone ?? '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('recordes.labels.respondent_heading') }}</h3>
                        <p><span class="font-medium">{{ __('recordes.labels.name') }}:</span> {{ $case->respondent_name ?? 'N/A' }}</p>
                        <p><span class="font-medium">{{ __('recordes.labels.address') }}:</span> {{ $case->respondent_address ?? '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- Bench Notes --}}
            <section id="section-bench-notes" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.bench_notes') }}</h2>
                    <span class="text-xs text-slate-500">{{ ($benchNotes ?? collect())->count() }} {{ __('recordes.labels.total') }}</span>
                </header>
                @forelse($benchNotes ?? [] as $note)
                    <div class="rounded-lg border border-slate-200 p-4 text-sm text-slate-700 space-y-2">
                        <div class="flex items-start justify-between">
                            <p class="font-semibold">{{ $note->title ?? __('recordes.labels.note') }}</p>
                            <span class="text-xs text-slate-500">{{ optional($note->created_at)->toDayDateTimeString() }}</span>
                        </div>
                        <div class="prose max-w-none text-sm text-slate-800">
                            {!! $sanitize($note->note ?? $note->body ?? '') !!}
                        </div>
                        @if(!empty($note->author))
                            <p class="text-xs text-slate-500">{{ __('recordes.labels.assigned_to') }} {{ $note->author }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('recordes.messages.no_bench_notes') }}</p>
                @endforelse
            </section>

            {{-- Case Details --}}
            <section id="section-case-details" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-6">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.case_details') }}</h2>
                    <span class="text-xs text-slate-500">{{ __('recordes.labels.case_submission') }}</span>
                </header>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-800">{{ __('recordes.labels.case_details_overview') }}</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-700">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('recordes.labels.case_type') }}</dt>
                            <dd class="font-medium text-slate-900">{{ $case->case_type ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('recordes.labels.status_label') }}</dt>
                            <dd class="font-medium text-slate-900">{{ ucfirst($case->status ?? 'N/A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('recordes.labels.filed') }}</dt>
                            <dd class="font-medium text-slate-900">{{ optional($case->filing_date)->toFormattedDateString() ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('recordes.labels.hearing_at') }}</dt>
                            <dd class="font-medium text-slate-900">{{ optional($case->first_hearing_date)->toFormattedDateString() ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-800">{{ __('recordes.labels.case_details') }}</h3>
                    <div class="prose max-w-none text-sm text-slate-800">
                        {!! $case->description
                            ? $sanitize($case->description)
                            : '<p>' . __('recordes.messages.no_details_provided') . '</p>' !!}
                    </div>
                </div>

                @if(!empty($case->relief_requested))
                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('recordes.labels.relief_requested') }}</h3>
                        <div class="prose max-w-none text-sm text-slate-800">
                            {!! $sanitize($case->relief_requested) !!}
                        </div>
                    </div>
                @endif

                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('recordes.labels.submitted_documents') }}</h3>
                        @forelse($submittedDocuments as $document)
                            <div class="rounded-lg border border-slate-200 p-3 text-sm text-slate-700 flex justify-between">
                                <div>
                                    <p class="font-semibold">{{ $document->label ?? $document->title ?? __('recordes.labels.document') }}</p>
                                    <p class="text-xs text-slate-500">{{ optional($document->created_at)->toDayDateTimeString() }}</p>
                                </div>
                                @if(!empty($document->path ?? $document->file_path))
                                    <span class="text-xs text-slate-500">{{ $document->path ?? $document->file_path }}</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('recordes.messages.no_files') }}</p>
                        @endforelse
                    </div>

                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-slate-800">{{ __('recordes.labels.witnesses') }}</h3>
                    @forelse(($witnesses ?? collect()) as $wit)
                        <div class="rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                            <p class="font-semibold">{{ $wit->full_name ?? __('recordes.labels.witness') }}</p>
                            <p class="text-xs text-slate-500">
                                {{ __('recordes.labels.phone') }} {{ $wit->phone ?? 'N/A' }}
                                &middot; {{ __('recordes.labels.email') }} {{ $wit->email ?? 'N/A' }}
                                &middot; {{ __('recordes.labels.address') }} {{ $wit->address ?? 'N/A' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('recordes.messages.no_witnesses') }}</p>
                    @endforelse
                </div>
            </section>

            {{-- Respondent Responses --}}
            <section id="section-respondent-responses" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.respondent_responses') }}</h2>
                    <span class="text-xs text-slate-500">{{ __('recordes.labels.respondent_responses') }}</span>
                </header>
                @forelse($respondentResponses ?? [] as $resp)
                    <div class="border border-slate-200 rounded-lg p-4 text-sm text-slate-700 space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold">{{ $resp->title ?? __('recordes.labels.response') }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ __('recordes.labels.case_number') }} {{ $resp->case_number ?? $case->case_number ?? __('recordes.messages.not_available') }}
                                </p>
                            </div>
                            <span class="text-xs text-slate-500">{{ optional($resp->created_at)->toDayDateTimeString() }}</span>
                        </div>
                        @if(!empty($resp->description))
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($resp->description), 260) }}</p>
                        @endif
                        @if(!empty($resp->pdf_path))
                            <p class="text-xs text-slate-500">
                                {{ __('recordes.labels.attachment') }} {{ $resp->pdf_path }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('recordes.messages.no_responses') }}</p>
                @endforelse
            </section>

            {{-- Letters --}}
            <section id="section-letters" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.letters_section') }}</h2>
                    <span class="text-xs text-slate-500">{{ ($letters ?? collect())->count() }} {{ __('recordes.labels.total') }}</span>
                </header>
                @forelse($letters ?? [] as $letter)
                    <div class="border border-slate-200 rounded-lg p-4 text-sm text-slate-700">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $letter->subject ?? __('recordes.labels.letter') }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ __('recordes.labels.reference_number') }} {{ $letter->reference_number ?? 'N/A' }} &middot; {{ ucfirst($letter->approval_status ?? 'draft') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ __('recordes.labels.author') }} {{ $letter->author_name ?? __('recordes.messages.not_available') }}
                                </p>
                            </div>
                            <span class="text-xs text-slate-500">{{ optional($letter->created_at)->toDayDateTimeString() }}</span>
                        </div>
                        @if(!empty($letter->body))
                            <div class="prose max-w-none mt-2">
                                {!! $sanitize($letter->body) !!}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('recordes.messages.no_letters') }}</p>
                @endforelse
            </section>

            {{-- Hearings --}}
            <section id="section-hearings" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.hearings') }}</h2>
                    <span class="text-xs text-slate-500">{{ ($hearings ?? collect())->count() }} {{ __('recordes.labels.total') }}</span>
                </header>
                @forelse($hearings ?? [] as $hearing)
                    @php
                        $hearingMoment = !empty($hearing->hearing_at) ? \Illuminate\Support\Carbon::parse($hearing->hearing_at) : null;
                        $hearingFormatted = $hearingMoment ? $hearingMoment->format('F j, Y g:i A') : __('recordes.labels.hearing_unknown');
                    @endphp
                    <div class="border border-slate-200 rounded-lg p-4 text-sm text-slate-700 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $hearingFormatted }}</p>
                                <p class="text-xs text-slate-500">{{ __('recordes.labels.location') }} {{ $hearing->location ?? 'N/A' }}</p>
                            </div>
                            <span class="text-xs text-slate-500">{{ $hearing->type ?? '' }}</span>
                        </div>
                        @if(!empty($hearing->judge_notes))
                            <p><span class="font-semibold">{{ __('recordes.labels.judge_notes') }}:</span> {{ strip_tags($hearing->judge_notes) }}</p>
                        @endif
                        @if(!empty($hearing->notes))
                            <p class="text-slate-600"><span class="font-semibold">{{ __('recordes.labels.hearing_notes') }}:</span> {{ strip_tags($hearing->notes) }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('recordes.messages.no_hearings') }}</p>
                @endforelse
            </section>

            {{-- Final Judgment --}}
            <section id="section-final-decision" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.final_judgment') }}</h2>
                    <span class="text-xs text-slate-500">{{ $decision ? __('recordes.labels.created') . ' ' . optional($decision->created_at)->toDayDateTimeString() : __('recordes.messages.no_decision') }}</span>
                </header>
                @if(!empty($decision))
                    <div class="border border-slate-200 rounded-lg p-4 text-sm text-slate-700 space-y-2">
                        <p class="font-semibold">{{ $decision->title ?? __('recordes.labels.decision') }}</p>
                        @if(!empty($decision->decision_content ?? $decision->body))
                            <div class="prose max-w-none">{!! $sanitize($decision->decision_content ?? $decision->body ?? '') !!}</div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-slate-500">{{ __('recordes.messages.no_decision') }}</p>
                @endif
            </section>

            {{-- Other Documents --}}
            <section id="section-other-documents" class="record-section hidden bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
                <header class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('recordes.labels.other_documents') }}</h2>
                    <span class="text-xs text-slate-500">
                        {{ ($files ?? collect())->count() + ($evidences ?? collect())->count() }}
                        {{ __('recordes.labels.items') }}
                    </span>
                </header>
                <div class="space-y-3">
                    @forelse($otherDocuments as $file)
                        <div class="rounded-lg border border-slate-200 p-4 text-sm text-slate-700 flex justify-between">
                            <div>
                                <p class="font-semibold">{{ $file->label ?? __('recordes.labels.document') }}</p>
                                <p class="text-xs text-slate-500">{{ optional($file->created_at)->toDayDateTimeString() }}</p>
                            </div>
                            @if(!empty($file->path))
                                <span class="text-xs text-slate-500">{{ $file->path }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('recordes.messages.no_uploaded_files') }}</p>
                    @endforelse
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('recordes.labels.applicant_evidence') }}</h3>
                    @forelse($evidences ?? [] as $ev)
                        <div class="rounded-lg border border-slate-200 p-4 text-sm text-slate-700">
                            <p class="font-semibold">{{ $ev->title ?? __('recordes.labels.document') }}</p>
                            <p class="text-xs text-slate-500">{{ optional($ev->created_at)->toDayDateTimeString() }}</p>
                            @if(!empty($ev->description))
                                <p class="mt-1">{{ $ev->description }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('recordes.messages.no_evidence') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const recordNav = document.querySelector('[data-record-nav] nav');
            const navLinks = recordNav ? Array.from(recordNav.querySelectorAll('a')) : [];
            const panels = Array.from(document.querySelectorAll('.record-section'));

            if (!recordNav || navLinks.length === 0) return;

            const activateNav = (targetId) => {
                navLinks.forEach(link => link.classList.remove('border-blue-500', 'text-blue-700', 'bg-blue-50'));
                const activeLink = navLinks.find(link => link.getAttribute('href') === `#${targetId}`);
                if (activeLink) {
                    activeLink.classList.add('border-blue-500', 'text-blue-700', 'bg-blue-50');
                }
            };

            const showSection = (targetId) => {
                panels.forEach(panel => {
                    if (panel.id === targetId) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });
                activateNav(targetId);
            };

            navLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    const targetId = (link.getAttribute('href') || '').replace('#', '');
                    if (!targetId) return;
                    event.preventDefault();
                    showSection(targetId);
                });
            });

            showSection('section-case-summary');
        });
    </script>
</x-admin-layout>
