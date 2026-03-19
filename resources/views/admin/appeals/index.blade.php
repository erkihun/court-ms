<x-admin-layout title="Appeals">
    @section('page_header','Appeals')

    <div class="enterprise-page">
        <div class="enterprise-toolbar">
            <form method="GET" class="enterprise-toolbar-block">
                <label for="appeals-status" class="text-sm font-medium text-slate-700">Status</label>
                <select id="appeals-status" name="status" class="ui-select min-w-[180px]" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach(['draft','submitted','under_review','approved','rejected','closed'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('appeals.create') }}" class="btn btn-primary">New Appeal</a>
        </div>

        <div class="ui-table-wrap">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Appeal #</th>
                            <th>Case #</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Decided By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appeals as $a)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $a->appeal_number }}</td>
                            <td>{{ $a->case_number }}</td>
                            <td>{{ $a->title }}</td>
                            <td>
                                @php
                                $badge = match($a->status) {
                                'draft' => 'border-slate-300 bg-slate-100 text-slate-700',
                                'submitted' => 'border-amber-300 bg-amber-100 text-amber-800',
                                'under_review' => 'border-blue-300 bg-blue-100 text-blue-800',
                                'approved' => 'border-emerald-300 bg-emerald-100 text-emerald-800',
                                'rejected' => 'border-rose-300 bg-rose-100 text-rose-800',
                                'closed' => 'border-cyan-300 bg-cyan-100 text-cyan-800',
                                default => 'border-slate-300 bg-slate-100 text-slate-700',
                                };
                                @endphp
                                <span class="enterprise-pill {{ $badge }}">{{ str_replace('_',' ', ucfirst($a->status)) }}</span>
                            </td>
                            <td class="text-slate-600">{{ $a->decided_by ?? '-' }}</td>
                            <td>
                                <a href="{{ route('appeals.show',$a->id) }}" class="btn btn-outline !px-3 !py-1.5 !text-xs">Open</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6"><div class="enterprise-empty">No appeals found.</div></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $appeals->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
