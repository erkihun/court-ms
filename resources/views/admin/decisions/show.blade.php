{{-- resources/views/admin/decisions/show.blade.php --}} 
<x-admin-layout title="{{ $decision->name ?? __('decisions.show.title') }}"> 
    @section('page_header', $decision->name ?? __('decisions.show.title')) 

    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex items-start justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span>Case: {{ $decision->case_number ?? '—' }}</span>
                </div>
                <h1 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ $decision->name ?? __('decisions.show.title') }}
                </h1>
                <p class="text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-12 8h14a2 2 0 002-2v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7a2 2 0 002 2z" />
                        </svg>
                        {{ \App\Support\EthiopianDate::format($decision->decision_date, fallback: '—') }}
                    </span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('decisions.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('decisions.create.back') }}
                </a>
                @php
                $middleJudgeId = $decision->panel_judges[1]['admin_user_id'] ?? null;
                $isMiddleJudge = $middleJudgeId && auth()->id() === $middleJudgeId;
                @endphp
                @if($isMiddleJudge)
                <a href="{{ route('decisions.edit', $decision) }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    {{ __('decisions.index.edit') }}
                </a>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-6">
            <!-- Meta -->
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                <div class="space-y-1 rounded-lg border border-gray-100 p-3 bg-gray-50">
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h6" />
                        </svg>
                        Case File Number (መዝገብ ቁጥር)
                    </p>
                    <p class="font-semibold text-gray-900">{{ $decision->case_file_number ?? $decision->case_number ?? '—' }}</p>
                </div>
                <div class="space-y-1 rounded-lg border border-gray-100 p-3 bg-gray-50">
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        {{ __('decisions.fields.case') }}
                    </p>
                    <p class="font-semibold text-gray-900">{{ $decision->case_number ?? '—' }}</p>
                </div>
                <div class="space-y-1 rounded-lg border border-gray-100 p-3 bg-gray-50">
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-12 8h14a2 2 0 002-2v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7a2 2 0 002 2z" />
                        </svg>
                        {{ __('decisions.fields.decision_date') }}
                    </p>
                    <p class="font-semibold text-gray-900">{{ \App\Support\EthiopianDate::format($decision->decision_date, fallback: '—') }}</p>
                </div>
            </div>

            <!-- Parties -->
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
                        </svg>
                        Name of Applicant/Appellant (አመልካች)
                    </p>
                    <p class="font-semibold text-gray-900 mt-1">{{ $decision->applicant_full_name ?: '—' }}</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13V5a3 3 0 00-6 0v8m-2 4h8" />
                        </svg>
                        Name of Respondent (መልስ ሰጭ)
                    </p>
                    <p class="font-semibold text-gray-900 mt-1">{{ $decision->respondent_full_name ?: '—' }}</p>
                </div>
            </div>

            <!-- Judicial Panel -->
            @php
            $panel = $decision->panel_judges ?? [];
            $panel = is_array($panel) ? array_values($panel) : [];
            $reviewLocked = in_array($decision->status, ['active', 'archived'], true);
            @endphp
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2M3 20h12M4 4h16v12H4z" />
                        </svg>
                        <h2 class="text-base font-semibold text-gray-900">Judicial Panel (3 Judges)</h2>
                    </div>
                    <span class="text-xs uppercase tracking-wide text-gray-500">Panel</span>
                </div>
                <div class="grid md:grid-cols-3 gap-3">
                    @for($i=0; $i<3; $i++)
                        @php
                        $judge=$panel[$i] ?? null;
                        $name=$judge['admin_user_name'] ?? null;
                        $vote=$judge['vote'] ?? null;
                        @endphp
                        <div class="border border-gray-100 rounded-lg p-3 space-y-1 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-900">Judge {{ $i+1 }}</div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.657-1.343-3-3-3S6 9.343 6 11s1.343 3 3 3 3-1.343 3-3z" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-700 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
                            </svg>
                            {{ $name ?: '—' }}
                        </div>
                        <div class="text-xs text-gray-500 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Decision: {{ $vote ? ucfirst($vote) : '—' }}
                        </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Status -->
        <div class="flex items-center gap-2">
            <span class="text-gray-600 text-sm flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                </svg>
                {{ __('app.Status') }}:
            </span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide
                    {{ $decision->status === 'active' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : ($decision->status === 'draft' ? 'bg-gray-100 text-gray-700 border border-gray-200' : 'bg-orange-100 text-orange-700 border border-orange-200') }}">
                {{ ucfirst($decision->status ?? '—') }}
            </span>
        </div>

        <!-- Decision Content -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                </svg>
                Final Decision / ውሳኔ
            </h2>
            <div class="prose max-w-none border border-gray-200 rounded-lg p-4 bg-white">
                {!! $decision->decision_content !!}
            </div>
        </div>

        <!-- Decision Reviews -->
        <div class="border border-gray-200 rounded-lg p-4 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Decision Reviews</h2>
                </div>
                <span class="text-xs uppercase tracking-wide text-gray-500">Approve / Reject / Improve</span>
            </div>
            @php
            $reviews = $decision->reviews ?? collect();
            $canReview = function_exists('userHasPermission')
            ? userHasPermission('decision.update')
            : (auth()->user()?->hasPermission('decision.update') ?? false);
            @endphp

                @if($reviews->count() === 0)
                <p class="text-sm text-gray-600">No reviews recorded yet.</p>
                @else
                <div class="grid md:grid-cols-1 gap-3">
                    @foreach($reviews as $review)
                    @php
                $isOwner = auth()->id() === $review->reviewer_id;
                @endphp
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs text-gray-500">Reviewer</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $review->reviewer?->name ?? '—' }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide
                                {{ $review->outcome === 'approve' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' :
                                   ($review->outcome === 'reject' ? 'bg-red-100 text-red-700 border border-red-200' :
                                   ($review->outcome === 'improve' ? 'bg-amber-100 text-amber-700 border border-amber-200' :
                                   'bg-gray-100 text-gray-700 border border-gray-200')) }}">
                            {{ ucfirst($review->outcome ?? 'pending') }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-700">
                        {{ $review->review_note ?: '—' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $review->updated_at ? $review->updated_at->diffForHumans() : '—' }}
                    </div>

                    @if($isOwner && !$reviewLocked)
                    <div class="pt-2 border-t border-gray-200 flex items-center gap-2">
                        <a href="{{ route('decisions.reviews.edit', [$decision, $review]) }}"
                            class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs">
                            Update
                        </a>
                        <form method="POST" action="{{ route('decisions.reviews.destroy', [$decision, $review]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs"
                                onclick="return confirm('Delete this review?')">
                                Delete
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($canReview && !$reviewLocked)
            <div class="border border-dashed border-gray-300 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Add Review</h3>
                <form method="POST" action="{{ route('decisions.reviews.store', $decision) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Outcome</label>
                        <select name="outcome"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                            <option value="improve">Improve</option>
                        </select>
                        @error('outcome') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Review Note</label>
                        <textarea name="review_note" rows="3"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            placeholder="Add a brief review or requested improvements">{{ old('review_note') }}</textarea>
                        @error('review_note') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end">
                        <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>

    </div>
    </div>

</x-admin-layout>
