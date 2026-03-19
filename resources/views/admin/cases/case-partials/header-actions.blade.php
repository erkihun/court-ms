<div class="admin-hero admin-subtle-grid mb-6">
    <div class="flex flex-wrap items-center gap-4 justify-between">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-6">
            <div>
                <div class="text-xs font-medium uppercase tracking-[0.2em] text-slate-300">{{ __('cases.case_number') }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <div class="font-mono text-2xl font-bold text-white" id="case-no">{{ $case->case_number }}</div>
                    <button
                        type="button"
                        class="btn btn-outline !rounded-full !border-white/20 !bg-white/10 !px-3 !py-1.5 !text-xs !font-semibold !tracking-normal !text-white hover:!bg-white/20"
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

        <div class="admin-header-actions">
            <a href="{{ route('cases.index') }}"
                class="btn btn-outline order-first !border-white/20 !bg-white/10 !text-white hover:!bg-white/20">
                {{ __('cases.back') }}
            </a>

            @if(in_array($reviewStatus, ['awaiting_review','returned','rejected']) && $canReview)
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary !bg-blue-500 hover:!bg-blue-400"
                    onclick="submitReviewDecision('accept')">Accept</button>
                <button type="button" class="btn btn-primary !bg-blue-500 hover:!bg-blue-400"
                    onclick="openReviewModal('return')">Return</button>
                <button type="button" class="btn btn-primary !bg-blue-500 hover:!bg-blue-400"
                    onclick="openReviewModal('reject')">Reject</button>
            </div>
            @endif

            @if(!$caseLocked && $canAssign)
            <a href="{{ route('cases.assign.form', $case->id) }}"
                class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ __('cases.assign_change') }}
            </a>
            @endif

            @if(!$caseLocked && $canManageBench)
            <a href="{{ route('bench-notes.index', ['case_id' => $case->id]) }}"
                class="btn btn-primary">
                Bench note
            </a>
            @endif

            @if(!$caseLocked && $canWriteLetter)
            <a href="#letters-compose"
                @click.prevent="openSection('letters-compose')"
                class="btn btn-primary">
                Write letter
            </a>
            @endif

            @if(!$caseLocked && ($case->status ?? '') === 'closed')
            <a href="{{ route('decisions.create', ['case_id' => $case->id]) }}"
                class="btn btn-primary">
                Give decision
            </a>
            @endif

            @include('admin.cases.case-partials.quick-access')
        </div>
    </div>

    @if($caseLocked)
    <div class="mt-4 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50/95 px-4 py-3 text-sm text-amber-900 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 19a7 7 0 110-14 7 7 0 010 14z" />
        </svg>
        <span>Actions are locked because this case is closed and has an active decision.</span>
    </div>
    @endif
</div>
