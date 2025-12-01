{{-- resources/views/apply/cases/show.blade.php --}}
@php
    $applicantUser = auth('applicant')->user();
@endphp
<x-applicant-layout :title="$case->case_number">
    <style>
        /* Lightweight, non-invasive styling so we don't destroy TinyMCE/Word HTML */
        .tiny-content * {
            font-family: inherit !important;
        }

        .tiny-content p {
            margin: 0 0 .65rem;
            text-align: justify;
        }

        .tiny-content ol,
        .tiny-content ul {
            margin: 0 0 .9rem 1.5rem;
            padding-left: 1.25rem;
        }

        .tiny-content ol {
            list-style: decimal;
        }

        .tiny-content ul {
            list-style: disc;
        }

        .tiny-content li {
            margin: .25rem 0;
        }

        .tiny-content ol ol {
            list-style: lower-alpha;
        }

        .tiny-content ul ul {
            list-style: circle;
        }

        .tiny-content table {
            width: 100%;
            border-collapse: collapse;
            margin: .75rem 0;
        }

        .tiny-content th,
        .tiny-content td {
            border: 1px solid #e5e7eb;
            padding: .4rem .5rem;
        }

        .tiny-content img {
            max-width: 100%;
            height: auto;
        }

        .tiny-content blockquote {
            border-left: 3px solid #e5e7eb;
            padding-left: .75rem;
            margin: .75rem 0;
        }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">

        {{-- CASE HEADER / ACTIONS --}}
        <section class="lg:col-span-3 rounded-xl border border-slate-200 bg-white overflow-hidden shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <div class="flex items-start md:items-center gap-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">
                            {{ __('cases.case_number') }}
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-2">
                            <span id="case-number" class="text-xl font-semibold tracking-tight text-slate-900">
                                {{ $case->case_number }}
                            </span>
                            <button
                                x-data
                                x-on:click="
                                    navigator.clipboard.writeText(document.querySelector('#case-number').textContent);
                                    $el.innerText='{{ __('cases.copied') }}';
                                    setTimeout(()=>{$el.innerText='{{ __('cases.copy') }}';},1400);
                                "
                                type="button"
                                class="text-[11px] rounded-full border border-slate-300 px-3 py-1 text-slate-600 hover:bg-slate-100">
                                {{ __('cases.copy') }}
                            </button>
                        </div>
                    </div>

                    @php
                    $status = $case->status;
                    $statusBase = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border capitalize';
                    $statusClass = match(true) {
                    $status === 'pending' => $statusBase.' bg-orange-50 text-orange-700 border-orange-200',
                    $status === 'active' => $statusBase.' bg-blue-50 text-blue-700 border-blue-200',
                    in_array($status, ['closed','dismissed']) => $statusBase.' bg-slate-100 text-slate-700 border-slate-200',
                    default => $statusBase.' bg-slate-50 text-slate-700 border-slate-200',
                    };

                    $reviewStatus = $case->review_status ?? 'accepted';
                    $reviewBase = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border';
                    $reviewClass = match ($reviewStatus) {
                        'awaiting_review' => $reviewBase.' bg-amber-50 text-amber-800 border-amber-200',
                        'returned' => $reviewBase.' bg-yellow-50 text-yellow-800 border-yellow-200',
                        'rejected' => $reviewBase.' bg-red-50 text-red-800 border-red-200',
                        default => $reviewBase.' bg-emerald-50 text-emerald-800 border-emerald-200',
                    };
                    $reviewLabel = match ($reviewStatus) {
                        'awaiting_review' => 'Awaiting approval',
                        'returned' => 'Needs correction',
                        'rejected' => 'Rejected',
                        default => 'Approved',
                    };
                    @endphp

                    <div class="flex flex-col gap-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $statusClass }}">
                                {{ __('cases.status.' . $case->status) }}
                            </span>
                            <span class="{{ $reviewClass }}">
                                {{ $reviewLabel }}
                            </span>
                        </div>
                        @if(!empty($case->review_note))
                        <div class="text-xs text-slate-600 leading-snug max-w-3xl">
                            <span class="font-semibold text-slate-700">Reviewer note:</span>
                            {{ $case->review_note }}
                            @if($reviewStatus === 'returned' && $case->status === 'pending')
                            <a href="{{ route('applicant.cases.edit', $case->id) }}"
                                class="text-blue-700 font-semibold hover:underline ml-1">Edit your case</a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('applicant.cases.edit', $case->id) }}"
                        class="btn btn-muted inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-blue-200 bg-blue-50 text-xs font-medium text-blue-700 hover:bg-blue-100">
                        {{ __('cases.edit_case') }}
                    </a>

                    <a href="{{ route('applicant.cases.receipt.pdf', $case->id) }}"
                        class="btn btn-outline inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-slate-300 bg-white text-xs font-medium text-slate-700 hover:bg-slate-50">
                        {{ __('cases.download_pdf') }}
                    </a>
                    <form method="POST" action="{{ route('applicant.cases.receipt.email', $case->id) }}" class="inline">
                        @csrf
                        <button
                            class="btn btn-outline inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-orange-300 bg-white text-xs font-medium text-orange-700 hover:bg-orange-50">
                            {{ __('cases.email_pdf') }}
                        </button>
                    </form>
                </div>
            </div>
        </section>

        {{-- LEFT: CASE INFO + RESPONDENT + DESCRIPTION + RELIEF + DOCS + WITNESSES --}}
        <section class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-5">
            @php $reviewStatus = $reviewStatus ?? ($case->review_status ?? 'accepted'); @endphp
            @if($reviewStatus === 'returned')
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-900">
                This case needs corrections based on the reviewer note above. Update your filing and resubmit.
            </div>
            @elseif($reviewStatus === 'rejected')
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-900">
                This case has been rejected. Please review the reviewer note above for details.
            </div>
            @elseif($reviewStatus === 'awaiting_review')
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                Your submission is awaiting admin approval. We will notify you once a decision is made.
            </div>
            @endif

            {{-- CASE INFO --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-3">
                    {{ __('cases.case_information') }}
                </h3>
                <dl class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Applicant Full Name') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $case->title ?: ($applicantUser->full_name ?? $applicantUser->name ?? '-') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Applicant Address') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $applicantUser->address ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">{{ __('cases.case_type') }}</dt>
                        <dd class="font-medium text-slate-900">{{ $case->case_type ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">{{ __('cases.filed') }}</dt>
                        <dd class="font-medium text-slate-900">
                            {{ \Illuminate\Support\Carbon::parse($case->filing_date ?? $case->created_at)->format('M d, Y') }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- RESPONDENT (CONDITIONAL) --}}
            @if(!empty($case->respondent_name) || !empty($case->respondent_address))
            <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-3">
                    {{ __('cases.respondent_defendant') }}
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-500">{{ __('cases.name') }}</div>
                        <div class="font-medium text-slate-900">{{ $case->respondent_name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">{{ __('cases.address') }}</div>
                        <div class="font-medium text-slate-900">{{ $case->respondent_address ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- DESCRIPTION (rich HTML, justified, no typography overrides) --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-2">
                    {{ __('cases.case_details') }}
                </h3>
                <div class="tiny-content text-sm text-slate-800">
                    {!! clean($case->description ?? '', 'cases') !!}
                </div>
            </div>

            {{-- RELIEF (CONDITIONAL, rich HTML, justified) --}}
            @if(!empty($case->relief_requested))
            <div>
                <h2 class="text-sm font-semibold text-slate-800 mb-2">
                    {{ __('cases.relief_requested') }}
                </h2>
                <div class="tiny-content text-sm text-slate-800">
                    {!! clean($case->relief_requested ?? '', 'cases') !!}
                </div>
            </div>
            @endif

            {{-- SUBMITTED DOCUMENTS --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">
                        {{ __('cases.submitted_documents') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($docs ?? collect())->count() }}
                    </span>
                </div>

                @if(($docs ?? collect())->isEmpty())
                <div class="rounded-lg border border-dashed border-slate-300 py-10 text-center text-slate-500 text-sm">
                    {{ __('cases.no_submitted_documents') }}
                </div>
                @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($docs as $d)
                    @php
                    $docPath = $d->file_path ?? $d->path ?? null;
                    $docTitle = $d->title ?? ($d->label ?? ($docPath ? basename($docPath) : 'Document'));
                    @endphp
                    <li class="py-2 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="font-medium text-slate-900">
                                {{ $docTitle }}
                            </div>
                            <div class="text-xs text-slate-500 flex flex-wrap gap-1">
                                @if(!empty($d->mime)) {{ $d->mime }} @else {{ __('cases.file') }} @endif
                                @if(isset($d->size)) · {{ number_format(max(0,(int)$d->size)/1024, 1) }} KB @endif
                                @if(!empty($d->created_at)) · {{ \Illuminate\Support\Carbon::parse($d->created_at)->format('M d, Y H:i') }} @endif
                            </div>
                            @if(!empty($d->description))
                            <div class="mt-1 text-xs text-slate-600 tiny-content">
                                {!! clean($d->description, 'cases') !!}
                            </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($docPath)
                            <a href="{{ asset('storage/'.$docPath) }}" target="_blank"
                                class="btn btn-muted inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-blue-200 bg-blue-50 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                {{ __('cases.view') }}
                            </a>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            {{-- WITNESSES --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">
                        {{ __('cases.witnesses_section.title') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($witnesses ?? collect())->count() }}
                    </span>
                </div>

                @if(($witnesses ?? collect())->isEmpty())
                <div class="rounded-lg border border-dashed border-slate-300 py-10 text-center text-slate-500 text-sm">
                    {{ __('cases.no_witnesses_listed') }}
                </div>
                @else
                <div class="overflow-x-auto -mx-2 md:mx-0">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600">
                                <th class="px-3 py-2 text-left font-medium border-b border-slate-200">
                                    {{ __('cases.labels.name') }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium border-b border-slate-200">
                                    {{ __('cases.labels.phone') }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium border-b border-slate-200">
                                    {{ __('cases.labels.email') }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium border-b border-slate-200">
                                    {{ __('cases.labels.address') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($witnesses as $w)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-medium text-slate-900">
                                    {{ $w->full_name }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $w->phone ?: '—' }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    @if(!empty($w->email))
                                    <a href="mailto:{{ $w->email }}" class="text-blue-700 hover:underline">
                                        {{ $w->email }}
                                    </a>
                                    @else
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $w->address ?: '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </section>

        {{-- RIGHT COLUMN: HEARINGS + TIMELINE + MESSAGES --}}
        <div class="space-y-2">

            {{-- HEARINGS --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">
                        {{ __('cases.hearings.title') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($hearings ?? collect())->count() }}
                    </span>
                </div>

                @if(($hearings ?? collect())->isEmpty())
                <div class="rounded-lg border border-dashed border-slate-300 py-8 text-center text-slate-500 text-sm">
                    {{ __('cases.no_hearings_scheduled') }}
                </div>
                @else
                <ul class="space-y-3 text-sm max-h-64 overflow-y-auto pr-1">
                    @foreach(($hearings ?? collect()) as $h)
                    <li class="group flex items-start justify-between gap-4 rounded-lg border border-slate-200 bg-white p-3 hover:border-blue-300 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-slate-50 text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M8 2v3M16 2v3M3.5 9.5h17m-15 3h5m-5 4h9M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" />
                                </svg>
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="font-medium text-slate-900">
                                        {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y · H:i') }}
                                    </div>
                                    @if($h->type)
                                    <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                                        {{ $h->type }}
                                    </span>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-slate-500 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 22s7-5.373 7-12a7 7 0 1 0-14 0c0 6.627 7 12 7 12z" />
                                        <circle cx="12" cy="10" r="2.5" stroke-width="1.5" />
                                    </svg>
                                    <span>{{ $h->location ?: '—' }}</span>
                                </div>
                                @if(!empty($h->notes))
                                <p class="mt-2 text-xs text-slate-600">
                                    {{ $h->notes }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('applicant.cases.hearings.ics', [$case->id, $h->id]) }}"
                                class="btn btn-outline inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-slate-300 bg-white text-xs font-medium text-slate-700 hover:bg-slate-50">
                                {{ __('cases.add_to_calendar') }}
                            </a>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </section>

            {{-- TIMELINE --}}
            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">
                        {{ __('cases.timeline_section.title') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($timeline ?? collect())->count() }}
                    </span>
                </div>
                @if(($timeline ?? collect())->isEmpty())
                <div class="text-slate-500 text-sm border border-dashed border-slate-300 rounded-lg p-6 text-center bg-slate-50">
                    {{ __('cases.no_history_yet') }}
                </div>
                @else
                <ol class="space-y-3 text-sm max-h-64 overflow-y-auto pr-1">
                    @foreach(($timeline ?? collect()) as $t)
                    @php
                        $nextStatus = $t->to_status ?? '';
                        $dotClass = match (true) {
                            $nextStatus === 'active' => 'bg-blue-500',
                            in_array($nextStatus, ['closed', 'dismissed']) => 'bg-slate-500',
                            $nextStatus === 'pending' => 'bg-orange-500',
                            default => 'bg-slate-400',
                        };
                    @endphp
                    <li class="relative pl-4">
                        <div class="absolute left-0 top-1.5 h-2 w-2 rounded-full {{ $dotClass }}"></div>
                        <div class="text-slate-700">
                            {{ $t->from_status ? __('cases.status.' . $t->from_status).' → ' : '' }}
                            <strong>{{ __('cases.status.' . $t->to_status) }}</strong>
                        </div>
                        <div class="text-slate-500 text-xs">
                            {{ \Illuminate\Support\Carbon::parse($t->created_at)->format('M d, Y H:i') }}
                        </div>
                    </li>
                    @endforeach
                </ol>
                @endif
            </aside>

            {{-- MESSAGES --}}
            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">
                        {{ __('cases.messages_section.title') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($msgs ?? collect())->count() }}
                    </span>
                </div>

                <div class="space-y-3 max-h-72 overflow-auto pr-1.5 text-sm">
                    @forelse(($msgs ?? collect()) as $m)
                    <div class="rounded-lg border p-2
                            {{ $m->sender_applicant_id
                                ? 'bg-blue-50 border-blue-200'
                                : 'bg-orange-50 border-orange-200' }}">
                        <div class="text-xs text-slate-500 mb-1">
                            @if($m->sender_applicant_id)
                            {{ __('cases.you') }}
                            @else
                            {{ $m->admin_name ?? __('cases.court_staff') }}
                            @endif
                            · {{ \Illuminate\Support\Carbon::parse($m->created_at)->format('M d, Y H:i') }}
                        </div>
                        <div class="text-sm whitespace-pre-line text-slate-800">
                            {{ $m->body }}
                        </div>
                    </div>
                    @empty
                    <div class="rounded-lg border border-dashed border-slate-300 py-10 text-center text-slate-500 text-sm">
                        {{ __('cases.no_messages_yet') }}
                    </div>
                    @endforelse
                </div>

                <form class="mt-3" method="POST" action="{{ route('applicant.cases.messages.post', $case->id) }}">
                    @csrf
                    <label class="block text-xs text-slate-500 mb-1">
                        {{ __('cases.new_message') }}
                    </label>
                    <textarea
                        name="body"
                        rows="3"
                        placeholder="{{ __('cases.write_message_placeholder') }}"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"></textarea>
                    @error('body')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                    <div class="mt-2">
                        <button
                            class="btn btn-primary w-full inline-flex justify-center items-center gap-1.5 px-3 py-2.5 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                            {{ __('cases.send') }}
                        </button>
                    </div>
                </form>
            </aside>

            {{-- CASE AUDIT --}}
            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">Case Audit Trail</h3>
                    <span class="text-[11px] text-slate-500">{{ ($audits ?? collect())->count() }}</span>
                </div>
                @if(($audits ?? collect())->isEmpty())
                <div class="text-slate-500 text-sm border border-dashed border-slate-300 rounded-lg p-4 text-center bg-slate-50">
                    No audit records yet.
                </div>
                @else
                <div class="max-h-64 overflow-y-auto space-y-3 text-sm">
                    @foreach($audits as $a)
                    @php $meta = $a->meta ? json_decode($a->meta, true) : []; @endphp
                    <div class="p-3 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="text-xs text-slate-500 flex items-center gap-2">
                            <span>{{ \Illuminate\Support\Carbon::parse($a->created_at)->format('M d, Y H:i') }}</span>
                            <span class="px-2 py-0.5 rounded-full border bg-white text-slate-700">{{ ucfirst(str_replace('_',' ', $a->action)) }}</span>
                        </div>
                        <div class="text-[11px] text-slate-600 mt-1">
                            Actor:
                            @if(!empty($a->actor_name))
                                {{ $a->actor_name }} @if($a->actor_id)(#{{ $a->actor_id }})@endif
                            @elseif(!empty($a->actor_id))
                                {{ $a->actor_type ?? 'system' }} (#{{ $a->actor_id }})
                            @else
                                {{ $a->actor_type ?? 'system' }}
                            @endif
                        </div>
                        @if($meta)
                        <pre class="mt-2 bg-white border border-slate-200 rounded px-2 py-1 whitespace-pre-wrap text-[11px]">{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </aside>
        </div>

    </div>

</x-applicant-layout>
