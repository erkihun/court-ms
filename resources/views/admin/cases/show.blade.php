{{-- resources/views/cases/show.blade.php --}}
<x-admin-layout title="{{ __('cases.case_details') }}">
    @section('page_header', __('cases.case_details'))

    @php
    // Safer permission checks (works even if user() is null or helper exists)
    $canEditStatus = function_exists('userHasPermission')
    ? userHasPermission('cases.edit')
    : (auth()->user()?->hasPermission('cases.edit') ?? false);

    $canAssign = function_exists('userHasPermission')
    ? userHasPermission('cases.assign')
    : (auth()->user()?->hasPermission('cases.assign') ?? false);

    $currentStatus = $case->status ?? 'pending';
    $reviewStatus  = $case->review_status ?? 'accepted';
    $reviewNote    = $case->review_note ?? null;

    $canReview = function_exists('userHasPermission')
        ? userHasPermission('cases.review')
        : (auth()->user()?->can('cases.review') ?? false);

    $statuses = [
    'pending' => __('cases.status.pending'),
    'active' => __('cases.status.active'),
    'adjourned' => __('cases.status.adjourned'),
    'dismissed' => __('cases.status.dismissed'),
    'closed' => __('cases.status.closed'),
    ];

    $statusChip = fn (string $s) => match ($s) {
    'pending' => 'bg-amber-100 text-amber-800 border border-amber-300',
    'active' => 'bg-blue-100 text-blue-800 border border-blue-300',
    'adjourned' => 'bg-purple-100 text-purple-800 border border-purple-300',
    'dismissed' => 'bg-rose-100 text-rose-800 border border-rose-300',
    'closed' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
    default => 'bg-gray-100 text-gray-800 border border-gray-300',
    };

    $reviewChip = fn (string $s) => match ($s) {
        'awaiting_review' => 'bg-amber-100 text-amber-800 border border-amber-300',
        'returned'        => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
        'rejected'        => 'bg-rose-100 text-rose-800 border border-rose-300',
        default           => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
    };
    $reviewLabel = fn (string $s) => match ($s) {
        'awaiting_review' => 'Awaiting approval',
        'returned'        => 'Needs correction',
        'rejected'        => 'Rejected',
        default           => 'Approved',
    };
    @endphp

    @push('styles')
    <style>
        /* Pretty output for sanitized HTML fields */
        .cms-output {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: .75rem;
            padding: 1rem;
            color: #111827;
            font-size: .925rem;
            line-height: 1.65;

        }

        .cms-output p {
            margin: 0 0 .65rem;
            text-align: justify;

        }

        .cms-output ul {
            list-style: disc;
            margin: .5rem 0 .75rem 1.25rem;
            padding-left: 1rem;
        }

        .cms-output ol {
            list-style: decimal;
            margin: .5rem 0 .75rem 1.25rem;
            padding-left: 1rem;
        }

        .cms-output li {
            margin: .15rem 0;
        }

        .cms-output blockquote {
            border-left: 4px solid #E5E7EB;
            padding-left: 1rem;
            color: #374151;
            margin: .75rem 0;
        }

        .cms-output h1,
        .cms-output h2,
        .cms-output h3,
        .cms-output h4,
        .cms-output h5,
        .cms-output h6 {
            font-weight: 600;
            margin: .75rem 0 .5rem;
        }

        .cms-output a {
            text-decoration: underline;
        }

        .cms-output table {
            width: 100%;
            border-collapse: collapse;
            margin: .75rem 0;
        }

        .cms-output th,
        .cms-output td {
            border: 1px solid #E5E7EB;
            padding: .35rem .5rem;
        }
    </style>

    @endpush

    {{-- Top "action bar" --}}
    <div class="mb-4 p-2 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-4">
                <div>
                    <div class="text-xs text-gray-600 font-medium uppercase tracking-wide">{{ __('cases.case_number') }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="font-mono text-lg font-bold text-gray-900" id="case-no">{{ $case->case_number }}</div>
                        <button
                            type="button"
                            class="px-2 py-1 text-xs rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 transition-colors duration-150"
                            x-data
                            x-on:click="
                                navigator.clipboard.writeText(document.querySelector('#case-no').textContent);
                                $el.innerText='{{ __('cases.copied') }}';
                                setTimeout(()=>{$el.innerText='{{ __('cases.copy') }}';},1200);
                            ">{{ __('cases.copy') }}</button>
                    </div>
                </div>

                <span class="px-3 py-1.5 rounded-full text-xs font-medium capitalize {{ $statusChip($currentStatus) }}">
                    {{ $currentStatus }}
                </span>

                <span class="px-3 py-1.5 rounded-full text-xs font-medium capitalize {{ $reviewChip($reviewStatus) }}">
                    {{ $reviewLabel($reviewStatus) }}
                </span>



            </div>


            <div class="flex flex-wrap items-center gap-2">
                @if(in_array($reviewStatus, ['awaiting_review','returned']) && $canReview)
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-sm font-medium text-white"
                        onclick="submitReviewDecision('accept')">Accept</button>
                    <button type="button" class="px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-sm font-medium text-white"
                        onclick="openReviewModal('return')">Return</button>
                    <button type="button" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-sm font-medium text-white"
                        onclick="openReviewModal('reject')">Reject</button>
                </div>
                @endif
                @if($canAssign)
                <a href="{{ route('cases.assign.form', $case->id) }}"
                    class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium text-white transition-colors duration-150 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ __('cases.assign_change') }}
                </a>
                @endif
                <a href="{{ route('cases.index') }}"
                    class="px-4 py-2 rounded-lg bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 border border-gray-300 transition-colors duration-150 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('cases.back') }}
                </a>
                <button onclick="window.print()"
                    class="px-4 py-2 rounded-lg bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 border border-gray-300 transition-colors duration-150 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    {{ __('cases.print') }}
                </button>

            </div>
        </div>
    </div>

    {{-- Review status + note --}}
    <div class="p-3 rounded-xl border border-gray-200 bg-white shadow-sm mb-3">
        <div class="flex flex-col gap-2">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-semibold text-gray-900">Review status:</span>
                <span class="px-3 py-1.5 rounded-full text-xs font-medium capitalize {{ $reviewChip($reviewStatus) }}">
                    {{ $reviewLabel($reviewStatus) }}
                </span>
                @if(!empty($case->reviewed_at))
                <span class="text-xs text-gray-500">
                    Updated {{ \Illuminate\Support\Carbon::parse($case->reviewed_at)->format('M d, Y h:i A') }}
                </span>
                @endif
            </div>
            @if(!empty($reviewNote))
            <div class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3">
                <div class="text-xs uppercase text-gray-500 font-semibold mb-1">Reviewer note</div>
                <div>{{ $reviewNote }}</div>
            </div>
            @elseif(in_array($reviewStatus, ['returned','rejected']))
            <div class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">
                Please add a reviewer note for returned/rejected decisions.
            </div>
            @endif
        </div>
    </div>

    {{-- Modal for return/reject note --}}
    <div id="review-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-30">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-5 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-2" id="review-modal-title">Review decision</h3>
            <form method="POST" action="{{ route('cases.review.update', $case->id) }}" id="review-form" class="space-y-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="decision" id="review-decision" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason / note</label>
                    <textarea name="note" id="review-note" rows="3" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeReviewModal()" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-800 border border-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <form id="review-quick-form" method="POST" action="{{ route('cases.review.update', $case->id) }}" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="decision" id="review-quick-decision" value="accept">
        <input type="hidden" name="note" value="">
    </form>

    {{-- Status change (admins) --}}
    @if($canEditStatus)
    <div class="p-2 rounded-xl border border-gray-200 bg-white shadow-sm mb-3">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ __('cases.status.change_case_status') }}
        </h3>
        <form method="POST" action="{{ route('cases.status.update', $case->id) }}" class="grid md:grid-cols-3 gap-8 items-end">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('cases.status.new_status') }}</label>
                <select name="status" class="w-full px-4 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150">
                    @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($currentStatus===$value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('cases.status.note_to_timeline') }}</label>
                <input name="note" placeholder="{{ __('cases.status.add_note_placeholder') }}"
                    class="w-full px-4 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150">
                @error('note') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <button class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors duration-150 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('cases.status.update_status') }}
                </button>
            </div>
        </form>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6"
        x-data="{
            activeSection: 'case-summary',
            switchSection(section) { this.activeSection = section; history.pushState(null, null, '#' + section); }
         }"
        x-init="
            if (window.location.hash) { activeSection = window.location.hash.substring(1); }
            window.addEventListener('popstate', function() {
                if (window.location.hash) { activeSection = window.location.hash.substring(1); }
            });
         ">
        {{-- Sidebar Navigation --}}
        <div class="lg:col-span-1">
            <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm sticky top-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    {{ __('cases.navigation.title') }}
                </h3>
                <nav class="space-y-2">
                    <button @click="switchSection('case-summary')"
                        :class="activeSection === 'case-summary' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'case-summary' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.case_summary') }}</span>
                    </button>

                    <button @click="switchSection('case-details')"
                        :class="activeSection === 'case-details' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'case-details' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.case_details') }}</span>
                    </button>

                    <button @click="switchSection('submitted-documents')"
                        :class="activeSection === 'submitted-documents' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'submitted-documents' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.submitted_documents') }}</span>
                    </button>

                    <button @click="switchSection('uploaded-files')"
                        :class="activeSection === 'uploaded-files' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'uploaded-files' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.uploaded_files') }}</span>
                    </button>

                    <button @click="switchSection('witnesses')"
                        :class="activeSection === 'witnesses' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'witnesses' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.witnesses') }}</span>
                    </button>

                    <button @click="switchSection('hearings')"
                        :class="activeSection === 'hearings' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'hearings' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.hearings') }}</span>
                    </button>

                    <button @click="switchSection('timeline')"
                        :class="activeSection === 'timeline' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'timeline' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.timeline') }}</span>
                    </button>

                    <button @click="switchSection('messages')"
                        :class="activeSection === 'messages' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'messages' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <span class="font-medium">{{ __('cases.navigation.messages') }}</span>
                    </button>

                    <button @click="switchSection('audits')"
                        :class="activeSection === 'audits' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent transition-all duration-200 group w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="activeSection === 'audits' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h8" />
                        </svg>
                        <span class="font-medium">Case Audits</span>
                    </button>
                </nav>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-3 space-y-4">

            {{-- Case Summary --}}
            <section x-show="activeSection === 'case-summary'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('cases.navigation.case_summary') }}
                </h3>

                <div class="space-y-1">
                    <div class="text-xs font-medium text-gray-600 uppercase tracking-wide">{{ __('cases.summary.title') }}</div>
                    <div class="text-gray-900 font-medium text-lg leading-tight">{{ $case->title }}</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.type') }}</div>
                            <div class="text-gray-900 font-medium">{{ $case->case_type ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.filing_date') }}</div>
                            <div class="text-gray-900 font-medium">
                                {{ $case->filing_date ? \Illuminate\Support\Carbon::parse($case->filing_date)->format('M d, Y') : '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.first_hearing') }}</div>
                            <div class="text-gray-900 font-medium">
                                {{ $case->first_hearing_date ? \Illuminate\Support\Carbon::parse($case->first_hearing_date)->format('M d, Y') : '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.applicant') }}</div>
                            <div class="text-gray-900 font-medium">{{ $case->applicant_name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.applicant_email') }}</div>
                            <div class="text-gray-900 font-medium">{{ $case->applicant_email ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-200">
                    <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-2">{{ __('cases.summary.assignee') }}</div>
                    @if($case->assignee_name)
                    <div class="text-gray-900 font-medium flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ $case->assignee_name }}
                        <span class="text-gray-600 text-xs">({{ $case->assignee_email }})</span>
                    </div>
                    @else
                    <div class="text-gray-500 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        — {{ __('cases.summary.unassigned') }} —
                    </div>
                    @endif

                </div>

                <div class="pt-2 border-t border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.created') }}</div>
                            <div class="text-gray-900 font-medium">
                                {{ $case->created_at ? \Illuminate\Support\Carbon::parse($case->created_at)->format('M d, Y h:i A') : '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.updated') }}</div>
                            <div class="text-gray-900 font-medium">
                                {{ $case->updated_at ? \Illuminate\Support\Carbon::parse($case->updated_at)->format('M d, Y h:i A') : '—' }}
                            </div>
                        </div>
                        @if($case->assigned_at)
                        <div class="sm:col-span-2">
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.assigned_at') }}</div>
                            <div class="text-gray-900 font-medium">
                                {{ \Illuminate\Support\Carbon::parse($case->assigned_at)->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </section>

            {{-- Case Audits --}}
            <section x-show="activeSection === 'audits'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Case Audit Trail
                    </h3>
                    <span class="text-xs text-gray-500">{{ ($audits ?? collect())->count() }} entries</span>
                </div>
                @if(($audits ?? collect())->isEmpty())
                <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                    No audit records yet.
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="p-2 text-left">When</th>
                                <th class="p-2 text-left">Action</th>
                                <th class="p-2 text-left">Actor</th>
                                <th class="p-2 text-left">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($audits as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 text-gray-700 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($a->created_at)->format('M d, Y H:i') }}
                                </td>
                                <td class="p-2 text-gray-900 font-medium">{{ str_replace('_',' ', ucfirst($a->action)) }}</td>
                                <td class="p-2 text-gray-700 text-xs">
                                    @if(!empty($a->actor_name))
                                        {{ $a->actor_name }} @if($a->actor_id)(#{{ $a->actor_id }})@endif
                                    @elseif(!empty($a->actor_id))
                                        {{ $a->actor_type ?? 'system' }} (#{{ $a->actor_id }})
                                    @else
                                        {{ $a->actor_type ?? 'system' }}
                                    @endif
                                </td>
                                <td class="p-2 text-gray-700 text-xs">
                                    @php $meta = $a->meta ? json_decode($a->meta, true) : []; @endphp
                                    @if($meta)
                                    <pre class="bg-gray-100 border border-gray-200 rounded px-2 py-1 whitespace-pre-wrap text-[11px]">{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </section>

            {{-- Case Details --}}
            <section x-show="activeSection === 'case-details'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="space-y-2">

                {{-- DESCRIPTION (rich HTML, uses sanitized HTML from controller if available) --}}
                <section class="lg:col-span-2 rounded-xl border bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('cases.details.case_details') }}</h3>
                    <div class="cms-output ">
                        {!! $case->description_html ?? clean($case->description ?? '', 'cases') !!}
                    </div>
                </section>

                {{-- RELIEF (CONDITIONAL, rich HTML) --}}
                @php
                $reliefHtmlOut = $case->relief_requested_html
                ?? (!empty($case->relief_requested) ? clean($case->relief_requested, 'cases') : null);
                @endphp
                @if(!empty($reliefHtmlOut))
                <section class="lg:col-span-2 rounded-xl border bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('cases.details.relief_requested') }}</h3>
                    <div class="cms-output ">
                        {!! $reliefHtmlOut !!}
                    </div>
                </section>
                @endif
            </section>

            {{-- Hearings --}}
            <section x-show="activeSection === 'hearings'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-2">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('cases.hearings.title') }}
                    </h3>
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($hearings ?? collect())->count() }} {{ __('cases.hearings.total') }}</span>
                </div>

                @if(($hearings ?? collect())->isEmpty())
                <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('cases.hearings.no_hearings') }}
                </div>
                @else
                <ul class="divide-y divide-gray-200">
                    @foreach($hearings as $h)
                    <li class="py-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div class="text-gray-900">
                                <span class="font-medium text-lg">
                                    {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                                </span>
                                <span class="text-gray-600 ml-2">• {{ $h->type ?? __('cases.hearings.title') }}</span>
                                @if($h->location)
                                <span class="text-gray-600 ml-2">• {{ $h->location }}</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- Inline edit --}}
                                <details class="relative">
                                    <summary class="px-3 py-1.5 rounded-lg bg-white text-xs cursor-pointer text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ __('cases.general.edit') }}
                                    </summary>
                                    <div class="absolute right-0 z-10 mt-2 w-80 p-4 rounded-lg border border-gray-200 bg-white shadow-xl">
                                        <form method="POST" action="{{ route('cases.hearings.update',$h->id) }}" class="space-y-3">
                                            @csrf @method('PATCH')
                                            <input type="datetime-local" name="hearing_at"
                                                value="{{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('Y-m-d\TH:i') }}"
                                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <input name="type" value="{{ $h->type }}" placeholder="{{ __('cases.hearings.type_placeholder') }}"
                                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <input name="location" value="{{ $h->location }}" placeholder="{{ __('cases.hearings.location_placeholder') }}"
                                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <textarea name="notes" rows="2" placeholder="{{ __('cases.hearings.notes_placeholder') }}"
                                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $h->notes }}</textarea>
                                            <div class="flex justify-end gap-2 pt-1">
                                                <button class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-xs text-white font-medium transition-colors duration-150">{{ __('cases.general.save') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </details>

                                <form method="POST" action="{{ route('cases.hearings.delete',$h->id) }}"
                                    onsubmit="return confirm('{{ __('cases.hearings.remove_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium transition-colors duration-150 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ __('cases.general.delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($h->notes)
                        <div class="text-xs text-gray-600 mt-2 bg-gray-50 rounded p-2 border border-gray-200">
                            {{ __('cases.hearings.notes_placeholder') }}: {{ $h->notes }}
                        </div>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif

                {{-- Create --}}
                @if($canEditStatus)
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('cases.hearings.add_new_hearing') }}</h4>
                    <form method="POST" action="{{ route('cases.hearings.store', $case->id) }}"
                        class="grid md:grid-cols-5 gap-3">
                        @csrf
                        <input type="datetime-local" name="hearing_at"
                            class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 md:col-span-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150" required>
                        <input name="type" placeholder="{{ __('cases.hearings.type_placeholder') }}"
                            class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150">
                        <input name="location" placeholder="{{ __('cases.hearings.location_placeholder') }}"
                            class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150">
                        <input name="notes" placeholder="{{ __('cases.hearings.notes_placeholder') }}"
                            class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150">
                        <button class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors duration-150 flex items-center justify-center gap-1 md:col-span-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ __('cases.hearings.add_hearing') }}
                        </button>
                    </form>
                </div>
                @endif
            </section>

            {{-- Submitted Documents --}}
            <section x-show="activeSection === 'submitted-documents'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('cases.documents.submitted_documents') }}
                    </h3>
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($docs ?? collect())->count() }} {{ __('cases.documents.items') }}</span>
                </div>

                @if(($docs ?? collect())->isEmpty())
                <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('cases.documents.no_documents') }}
                </div>
                @else
                <ul class="divide-y divide-gray-200">
                    @foreach($docs as $d)
                    @php
                        $filePath = $d->file_path ?? $d->path ?? null;
                        $docTitle = $d->title ?? ($d->label ?? ($filePath ? basename($filePath) : __('cases.documents.document')));
                        $fileTime = !empty($d->created_at) ? \Illuminate\Support\Carbon::parse($d->created_at)->format('M d, Y H:i') : null;
                        $fileSize = isset($d->size) ? number_format(max(0, (int) $d->size) / 1024, 1) : null;
                    @endphp
                    <li class="py-3 px-3 hover:bg-gray-50 rounded-lg transition-colors duration-150">
                        <div class="flex items-start justify-between gap-2">
                            <div class="text-sm flex-1">
                                <div class="font-medium text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $docTitle }}
                                </div>
                                <div class="text-xs text-gray-500 flex flex-wrap gap-2 mt-1">
                                    <span>{{ $d->mime ?? __('cases.documents.document') }}</span>
                                    @if($fileSize)<span>· {{ $fileSize }} KB</span>@endif
                                    @if($fileTime)<span>· {{ $fileTime }}</span>@endif
                                </div>
                                @if(!empty($d->description))
                                <div class="text-xs text-gray-600 mt-2 tiny-content">
                                    {!! clean($d->description, 'cases') !!}
                                </div>
                                @endif
                            </div>
                            <a @if($filePath) href="{{ route('cases.documents.view', [$case->id, $d->id]) }}" target="_blank" @endif
                                class="px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors duration-150 flex items-center gap-1
                                       {{ $filePath ? 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100' : 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed' }}"
                                @unless($filePath) aria-disabled="true" @endunless>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('cases.documents.view') }}
                            </a>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </section>

            {{-- Witnesses --}}
            <section x-show="activeSection === 'witnesses'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">

                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('cases.witnesses_section.title') }}
                    </h3>
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">
                        {{ ($witnesses ?? collect())->count() }} {{ __('cases.witnesses_section.total') }}
                    </span>
                </div>


                @if(($witnesses ?? collect())->isEmpty())
                <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('cases.witnesses_section.no_witnesses') }}
                </div>
                @else
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                    {{ __('cases.labels.name') }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                    {{ __('cases.labels.phone') }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                    {{ __('cases.labels.email') }}
                                </th>
                                <th class="px-3 py-2 text-left font-medium border-b border-gray-200">
                                    {{ __('cases.labels.address') }}
                                </th>


                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($witnesses as $w)
                            <tr class="hover:bg-gray-50">
                                {{-- Name --}}
                                <td class="px-3 py-2 align-top">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="font-medium text-gray-900">
                                            {{ $w->full_name }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Phone --}}
                                <td class="px-3 py-2 align-top text-gray-700">
                                    {{ $w->phone ?: '—' }}
                                </td>

                                {{-- Email --}}
                                <td class="px-3 py-2 align-top">
                                    @if(!empty($w->email))
                                    <a href="mailto:{{ $w->email }}" class="text-blue-700 hover:underline">
                                        {{ $w->email }}
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Address --}}
                                <td class="px-3 py-2 align-top text-gray-700">
                                    {{ $w->address ?: '—' }}
                                </td>




                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </section>


            {{-- Timeline --}}
            <section x-show="activeSection === 'timeline'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('cases.timeline_section.title') }}
                    </h3>
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($timeline ?? collect())->count() }} {{ __('cases.timeline_section.events') }}</span>
                </div>

                @if(($timeline ?? collect())->isEmpty())
                <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('cases.timeline_section.no_history') }}
                </div>
                @else
                <ol class="space-y-4 text-sm relative before:absolute before:top-0 before:bottom-0 before:left-4 before:w-0.5 before:bg-gray-300">
                    @foreach($timeline as $t)
                    @php
                    $dot = match(($t->to_status ?? '')) {
                    'active' => 'bg-blue-500 ring-blue-500/20',
                    'closed', 'dismissed' => 'bg-emerald-500 ring-emerald-500/20',
                    'pending' => 'bg-amber-500 ring-amber-500/20',
                    default => 'bg-gray-400 ring-gray-400/20',
                    };
                    @endphp
                    <li class="relative pl-10">
                        <div class="absolute left-0 top-1.5 h-3 w-3 rounded-full {{ $dot }} ring-4 z-10"></div>
                        <div class="text-gray-800 bg-gray-50 rounded-lg p-3 border border-gray-200">
                            {{ $t->from_status ? ucfirst($t->from_status).' → ' : '' }}
                            <strong>{{ ucfirst($t->to_status) }}</strong>
                            @if(!empty($t->note)) — <span class="text-gray-600">{{ $t->note }}</span>@endif
                            <div class="text-gray-600 text-xs mt-2">
                                {{ \Illuminate\Support\Carbon::parse($t->created_at)->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ol>
                @endif
            </section>

            {{-- Messages --}}
            <section x-show="activeSection === 'messages'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        {{ __('cases.messages_section.title') }}
                    </h3>
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($messages ?? collect())->count() }} {{ __('cases.messages.total') }}</span>
                </div>

                <div class="space-y-4 max-h-96 overflow-auto pr-2">
                    @forelse($messages as $m)
                    @php
                    $fromAdmin = !is_null($m->sender_user_id);
                    $fromApplicant = !is_null($m->sender_applicant_id);
                    $who = $fromAdmin
                    ? ($m->admin_name ?: __('cases.messages.court_staff'))
                    : ($fromApplicant ? trim(($m->first_name ?? '').' '.($m->last_name ?? '')) : __('cases.messages_section.system'));
                    @endphp
                    <div class="rounded-lg border border-gray-200 p-4 {{ $fromAdmin ? 'bg-blue-50 ml-8' : 'bg-gray-50 mr-8' }}">
                        <div class="text-xs text-gray-600 mb-2 flex items-center justify-between">
                            <span class="font-medium text-gray-900">{{ $who }}</span>
                            <span>{{ \Illuminate\Support\Carbon::parse($m->created_at)->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="whitespace-pre-wrap text-gray-800 text-sm">{{ $m->body }}</div>
                    </div>
                    @empty
                    <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        {{ __('cases.messages_section.no_messages') }}
                    </div>
                    @endforelse
                </div>

                @if($canEditStatus)
                <form method="POST" action="{{ route('cases.messages.post', $case->id) }}" class="pt-4 border-t border-gray-200 space-y-3">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">{{ __('cases.messages_section.reply_to_applicant') }}</label>
                    <textarea name="body" rows="3"
                        class="w-full px-4 py-3 rounded-lg bg-white text-gray-900 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
                        placeholder="{{ __('cases.messages_section.write_message_placeholder') }}">{{ old('body') }}</textarea>
                    @error('body') <p class="text-red-600 text-sm p-2 bg-red-50 rounded-lg border border-red-200">{{ $message }}</p> @enderror

                    <button class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors duration-150 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        {{ __('cases.messages_section.send_message') }}
                    </button>
                </form>
                @endif
            </section>

            {{-- Uploaded Files --}}
            <section x-show="activeSection === 'uploaded-files'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('cases.files.title') }}
                    </h3>
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($files ?? collect())->count() }} {{ __('cases.files.total') }}</span>
                </div>

                @if($canEditStatus)
                <form method="POST" action="{{ route('cases.files.upload', $case->id) }}"
                    enctype="multipart/form-data"
                    class="mb-2 grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    @csrf
                    <input name="label" placeholder="{{ __('cases.files.label_placeholder') }}"
                        class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150">
                    <input type="file" name="file" required
                        class="text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 transition-colors duration-150">
                    <button class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors duration-150 flex items-center justify-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        {{ __('cases.files.upload') }}
                    </button>
                </form>
                @error('file') <div class="text-red-600 text-sm mb-2 p-2 bg-red-50 rounded-lg border border-red-200">{{ $message }}</div> @enderror
                @endif

                @if(($files ?? collect())->isEmpty())
                <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('cases.files.no_files') }}
                </div>
                @else
                <ul class="divide-y divide-gray-200">
                    @foreach($files as $f)
                    <li class="py-3 flex items-center justify-between hover:bg-gray-50 px-3 rounded-lg transition-colors duration-150">
                        <div class="text-sm">
                            <div class="font-medium text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $f->label ?? basename($f->path) }}
                            </div>
                            <div class="text-xs text-gray-600 mt-1 flex items-center gap-3 flex-wrap">
                                <span>{{ $f->mime ?? 'file' }}</span>
                                <span>• {{ number_format(($f->size ?? 0)/1024,1) }} KB</span>
                                <span>• {{ \Illuminate\Support\Carbon::parse($f->created_at)->format('M d, Y H:i') }}</span>
                                @php $by = $f->uploader_name ?? trim(($f->first_name ?? '').' '.($f->last_name ?? '')); @endphp
                                @if($by) <span>• {{ __('cases.files.uploaded_by') }} {{ $by }}</span> @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ asset('storage/'.$f->path) }}" target="_blank"
                                class="px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 text-xs text-gray-700 border border-gray-300 transition-colors duration-150 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('cases.documents.view') }}
                            </a>
                            @if($canEditStatus)
                            <form method="POST" action="{{ route('cases.files.delete', [$case->id, $f->id]) }}"
                                onsubmit="return confirm('{{ __('cases.files.remove_confirm') }}')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium transition-colors duration-150 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ __('cases.general.delete') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </section>
        </div>
    </div>
</x-admin-layout>

<script>
    function openReviewModal(decision) {
        const modal = document.getElementById('review-modal');
        document.getElementById('review-decision').value = decision;
        document.getElementById('review-note').value = '';
        const title = decision === 'return' ? 'Return for correction' : 'Reject case';
        document.getElementById('review-modal-title').textContent = title;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('review-note').focus();
    }
    function closeReviewModal() {
        const modal = document.getElementById('review-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function submitReviewDecision(decision) {
        if (decision === 'accept') {
            document.getElementById('review-quick-decision').value = decision;
            document.getElementById('review-quick-form').submit();
            return;
        }
        openReviewModal(decision);
    }
</script>
