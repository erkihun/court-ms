<x-applicant-layout title="Cases">
    <h1 class="text-2xl font-semibold mb-4">Public Cases</h1>

    <form method="GET" class="mb-4 flex gap-2">
        <input name="q" value="{{ $q ?? '' }}" placeholder="Search…"
            class="w-full md:w-96 px-3 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600">
        <button class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Filter</button>
        @if(($q ?? '') !== '')
        <a href="{{ route('public.cases') }}" class="px-3 py-2 rounded-md bg-slate-200 hover:bg-slate-300">Reset</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-3 text-left">Case #</th>
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Court</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Filed</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($cases as $c)
                <tr class="hover:bg-slate-50">
                    <td class="p-3 font-mono">{{ $c->case_number }}</td>
                    <td class="p-3">{{ $c->title }}</td>
                    <td class="p-3">{{ $c->case_type }}</td>
                    <td class="p-3">{{ $c->court_name }}</td>
                    <td class="p-3 capitalize">{{ $c->status }}</td>
                    <td class="p-3">{{ \Illuminate\Support\Carbon::parse($c->filing_date)->format('M d, Y') }}</td>
                    <td class="p-3">
                        <a href="{{ route('public.cases.show', $c->case_number) }}"
                            class="px-2 py-1 rounded bg-slate-800 text-white hover:bg-slate-700 text-xs">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-6 text-center text-slate-500">No cases found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $cases->links() }}</div>
</x-applicant-layout>