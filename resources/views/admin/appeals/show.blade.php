<x-admin-layout :title="$appeal->appeal_number">
    @section('page_header', $appeal->appeal_number)

    <div class="grid md:grid-cols-3 gap-4">
        {{-- Main card --}}
        <div class="md:col-span-2 rounded border border-gray-200 bg-white p-4 md:p-6 space-y-3 shadow-sm">
            <div class="text-sm text-gray-600">
                Case: <span class="text-gray-900">{{ $appeal->case_number }}</span>
                <span class="text-gray-500">—</span>
                <span class="text-gray-900">{{ $appeal->case_title }}</span>
            </div>

            <div class="text-lg font-semibold text-gray-900">{{ $appeal->title }}</div>

            <div class="text-[11px] uppercase tracking-wide text-gray-600">
                Status:
                <span class="ml-1 rounded-md border px-2 py-0.5 capitalize
                    @class([
                        'border-blue-300 bg-blue-100 text-blue-800' => $appeal->status==='submitted' || $appeal->status==='under_review',
                        'border-amber-300 bg-amber-100 text-amber-800' => $appeal->status==='draft',
                        'border-emerald-300 bg-emerald-100 text-emerald-800' => $appeal->status==='approved',
                        'border-rose-300 bg-rose-100 text-rose-800' => $appeal->status==='rejected',
                        'border-gray-300 bg-gray-100 text-gray-800' => $appeal->status==='closed',
                    ])
                ">
                    {{ $appeal->status }}
                </span>
            </div>

            @if(!empty($appeal->grounds))
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($appeal->grounds)) !!}
            </div>
            @endif

            @perm('appeals.edit')
            @if($appeal->status==='draft')
            <form method="POST" action="{{ route('appeals.submit',$appeal->id) }}" class="mt-2 inline">
                @csrf
                <button
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white
                                   hover:bg-blue-500 border border-blue-600/70 transition-colors duration-200">
                    Submit
                </button>
            </form>

            <a href="{{ route('appeals.edit',$appeal->id) }}"
                class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800
                              hover:bg-gray-300 border border-gray-300 transition-colors duration-200 ml-2">
                Edit
            </a>
            @endif
            @endperm
        </div>

        {{-- Aside: documents & decision --}}
        <aside class="rounded border border-gray-200 bg-white p-4 md:p-6 space-y-4 shadow-sm">
            <div>
                <div class="font-medium text-gray-900 mb-2">Documents</div>
                <form class="flex flex-col sm:flex-row gap-2"
                    method="POST"
                    action="{{ route('appeals.docs.upload',$appeal->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input name="label" placeholder="Label"
                        class="w-full rounded-md border border-gray-300 bg-white text-gray-900 px-3 py-2
                                  focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500">
                    <input type="file" name="file" required
                        class="text-sm text-gray-700 file:mr-2 file:rounded-md file:border file:border-gray-300
                                  file:bg-gray-100 file:text-gray-700 file:px-3 file:py-1.5">
                    <button class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-500 transition-colors duration-200">
                        Upload
                    </button>
                </form>
            </div>

            <ul class="divide-y divide-gray-200">
                @forelse($docs as $d)
                <li class="py-2 flex items-center justify-between">
                    <div class="text-sm text-gray-900">
                        {{ $d->label ?? basename($d->path) }}
                        <div class="text-xs text-gray-600">
                            {{ \App\Support\EthiopianDate::format($d->created_at, withTime: true) }}
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ asset('storage/'.$d->path) }}" target="_blank" class="text-blue-600 text-sm hover:text-blue-800">View</a>
                        @perm('appeals.edit')
                        <form method="POST" action="{{ route('appeals.docs.delete', [$appeal->id, $d->id]) }}"
                            onsubmit="return confirm('Delete this document?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 text-sm hover:text-red-800">Delete</button>
                        </form>
                        @endperm
                    </div>
                </li>
                @empty
                <li class="py-8 text-center text-sm text-gray-500 border border-dashed border-gray-300 rounded">
                    No documents uploaded.
                </li>
                @endforelse
            </ul>

            @perm('appeals.decide')
            @if(in_array($appeal->status,['submitted','under_review']))
            <div class="pt-2 border-t border-gray-200">
                <div class="font-medium text-gray-900 mb-2">Record decision</div>
                <form method="POST" action="{{ route('appeals.decide',$appeal->id) }}" class="space-y-2">
                    @csrf
                    <select name="decision"
                        class="w-full rounded-md border border-gray-300 bg-white text-gray-900 px-3 py-2
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500">
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                        <option value="closed">Close</option>
                    </select>
                    <textarea name="decision_notes" rows="3" placeholder="Notes (optional)"
                        class="w-full rounded-md border border-gray-300 bg-white text-gray-900 px-3 py-2
                                         focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500"></textarea>
                    <button class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 transition-colors duration-200">
                        Save decision
                    </button>
                </form>
            </div>
            @endif
            @endperm
        </aside>
    </div>
</x-admin-layout>
