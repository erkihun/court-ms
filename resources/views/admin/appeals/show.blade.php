<x-admin-layout :title="$appeal->appeal_number">
    @section('page_header', $appeal->appeal_number)

    <div class="enterprise-page">
        <div class="grid gap-6 md:grid-cols-3">
            <section class="enterprise-panel md:col-span-2">
                <div class="enterprise-panel-body space-y-4">
                    <div class="text-sm text-slate-600">
                        Case: <span class="font-medium text-slate-900">{{ $appeal->case_number }}</span>
                        <span class="text-slate-400">-</span>
                        <span class="text-slate-900">{{ $appeal->case_title }}</span>
                    </div>

                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">{{ $appeal->title }}</h2>

                    <div>
                        <span class="text-xs uppercase tracking-[0.18em] text-slate-500">Status</span>
                        <span class="enterprise-pill ml-2
                            @class([
                                'border-blue-300 bg-blue-100 text-blue-800' => $appeal->status==='submitted' || $appeal->status==='under_review',
                                'border-amber-300 bg-amber-100 text-amber-800' => $appeal->status==='draft',
                                'border-emerald-300 bg-emerald-100 text-emerald-800' => $appeal->status==='approved',
                                'border-rose-300 bg-rose-100 text-rose-800' => $appeal->status==='rejected',
                                'border-slate-300 bg-slate-100 text-slate-800' => $appeal->status==='closed',
                            ])">
                            {{ $appeal->status }}
                        </span>
                    </div>

                    @if(!empty($appeal->grounds))
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-700">
                        {!! nl2br(e($appeal->grounds)) !!}
                    </div>
                    @endif

                    @perm('appeals.edit')
                    @if($appeal->status==='draft')
                    <div class="enterprise-actions">
                        <form method="POST" action="{{ route('appeals.submit',$appeal->id) }}">
                            @csrf
                            <button class="btn btn-primary">Submit</button>
                        </form>
                        <a href="{{ route('appeals.edit',$appeal->id) }}" class="btn btn-outline">Edit</a>
                    </div>
                    @endif
                    @endperm
                </div>
            </section>

            <aside class="enterprise-panel">
                <div class="enterprise-panel-body space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-2">Documents</h3>
                        <form class="space-y-2" method="POST" action="{{ route('appeals.docs.upload',$appeal->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input name="label" placeholder="Label" class="ui-input">
                            <input type="file" name="file" required class="enterprise-file-input">
                            <button class="btn btn-primary w-full">Upload</button>
                        </form>
                    </div>

                    <ul class="divide-y divide-slate-200">
                        @forelse($docs as $d)
                        <li class="py-3 flex items-center justify-between gap-3">
                            <div class="text-sm text-slate-900">
                                {{ $d->label ?? basename($d->path) }}
                                <div class="text-xs text-slate-500">{{ \App\Support\EthiopianDate::format($d->created_at, withTime: true) }}</div>
                            </div>
                            <div class="enterprise-actions">
                                <a href="{{ route('appeals.docs.download', [$appeal->id, $d->id]) }}" class="text-blue-600 text-sm hover:underline">View</a>
                                @perm('appeals.edit')
                                <form method="POST" action="{{ route('appeals.docs.delete', [$appeal->id, $d->id]) }}" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 text-sm hover:underline">Delete</button>
                                </form>
                                @endperm
                            </div>
                        </li>
                        @empty
                        <li class="py-8"><div class="enterprise-empty">No documents uploaded.</div></li>
                        @endforelse
                    </ul>

                    @perm('appeals.decide')
                    @if(in_array($appeal->status,['submitted','under_review']))
                    <div class="pt-3 border-t border-slate-200">
                        <h3 class="text-sm font-semibold text-slate-900 mb-2">Record Decision</h3>
                        <form method="POST" action="{{ route('appeals.decide',$appeal->id) }}" class="space-y-2">
                            @csrf
                            <select name="decision" class="ui-select">
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                                <option value="closed">Close</option>
                            </select>
                            <textarea name="decision_notes" rows="3" placeholder="Notes (optional)" class="ui-textarea"></textarea>
                            <button class="btn btn-primary w-full">Save Decision</button>
                        </form>
                    </div>
                    @endif
                    @endperm
                </div>
            </aside>
        </div>
    </div>
</x-admin-layout>
