<x-admin-layout title="Appeals">
    @section('page_header','Appeals')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <form method="GET" class="inline-flex items-center gap-2">
            <label for="appeals-status" class="text-sm text-gray-700">Status</label>
            <select id="appeals-status" name="status"
                class="bg-white text-gray-900 border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-600"
                onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['draft','submitted','under_review','approved','rejected','closed'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                @endforeach
            </select>
        </form>


        <a href="{{ route('appeals.create') }}"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-500 border border-blue-600/70">
            New Appeal
        </a>

    </div>

    <div class="rounded-md border border-gray-200 bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="border-b border-gray-200 text-gray-700">
                    <th class="p-2 text-left font-medium">Appeal #</th>
                    <th class="p-2 text-left font-medium">Case #</th>
                    <th class="p-2 text-left font-medium">Title</th>
                    <th class="p-2 text-left font-medium">Status</th>
                    <th class="p-2 text-left font-medium">Decided By</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($appeals as $a)
                <tr class="text-gray-900 hover:bg-gray-50 transition">
                    <td class="p-2">{{ $a->appeal_number }}</td>
                    <td class="p-2">{{ $a->case_number }}</td>
                    <td class="p-2">{{ $a->title }}</td>
                    <td class="p-2">
                        @php
                        $badge = match($a->status) {
                        'draft' => 'bg-gray-100 text-gray-800 border-gray-300',
                        'submitted' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'under_review' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                        'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                        'closed' => 'bg-teal-100 text-teal-800 border-teal-300',
                        default => 'bg-gray-100 text-gray-800 border-gray-300',
                        };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium border {{ $badge }}">
                            {{ str_replace('_',' ', ucfirst($a->status)) }}
                        </span>
                    </td>
                    <td class="p-2 text-gray-900">{{ $a->decided_by ?? '—' }}</td>
                    <td class="p-2 text-right">
                        <a href="{{ route('appeals.show',$a->id) }}"
                            class="text-blue-600 hover:text-blue-800 underline underline-offset-2">
                            Open
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">
                        No appeals found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-3 border-t border-gray-200">
            {{-- Laravel default pagination markup is light; wrapping helps it sit on dark bg --}}
            <div class="max-w-none">
                {{ $appeals->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>