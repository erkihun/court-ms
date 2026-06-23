{{-- resources/views/admin/decisions/show.blade.php --}} 
<x-admin-layout title="{{ $decision->name ?? __('decisions.show.title') }}"> 
    @section('page_header', $decision->name ?? __('decisions.show.title')) 
    @include('admin.decisions.partials.font-style')

    @push('styles')
        <style>
            .decision-document-shell {
                background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), #fff);
            }

            .decision-document-content {
                color: #111827;
                line-height: 1.85;
                overflow-wrap: anywhere;
                text-align: justify;
                text-justify: inter-word;
            }

            /* Use only the styles the editor saved, not the page's Ethiopic font. */
            .decision-document-content,
            .decision-document-content * {
                font-family: revert;
            }

            .decision-document-content > *:first-child {
                margin-top: 0;
            }

            .decision-document-content > *:last-child {
                margin-bottom: 0;
            }

            .decision-document-content ul,
            .decision-document-content ol {
                margin: 0.75rem 0 1rem 1.5rem;
                padding-left: 1rem;
            }

            .decision-document-content ul {
                list-style: disc;
            }

            .decision-document-content ol {
                list-style: decimal;
            }

            .decision-document-content li {
                margin: 0.35rem 0;
                padding-left: 0.15rem;
            }

            .decision-document-content table {
                width: 100%;
                min-width: 42rem;
                margin: 1rem 0;
                border-collapse: collapse;
                font-size: 0.92rem;
            }

            .decision-document-content th,
            .decision-document-content td {
                border: 1px solid #dbe3ef;
                padding: 0.65rem 0.75rem;
                vertical-align: top;
            }

            .decision-document-content th {
                background: #f1f5f9;
                color: #0f172a;
                font-weight: 700;
            }

            .decision-document-content blockquote {
                margin: 1rem 0;
                border-left: 4px solid #10b981;
                background: #ecfdf5;
                padding: 0.85rem 1rem;
                color: #065f46;
            }

            .decision-document-content img {
                max-width: 100%;
                height: auto;
                border-radius: 0.5rem;
            }
        </style>
    @endpush

    <div class="decision-ethiopic-font w-full space-y-6">
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
                @if($isMiddleJudge && ($decision->status ?? 'draft') !== 'published')
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
                        {{ __('decisions.fields.case_file_number') }}
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
                        {{ __('decisions.fields.applicant_label') }}
                    </p>
                    <p class="font-semibold text-gray-900 mt-1">{{ $decision->applicant_full_name ?: '—' }}</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13V5a3 3 0 00-6 0v8m-2 4h8" />
                        </svg>
                        {{ __('decisions.fields.respondent_label') }}
                    </p>
                    <p class="font-semibold text-gray-900 mt-1">{{ $decision->respondent_full_name ?: '—' }}</p>
                </div>
            </div>

            <!-- Judicial Panel -->
            @php
            $panel = $decision->panel_judges ?? [];
            $panel = is_array($panel) ? array_values($panel) : [];
            $reviewLocked = $decision->status === 'published';
            @endphp
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2M3 20h12M4 4h16v12H4z" />
                        </svg>
                        <h2 class="text-base font-semibold text-gray-900">{{ __('decisions.judges.judicial_panel') }}</h2>
                    </div>
                    <span class="text-xs uppercase tracking-wide text-gray-500">{{ __('decisions.judges.panel') }}</span>
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
                            <div class="text-sm font-medium text-gray-900">{{ __('decisions.judges.judge', ['number' => $i+1]) }}</div>
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
                            {{ __('decisions.judges.vote', ['vote' => $vote ? __('decisions.reviews.' . $vote) : '—']) }}
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
                    {{ $decision->status === 'published' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                {{ $decision->status ? __('decisions.status.' . $decision->status) : '—' }}
            </span>
        </div>

        @php
        $canChangeStatus = function_exists('userHasPermission')
            ? userHasPermission('decision.update')
            : (auth()->user()?->hasPermission('decision.update') ?? false);
        $canApprove = function_exists('userHasPermission')
            ? userHasPermission('decision.approve')
            : (auth()->user()?->hasPermission('decision.approve') ?? false);
        $isPublished = ($decision->status ?? 'draft') === 'published';
        $isApproved = $decision->approved_at !== null;
        @endphp

        <!-- Final Output (generate PDF from a decision template) -->
        <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 6h16M4 6a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2" />
                        </svg>
                        {{ __('decision_templates.output.heading') }}
                    </h2>
                    <p class="mt-1 text-xs text-gray-600">{{ __('decision_templates.output.choose_template') }}</p>

                    {{-- Approved badge (gate to publishing + seals the PDF) --}}
                    @if($isApproved)
                    <p class="mt-3 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('decisions.approved_badge', [
                            'user' => $decision->approver?->name ?? '—',
                            'date' => \App\Support\EthiopianDate::format($decision->approved_at),
                        ]) }}
                    </p>
                    @endif

                    @if($isPublished)
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <p class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('decisions.published_locked') }}
                        </p>

                        {{-- Approve button: only after publishing, before approval --}}
                        @if($canApprove && !$isApproved)
                        <div x-data="{ approveOpen: false }">
                            <form method="POST" action="{{ route('decisions.approve', $decision) }}" x-ref="approveForm" class="hidden">
                                @csrf
                                @method('PATCH')
                            </form>
                            <button type="button" @click="approveOpen = true"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('decisions.approve') }}
                            </button>

                            <template x-teleport="body">
                                <div x-show="approveOpen" x-cloak
                                    class="fixed inset-0 z-[1100] flex items-center justify-center p-4"
                                    x-on:keydown.escape.window="approveOpen = false" role="dialog" aria-modal="true">
                                    <div x-show="approveOpen" x-transition.opacity
                                        class="absolute inset-0 bg-gray-900/50" @click="approveOpen = false"></div>
                                    <div x-show="approveOpen"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                                        class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                                        <div class="flex items-start gap-3 p-5">
                                            <span class="mt-0.5 inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <h3 class="text-base font-semibold text-gray-900">{{ __('decisions.approve') }}</h3>
                                                <p class="mt-1 text-sm text-gray-600">{{ __('decisions.approve_confirm') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
                                            <button type="button" @click="approveOpen = false"
                                                class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                {{ __('decisions.confirm_cancel') }}
                                            </button>
                                            <button type="button" @click="approveOpen = false; $refs.approveForm.submit()"
                                                class="px-3 py-2 rounded-lg bg-emerald-600 text-sm font-medium text-white hover:bg-emerald-700">
                                                {{ __('decisions.approve') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @endif
                    </div>
                    @elseif($canChangeStatus)
                    <div x-data="{
                            confirmOpen: false,
                            selected: '{{ $decision->status ?? 'draft' }}',
                            get isPublish() { return this.selected === 'published'; },
                            get message() {
                                return this.isPublish
                                    ? @js(__('decisions.publish_confirm'))
                                    : @js(__('decisions.status_change_confirm'));
                            }
                        }">
                        <form method="POST" action="{{ route('decisions.status', $decision) }}"
                            x-ref="statusForm" class="mt-3 flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <span class="text-xs font-medium text-gray-600">{{ __('decisions.fields.status') }}:</span>
                            <select name="status" x-model="selected"
                                class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                <option value="draft" @selected(($decision->status ?? 'draft') === 'draft')>
                                    {{ __('decisions.status.draft') }}
                                </option>
                                <option value="published" @selected(($decision->status ?? 'draft') === 'published')>
                                    {{ __('decisions.status.published') }}
                                </option>
                            </select>
                            <button type="button" @click="confirmOpen = true"
                                class="px-3 py-2 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-900">
                                {{ __('decisions.status_change') }}
                            </button>
                        </form>

                        {{-- Custom confirmation modal --}}
                        <template x-teleport="body">
                            <div x-show="confirmOpen" x-cloak
                                class="fixed inset-0 z-[1100] flex items-center justify-center p-4"
                                x-on:keydown.escape.window="confirmOpen = false" role="dialog" aria-modal="true">
                                {{-- Backdrop --}}
                                <div x-show="confirmOpen" x-transition.opacity
                                    class="absolute inset-0 bg-gray-900/50" @click="confirmOpen = false"></div>

                                {{-- Dialog --}}
                                <div x-show="confirmOpen"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                                    class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                                    <div class="flex items-start gap-3 p-5">
                                        <span class="mt-0.5 inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
                                            :class="isPublish ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-semibold text-gray-900">{{ __('decisions.status_change') }}</h3>
                                            <p class="mt-1 text-sm text-gray-600" x-text="message"></p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
                                        <button type="button" @click="confirmOpen = false"
                                            class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-100">
                                            {{ __('decisions.confirm_cancel') }}
                                        </button>
                                        <button type="button" @click="confirmOpen = false; $refs.statusForm.submit()"
                                            class="px-3 py-2 rounded-lg text-sm font-medium text-white"
                                            :class="isPublish ? 'bg-amber-600 hover:bg-amber-700' : 'bg-gray-800 hover:bg-gray-900'"
                                            x-text="isPublish ? @js(__('decisions.publish')) : @js(__('decisions.status_change'))">
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    @endif
                </div>
                <form method="GET" action="{{ route('decisions.output', $decision) }}" target="_blank"
                    class="flex flex-wrap items-center gap-2">
                    <select name="template_id"
                        class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <option value="">{{ __('decision_templates.output.default_layout') }}</option>
                        @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" name="mode" value="stream"
                        class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                        {{ __('decision_templates.output.generate') }}
                    </button>
                    <button type="submit" name="mode" value="download"
                        class="px-3 py-2 rounded-lg border border-indigo-300 bg-white text-indigo-700 text-sm font-medium hover:bg-indigo-50">
                        {{ __('decision_templates.output.download') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Decision Content + Reviews (side by side) -->
        <div class="grid gap-6 lg:grid-cols-3 items-start">
        <!-- Decision Content -->
        <div class="lg:col-span-2 overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-emerald-100 bg-emerald-50/70 px-5 py-4">
                <h2 class="flex items-center gap-2 text-base font-bold text-gray-950">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                    </svg>
                    {{ __('decisions.fields.decision_content') }}
                </h2>
                <span class="hidden rounded-full border border-emerald-200 bg-white px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700 sm:inline-flex">
                    {{ __('decisions.status.' . ($decision->status ?? 'draft')) }}
                </span>
            </div>
            <div class="decision-document-shell px-4 py-5 sm:px-6">
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white p-4 shadow-inner sm:p-6">
                    <div class="decision-document-content">
                        {!! $decision->decision_content !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Decision Reviews -->
        <div class="lg:col-span-1 border border-gray-200 rounded-lg p-4 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">{{ __('decisions.reviews.heading') }}</h2>
                </div>
                <span class="text-xs uppercase tracking-wide text-gray-500">{{ __('decisions.reviews.legend') }}</span>
            </div>
            @php
            $reviews = $decision->reviews ?? collect();
            $canReview = function_exists('userHasPermission')
            ? userHasPermission('decision.update')
            : (auth()->user()?->hasPermission('decision.update') ?? false);
            @endphp

                @if($reviews->count() === 0)
                <p class="text-sm text-gray-600">{{ __('decisions.reviews.none') }}</p>
                @else
                <div class="flex flex-col gap-3">
                    @foreach($reviews as $review)
                    @php
                $isOwner = auth()->id() === $review->reviewer_id;
                @endphp
                <div class="w-full border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs text-gray-500">{{ __('decisions.reviews.reviewer') }}</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $review->reviewer?->name ?? '—' }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wide
                                {{ $review->outcome === 'approve' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' :
                                   ($review->outcome === 'reject' ? 'bg-red-100 text-red-700 border border-red-200' :
                                   ($review->outcome === 'improve' ? 'bg-amber-100 text-amber-700 border border-amber-200' :
                                   'bg-gray-100 text-gray-700 border border-gray-200')) }}">
                            {{ __('decisions.reviews.' . ($review->outcome ?? 'pending')) }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-700">
                        {{ $review->review_note ?: '—' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ \App\Support\EthiopianDate::smartRelative($review->updated_at) }}
                    </div>

                    @if($isOwner && !$reviewLocked)
                    <div class="pt-2 border-t border-gray-200 flex items-center gap-2">
                        <a href="{{ route('decisions.reviews.edit', [$decision, $review]) }}"
                            class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs">
                            {{ __('decisions.reviews.update') }}
                        </a>
                        <form method="POST" action="{{ route('decisions.reviews.destroy', [$decision, $review]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs"
                                onclick="return confirm(@js(__('decisions.reviews.delete_confirm')))">
                                {{ __('decisions.reviews.delete') }}
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
                <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ __('decisions.reviews.add') }}</h3>
                <form method="POST" action="{{ route('decisions.reviews.store', $decision) }}" class="space-y-3"
                    x-data="{
                        outcome: '{{ old('outcome', 'approve') }}',
                        get needsNote() { return this.outcome === 'reject' || this.outcome === 'improve'; },
                        get notePlaceholder() {
                            return this.outcome === 'improve'
                                ? @js(__('decisions.reviews.note_placeholder_improve'))
                                : @js(__('decisions.reviews.note_placeholder_reject'));
                        }
                    }">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('decisions.reviews.outcome') }}</label>
                        <select name="outcome" x-model="outcome"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="approve">{{ __('decisions.reviews.approve') }}</option>
                            <option value="reject">{{ __('decisions.reviews.reject') }}</option>
                            <option value="improve">{{ __('decisions.reviews.improve') }}</option>
                        </select>
                        @error('outcome') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="needsNote" x-cloak>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('decisions.reviews.review_note') }}
                            <span class="text-red-500" x-show="needsNote">*</span>
                        </label>
                        <textarea name="review_note" rows="3" x-bind:required="needsNote"
                            x-bind:placeholder="notePlaceholder"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">{{ old('review_note') }}</textarea>
                        @error('review_note') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end">
                        <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                            {{ __('decisions.reviews.submit') }}
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
        </div><!-- /Decision Content + Reviews grid -->

    </div>
    </div>

</x-admin-layout>

