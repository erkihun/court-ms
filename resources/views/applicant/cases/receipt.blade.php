@props(['title' => 'Filing Receipt'])
@php
    use Illuminate\Support\Carbon;

    $filingDate = Carbon::parse($case->filing_date ?? $case->created_at);
    $statusLabel = __('cases.status.' . $case->status);
    $statusLabel = $statusLabel === 'cases.status.' . $case->status ? ucfirst($case->status) : $statusLabel;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ $title }} | {{ config('app.name','Court-MS') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border-color: #e2e8f0 !important;
            }

            body {
                background: #fff !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-slate-800">
    <div class="max-w-3xl mx-auto my-8 px-4">
        <div class="no-print mb-4 flex items-center justify-between">
            <a href="{{ route('applicant.cases.show', $case->id) }}"
                class="rounded-md border border-slate-200 bg-white px-3 py-2  hover:bg-slate-50">
                Back to case
            </a>
            <button onclick="window.print()"
                class="rounded-md bg-slate-800 px-3 py-2  font-medium text-white hover:bg-slate-700">
                Print receipt
            </button>
        </div>

        <div class="card rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">Filing Receipt</h1>
                    <div class="mt-1  text-slate-500">{{ config('app.name','Court-MS') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-500">Case #</div>
                    <div class="text-lg font-semibold tracking-tight">{{ $case->case_number }}</div>
                    @if(!empty($case->code))
                    <div class="mt-1 text-xs text-slate-500">Access code</div>
                    <div class="text-sm font-semibold tracking-tight">{{ $case->code }}</div>
                    @endif
                    <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs capitalize
                        @if($case->status==='pending') bg-amber-100 text-amber-800 border border-amber-200
                        @elseif($case->status==='active') bg-blue-100 text-blue-800 border border-blue-200
                        @elseif(in_array($case->status,['closed','dismissed'])) bg-emerald-100 text-emerald-800 border border-emerald-200
                        @else bg-slate-100 text-slate-800 border border-slate-200 @endif">
                        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                        {{ $statusLabel }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-slate-600">
                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                    <div class="uppercase tracking-wide font-semibold text-[11px] text-slate-500">Filed on</div>
                    <div class=" font-semibold text-slate-800">{{ \App\Support\EthiopianDate::format($filingDate) }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                    <div class="uppercase tracking-wide font-semibold text-[11px] text-slate-500">Documents</div>
                    <div class=" font-semibold text-slate-800">{{ $evidenceDocs->count() }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                    <div class="uppercase tracking-wide font-semibold text-[11px] text-slate-500">Witnesses</div>
                    <div class=" font-semibold text-slate-800">{{ $witnesses->count() }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2">
                    <div class="uppercase tracking-wide font-semibold text-[11px] text-slate-500">Hearings</div>
                    <div class=" font-semibold text-slate-800">{{ $hearings->count() }}</div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 ">
                <div>
                    <div class="text-slate-500">Applicant</div>
                    <div class="font-medium">
                        {{ trim(($case->first_name.' '.($case->middle_name ?? '').' '.$case->last_name)) }}
                    </div>
                    <div class="text-slate-500">
                        {{ $case->email }} @if($case->phone) | {{ $case->phone }} @endif
                    </div>
                </div>
                <div>
                    <div class="text-slate-500">Filed</div>
                    <div class="font-medium">
                        {{ \App\Support\EthiopianDate::format($filingDate) }}
                    </div>
                </div>
                <div>
                    <div class="text-slate-500">Case title</div>
                    <div class="font-medium">{{ $case->title }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Court / Type</div>
                    <div class="font-medium">
                        {{ $case->court_name ?? 'Not provided' }} @if($case->case_type) | {{ $case->case_type }} @endif
                    </div>
                </div>
                @if($case->respondent_name || $case->respondent_address)
                <div class="md:col-span-2">
                    <div class="text-slate-500">Respondent</div>
                    <div class="font-medium">
                        {{ $case->respondent_name ?: 'Not provided' }}
                        @if($case->respondent_address)
                        <span class="text-slate-500"> | {{ $case->respondent_address }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <hr class="my-6 border-slate-200">

            <div>
                <div class="text-slate-500 ">Case details</div>
                <p class="mt-1  leading-relaxed whitespace-pre-line">{{ $case->description }}</p>
                @if($case->relief_requested)
                <div class="mt-3">
                    <div class="text-slate-500 ">Relief requested</div>
                    <p class=" whitespace-pre-line">{{ $case->relief_requested }}</p>
                </div>
                @endif
            </div>

            <div class="mt-6 grid md:grid-cols-2 gap-6">
                {{-- Witnesses --}}
                <div>
                    <h3 class=" font-semibold text-slate-700 mb-2">Human Evidence / Testimony</h3>
                    @if($witnesses->isEmpty())
                    <div class=" text-slate-500">None provided.</div>
                    @else
                    <ul class="space-y-2 ">
                        @foreach($witnesses as $w)
                        <li class="rounded-md border border-slate-200 p-2">
                            <div class="font-medium text-slate-900">{{ $w->full_name }}</div>
                            <div class="text-xs text-slate-500">
                                {{ $w->phone ?: 'No phone' }}
                                @if($w->email)
                                | {{ $w->email }}
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>

                {{-- Document evidences --}}
                <div>
                    <h3 class=" font-semibold text-slate-700 mb-2">List of Evidence (Documents)</h3>
                    @if($evidenceDocs->isEmpty())
                    <div class=" text-slate-500">None uploaded.</div>
                    @else
                    <ul class="space-y-2 ">
                        @foreach($evidenceDocs as $d)
                        @php
                            $docTitle = $d->title ?? basename($d->file_path);
                            $docUrl = $d->file_path ? route('applicant.cases.evidences.download', ['id' => $case->id, 'evidenceId' => $d->id]) : null;
                        @endphp
                        <li class="flex items-center justify-between rounded-md border border-slate-200 p-2">
                            <span class="font-medium text-slate-900">{{ $docTitle }}</span>
                            @if($docUrl)
                            <a href="{{ $docUrl }}" target="_blank" rel="noopener"
                                class="no-print text-xs text-blue-700 hover:underline">
                                View
                            </a>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

            {{-- Optional: recent uploads --}}
            @if(!$files->isEmpty())
            <div class="mt-6">
                <h3 class=" font-semibold text-slate-700 mb-2">Additional files</h3>
                <ul class="divide-y border rounded-md">
                    @foreach($files as $f)
                    <li class="flex items-center justify-between p-2 ">
                        <div>
                            <div class="font-medium">{{ $f->label ?? basename($f->path) }}</div>
                            <div class="text-xs text-slate-500">
                                {{ \App\Support\EthiopianDate::format($f->created_at, withTime: true) }}
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">{{ strtoupper(pathinfo($f->path, PATHINFO_EXTENSION)) }}</div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Upcoming hearing (if any) --}}
            @if(!$hearings->isEmpty())
            <div class="mt-6">
                <h3 class=" font-semibold text-slate-700 mb-2">Hearings</h3>
                <ul class="space-y-2 ">
                    @foreach($hearings as $h)
                    <li class="flex items-center justify-between rounded-md border border-slate-200 p-2">
                        <div>
                            <div class="font-medium">
                                {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                                @if($h->type) | {{ $h->type }} @endif
                            </div>
                            <div class="text-xs text-slate-500">{{ $h->location ?: 'Not provided' }}</div>
                        </div>
                        <a href="{{ route('applicant.cases.hearings.ics', [$case->id, $h->id]) }}"
                            class="no-print inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-2.5 py-1.5 text-xs hover:bg-slate-50">
                            Add to calendar
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="mt-8 text-xs text-slate-500">
                Generated on {{ \App\Support\EthiopianDate::format(now(), withTime: true) }}.
            </div>
        </div>
    </div>
</body>

</html>
