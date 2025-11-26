@props(['title' => 'Filing Receipt'])

<!DOCTYPE html>
<html lang="en">

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
                class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm hover:bg-slate-50">
                ← Back to case
            </a>
            <button onclick="window.print()"
                class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Print receipt
            </button>
        </div>

        <div class="card rounded-xl border border-slate-200 bg-white p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Filing Receipt</h1>
                    <div class="mt-1 text-sm text-slate-500">{{ config('app.name','Court-MS') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-500">Case #</div>
                    <div class="text-lg font-semibold tracking-tight">{{ $case->case_number }}</div>
                    <div class="mt-1">
                        <span class="px-2 py-0.5 rounded text-xs capitalize
                            @if($case->status==='pending') bg-amber-100 text-amber-800 border border-amber-200
                            @elseif($case->status==='active') bg-blue-100 text-blue-800 border border-blue-200
                            @elseif(in_array($case->status,['closed','dismissed'])) bg-emerald-100 text-emerald-800 border border-emerald-200
                            @else bg-slate-100 text-slate-800 border border-slate-200 @endif">
                            {{ $case->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-slate-500">Applicant</div>
                    <div class="font-medium">
                        {{ trim(($case->first_name.' '.($case->middle_name ?? '').' '.$case->last_name)) }}
                    </div>
                    <div class="text-slate-500">
                        {{ $case->email }} @if($case->phone) · {{ $case->phone }} @endif
                    </div>
                </div>
                <div>
                    <div class="text-slate-500">Filed</div>
                    <div class="font-medium">
                        {{ \Illuminate\Support\Carbon::parse($case->filing_date ?? $case->created_at)->format('M d, Y') }}
                    </div>
                </div>
                <div>
                    <div class="text-slate-500">Case title</div>
                    <div class="font-medium">{{ $case->title }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Court / Type</div>
                    <div class="font-medium">
                        {{ $case->court_name ?? '—' }} @if($case->case_type) · {{ $case->case_type }} @endif
                    </div>
                </div>
                @if($case->respondent_name || $case->respondent_address)
                <div class="md:col-span-2">
                    <div class="text-slate-500">Respondent</div>
                    <div class="font-medium">
                        {{ $case->respondent_name ?: '—' }}
                        @if($case->respondent_address)
                        <span class="text-slate-500"> · {{ $case->respondent_address }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <hr class="my-6 border-slate-200">

            <div>
                <div class="text-slate-500 text-sm">Case details</div>
                <p class="mt-1 text-sm leading-relaxed whitespace-pre-line">{{ $case->description }}</p>
                @if($case->relief_requested)
                <div class="mt-3">
                    <div class="text-slate-500 text-sm">Relief requested</div>
                    <p class="text-sm whitespace-pre-line">{{ $case->relief_requested }}</p>
                </div>
                @endif
            </div>

            <div class="mt-6 grid md:grid-cols-2 gap-6">
                {{-- Witnesses --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">Human Evidence / Testimony</h3>
                    @if($witnesses->isEmpty())
                    <div class="text-sm text-slate-500">None provided.</div>
                    @else
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($witnesses as $w)
                        <li>{{ $w->full_name }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>

                {{-- Document evidences --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">List of Evidence (Documents)</h3>
                    @if($evidenceDocs->isEmpty())
                    <div class="text-sm text-slate-500">None uploaded.</div>
                    @else
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($evidenceDocs as $d)
                        <li>{{ $d->title ?? basename($d->file_path) }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

            {{-- Optional: recent uploads --}}
            @if(!$files->isEmpty())
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Additional files</h3>
                <ul class="divide-y border rounded-md">
                    @foreach($files as $f)
                    <li class="flex items-center justify-between p-2 text-sm">
                        <div>
                            <div class="font-medium">{{ $f->label ?? basename($f->path) }}</div>
                            <div class="text-xs text-slate-500">
                                {{ \Illuminate\Support\Carbon::parse($f->created_at)->format('M d, Y H:i') }}
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
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Hearings</h3>
                <ul class="space-y-2 text-sm">
                    @foreach($hearings as $h)
                    <li class="flex items-center justify-between rounded-md border border-slate-200 p-2">
                        <div>
                            <div class="font-medium">
                                {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                                @if($h->type) · {{ $h->type }} @endif
                            </div>
                            <div class="text-xs text-slate-500">{{ $h->location ?: '—' }}</div>
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
                Generated on {{ now()->format('M d, Y H:i') }}.
            </div>
        </div>
    </div>
</body>

</html>