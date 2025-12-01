<x-applicant-layout title="Case Details">
    <a href="{{ route('public.cases') }}" class="inline-block mb-4 text-sm text-blue-700 hover:underline">← Back to cases</a>

    <div class="rounded-xl border bg-white p-6 md:p-8 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs text-slate-500">Case #</div>
                <div class="font-mono text-lg">{{ $case->case_number }}</div>
                <h1 class="text-xl font-semibold mt-1">{{ $case->title }}</h1>
            </div>
            <span class="px-2.5 py-1 rounded text-xs capitalize bg-slate-800 text-white">
                {{ $case->status }}
            </span>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-slate-500">Type</div>
                <div class="font-medium">{{ $case->case_type ?? '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500">Court</div>
                <div class="font-medium">{{ $case->court_name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500">Filing Date</div>
                <div class="font-medium">{{ \Illuminate\Support\Carbon::parse($case->filing_date)->format('M d, Y') }}</div>
            </div>
        </div>

        <div>
            <div class="text-slate-500 text-sm mb-1">Case Details</div>
            <div class="whitespace-pre-line text-slate-700">{{ $case->description ?? '—' }}</div>
        </div>
    </div>
</x-applicant-layout>