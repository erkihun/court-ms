<div class="mb-6 p-4 rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center gap-4 justify-between">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-6">
            <div>
                <div class="text-xs text-gray-600 font-medium uppercase tracking-wide">{{ __('cases.case_number') }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <div class="font-mono text-xl font-bold text-gray-900" id="case-no">{{ $case->case_number }}</div>
                    <button
                        type="button"
                        class="px-2.5 py-1 text-xs rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 transition-all duration-200 hover:scale-105"
                        x-data
                        x-on:click="
                            navigator.clipboard.writeText(document.querySelector('#case-no').textContent);
                            $el.innerText='{{ __('cases.copied') }}';
                            setTimeout(()=>{$el.innerText='{{ __('cases.copy') }}';},1200);
                        ">{{ __('cases.copy') }}</button>
                </div>
            </div>

            <span class="px-3 py-1.5 rounded-full text-xs font-semibold capitalize {{ $statusChip($currentStatus) }}">
                {{ $currentStatus }}
            </span>

            <span class="px-3 py-1.5 rounded-full text-xs font-semibold capitalize {{ $reviewChip($reviewStatus) }}">
                {{ $reviewLabel($reviewStatus) }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2 justify-end">
            <a href="{{ route('cases.index') }}"
                class="order-first px-4 py-2.5 rounded-lg bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 border border-gray-300 transition-all duration-200 shadow-sm hover:shadow">
                {{ __('cases.back') }}
            </a>

            @if(in_array($reviewStatus, ['awaiting_review','returned']) && $canReview)
            <div class="flex flex-wrap gap-2">
                <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white shadow-sm hover:shadow transition-all duration-200"
                    onclick="submitReviewDecision('accept')">Accept</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white shadow-sm hover:shadow transition-all duration-200"
                    onclick="openReviewModal('return')">Return</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white shadow-sm hover:shadow transition-all duration-200"
                    onclick="openReviewModal('reject')">Reject</button>
            </div>
            @endif

            @if(!$caseLocked && $canAssign)
            <a href="{{ route('cases.assign.form', $case->id) }}"
                class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ __('cases.assign_change') }}
            </a>
            @endif

            @if(!$caseLocked && $canManageBench)
            <a href="{{ route('bench-notes.index', ['case_id' => $case->id]) }}"
                class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow">
                Bench note
            </a>
            @endif

            @if(!$caseLocked && $canWriteLetter)
            <a href="#letters-compose"
                @click.prevent="openSection('letters-compose')"
                class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow">
                Write letter
            </a>
            @endif

            @if(!$caseLocked && ($case->status ?? '') === 'closed')
            <a href="{{ route('decisions.create', ['case_id' => $case->id]) }}"
                class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow">
                Give decision
            </a>
            @endif

            @include('admin.cases.case-partials.quick-access')
        </div>
    </div>

    @if($caseLocked)
    <div class="mt-4 px-4 py-3 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-2 text-sm shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 19a7 7 0 110-14 7 7 0 010 14z" />
        </svg>
        <span>Actions are locked because this case is closed and has an active decision.</span>
    </div>
    @endif
</div>
