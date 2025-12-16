{{-- resources/views/cases/show.blade.php --}}
<x-admin-layout title="{{ __('cases.case_details') }}">
    @section('page_header', __('cases.case_details'))

    @php
    // Safer permission checks (works even if user() is null or helper exists)
    $canEditStatus = function_exists('userHasPermission')
    ? userHasPermission('cases.edit')
    : (auth()->user()?->hasPermission('cases.edit') ?? false);

    $canAssign = function_exists('userHasPermission')
    ? (
    userHasPermission('cases.assign.team')
    || userHasPermission('cases.assign.member')
    || userHasPermission('cases.assign')
    )
    : (
    (auth()->user()?->hasPermission('cases.assign.team') ?? false)
    || (auth()->user()?->hasPermission('cases.assign.member') ?? false)
    || (auth()->user()?->hasPermission('cases.assign') ?? false)
    );

    $currentStatus = $case->status ?? 'pending';
    $reviewStatus = $case->review_status ?? 'accepted';
    $reviewNote = $case->review_note ?? null;

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
    'returned' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
    'rejected' => 'bg-rose-100 text-rose-800 border border-rose-300',
    default => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
    };
    $reviewLabel = fn (string $s) => match ($s) {
    'awaiting_review' => 'Awaiting approval',
    'returned' => 'Needs correction',
    'rejected' => 'Rejected',
    default => 'Approved',
    };

    $letterPanelOpen = $errors->has('template_id') || $errors->has('recipient_name') || $errors->has('body');
    $defaultSection = $letterPanelOpen ? 'letters-compose' : 'case-summary';
    $canWriteLetter = function_exists('userHasPermission')
    ? userHasPermission('letters.create')
    : (auth()->user()?->hasPermission('letters.create') ?? false);
    $canManageBench = function_exists('userHasPermission')
    ? userHasPermission('bench-notes.manage')
    : (auth()->user()?->hasPermission('bench-notes.manage') ?? false);
    $canViewHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.view')
    : (auth()->user()?->hasPermission('hearing.view') ?? true);
    $canCreateHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.create')
    : (auth()->user()?->hasPermission('hearing.create') ?? false);
    $canUpdateHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.update')
    : (auth()->user()?->hasPermission('hearing.update') ?? false);
    $canDeleteHearings = function_exists('userHasPermission')
    ? userHasPermission('hearing.delete')
    : (auth()->user()?->hasPermission('hearing.delete') ?? false);
    $canViewFiles = function_exists('userHasPermission')
    ? userHasPermission('file.view')
    : (auth()->user()?->hasPermission('file.view') ?? true);
    $canCreateFiles = function_exists('userHasPermission')
    ? userHasPermission('file.create')
    : (auth()->user()?->hasPermission('file.create') ?? false);
    $canUpdateFiles = function_exists('userHasPermission')
    ? userHasPermission('file.update')
    : (auth()->user()?->hasPermission('file.update') ?? false);
    $canDeleteFiles = function_exists('userHasPermission')
    ? userHasPermission('file.delete')
    : (auth()->user()?->hasPermission('file.delete') ?? false);
    $canCreateMessage = function_exists('userHasPermission')
    ? userHasPermission('message.create')
    : (auth()->user()?->hasPermission('message.create') ?? false);
    $canViewLetters = function_exists('userHasPermission')
    ? userHasPermission('letters.view')
    : (auth()->user()?->hasPermission('letters.view') ?? false);
    $selectedInlineTemplate = null;
    if(old('template_id')) {
    $selectedInlineTemplate = $letterTemplates->firstWhere('id', old('template_id'));
    }

    $letterFieldsDisabled = false;
    $inlineTemplatesData = $letterTemplates->mapWithKeys(function($tpl) {
    $placeholders = $tpl->placeholders ?? [];
    if (is_string($placeholders)) {
    $decoded = json_decode($placeholders, true);
    $placeholders = $decoded ?: [];
    }
    return [
    $tpl->id => [
    'id' => $tpl->id,
    'title' => $tpl->title,
    'body' => $tpl->body,
    'subject' => $tpl->title,
    'placeholders' => $placeholders,
    'category' => $tpl->category,
    ],
    ];
    });

    // Lock actions if case is closed AND has an active decision
    $hasActiveDecision = false;
    try {
    $hasActiveDecision = \App\Models\Decision::where('court_case_id', $case->id)->where('status', 'active')->exists();
    } catch (\Throwable $e) {
    $hasActiveDecision = false;
    }
    $caseLocked = (($case->status ?? '') === 'closed') && $hasActiveDecision;

    // Lock actions if case is closed AND has an active decision
    $hasActiveDecision = false;
    try {
    $hasActiveDecision = \App\Models\Decision::where('court_case_id', $case->id)->where('status', 'active')->exists();
    } catch (\Throwable $e) {
    $hasActiveDecision = false;
    }
    $caseLocked = (($case->status ?? '') === 'closed') && $hasActiveDecision;

    $hearingDateKeys = ($hearings ?? collect())
    ->map(function ($h) {
    try {
    return \Illuminate\Support\Carbon::parse($h->hearing_at)->format('Y-m-d');
    } catch (\Throwable $e) {
    return $h->hearing_at ? substr((string) $h->hearing_at, 0, 10) : null;
    }
    })
    ->filter()
    ->unique()
    ->values()
    ->toArray();
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

        /* Modern calendar popup layering */
        .datepicker-popup {
            z-index: 9999 !important;
        }

        /* Hide default title and "Select a date" footer from modern calendar */
        .modern-calendar .calendar-header .calendar-title,
        .modern-calendar .selected-date-display {
            display: none !important;
        }

        /* Hearing calendar event markers */
        #hearings-calendar .day.has-event {
            position: relative;
        }

        #hearings-calendar .day.has-event .event-dot {
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #2563eb;
            box-shadow: 0 0 0 2px #fff;
        }

        /* Limit inline calendar size for better layout */
        #hearings-calendar .modern-calendar {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        /* Improved section transitions */
        [x-cloak] {
            display: none !important;
        }

        /* Enhanced quick access */
        .quick-access-btn {
            transition: all 0.2s ease;
            border-width: 1.5px;
        }

        .quick-access-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Main content area improvements */
        .main-content-section {
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    {{-- Modern Ethiopian calendar assets --}}
    <link rel="stylesheet" href="{{ asset('vendor/modern-ethiopian-calendar/css/modern-calendar.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/modern-ethiopian-calendar/css/datepicker.css') }}">

    @endpush

    @push('scripts')
    <script>
        (function() {
            const templates = @json($inlineTemplatesData ?? []);
            const selectEl = document.getElementById('inline-template-select');
            const loadBtn = document.getElementById('inline-template-load');
            const hiddenTemplate = document.getElementById('inline-template-hidden');
            const subjectInput = document.querySelector('#write-letter-panel input[name="subject"]');
            const refBlock = document.getElementById('inline-reference-block');
            const refValue = document.getElementById('inline-reference-value');
            const placeholderBlock = document.getElementById('inline-placeholders');
            const placeholderText = document.getElementById('inline-placeholders-text');
            const summaryBlock = document.getElementById('inline-template-summary');
            const summaryTitle = document.getElementById('inline-template-title');
            const summaryCategory = document.getElementById('inline-template-category');
            const summaryExcerpt = document.getElementById('inline-template-excerpt');
            const categoryFallback = @json(__('letters.form.category_fallback'));

            const getTpl = (id) => templates[id] || templates[String(id)] || templates[Number(id)];

            const stripHtml = (html) => {
                const div = document.createElement('div');
                div.innerHTML = html || '';
                return div.textContent || div.innerText || '';
            };

            const buildReference = (tpl) => {
                if (!tpl || tpl.reference_sequence === null || tpl.reference_sequence === undefined) return null;
                const next = (parseInt(tpl.reference_sequence, 10) || 0) + 1;
                const seq = String(next).padStart(4, '0');
                return [tpl.subject_prefix || '', seq].filter(Boolean).join('/');
            };

            const renderMeta = (tpl) => {
                if (!tpl) {
                    placeholderBlock?.classList.add('hidden');
                    summaryBlock?.classList.add('hidden');
                    refBlock?.classList.add('hidden');
                    return;
                }
                const placeholders = Array.isArray(tpl.placeholders) ? tpl.placeholders : [];
                if (placeholderBlock) {
                    if (placeholders.length) {
                        placeholderText.textContent = placeholders.join(', ');
                        placeholderBlock.classList.remove('hidden');
                    } else {
                        placeholderBlock.classList.add('hidden');
                    }
                }
                if (summaryBlock) {
                    summaryBlock.classList.remove('hidden');
                    if (summaryTitle) summaryTitle.textContent = tpl.title || '';
                    if (summaryCategory) summaryCategory.textContent = tpl.category || categoryFallback;
                    if (summaryExcerpt) {
                        const text = stripHtml(tpl.body || '');
                        summaryExcerpt.textContent = text.length > 120 ? text.slice(0, 120) + '...' : text;
                    }
                }
                if (refBlock && refValue) {
                    const ref = buildReference(tpl);
                    if (ref) {
                        refValue.value = ref;
                        refBlock.classList.remove('hidden');
                    } else {
                        refBlock.classList.add('hidden');
                    }
                }
            };

            const applyTemplate = (tpl) => {
                if (!tpl) return;
                if (subjectInput) subjectInput.value = tpl.subject || tpl.title || '';
                if (hiddenTemplate) hiddenTemplate.value = tpl.id || '';

                const body = tpl.body || '';
                const editor = tinymce.get('letter-body-editor');
                if (editor) {
                    editor.setContent(body);
                    editor.focus();
                } else {
                    const textarea = document.getElementById('letter-body-editor');
                    if (textarea) textarea.value = body;
                }
                renderMeta(tpl);
            };

            const handleLoad = () => {
                const id = selectEl?.value;
                if (!id) {
                    alert(@json(__('letters.form.select_placeholder')));
                    return;
                }
                const tpl = getTpl(id);
                applyTemplate(tpl);
            };

            selectEl?.addEventListener('change', handleLoad);
            loadBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                handleLoad();
            });

            if (selectEl?.value) {
                applyTemplate(getTpl(selectEl.value));
            }
        })();
    </script>
    @endpush

    <div x-data="{
            activeSection: {{ json_encode($defaultSection) }},
            init() {
                const hashSection = window.location.hash ? window.location.hash.substring(1) : null;
                if (hashSection) {
                    this.activeSection = hashSection;
                }
                const syncSectionFromHash = () => {
                    if (window.location.hash) {
                        this.activeSection = window.location.hash.substring(1);
                    }
                };
                window.addEventListener('popstate', syncSectionFromHash);
                window.addEventListener('hashchange', syncSectionFromHash);
            },
            openSection(section) {
                this.activeSection = section;
                history.pushState(null, null, '#' + section);
                this.$nextTick(() => {
                    const target = document.getElementById(section);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        }"
        x-on:open-section.window="openSection($event.detail.section)"
        x-init="init()">

        {{-- Header Card --}}
        <div class="mb-6 p-4 rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
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

                <div class="flex flex-wrap items-center gap-2">
                    @if(in_array($reviewStatus, ['awaiting_review','returned']) && $canReview)
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-sm font-medium text-white shadow-sm hover:shadow transition-all duration-200"
                            onclick="submitReviewDecision('accept')">Accept</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-sm font-medium text-white shadow-sm hover:shadow transition-all duration-200"
                            onclick="openReviewModal('return')">Return</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-sm font-medium text-white shadow-sm hover:shadow transition-all duration-200"
                            onclick="openReviewModal('reject')">Reject</button>
                    </div>
                    @endif

                    @if(!$caseLocked && $canAssign)
                    <a href="{{ route('cases.assign.form', $case->id) }}"
                        class="px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ __('cases.assign_change') }}
                    </a>
                    @endif

                    @if(!$caseLocked && $canManageBench)
                    <a href="{{ route('bench-notes.index', ['case_id' => $case->id]) }}"
                        class="px-4 py-2.5 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4M7 16h8M11 4h2a2 2 0 012 2v14h-4V6a2 2 0 012-2h2" />
                        </svg>
                        Bench note
                    </a>
                    @endif

                    @if(!$caseLocked && $canWriteLetter)
                    <a href="#letters-compose"
                        @click.prevent="openSection('letters-compose')"
                        class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9M12 4h9m-9 8h9M5 6h.01M5 12h.01M5 18h.01" />
                        </svg>
                        Write letter
                    </a>
                    @endif

                    @if(!$caseLocked && ($case->status ?? '') === 'closed')
                    <a href="{{ route('decisions.create', ['case_id' => $case->id]) }}"
                        class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Give decision
                    </a>
                    @endif

                    <a href="{{ route('cases.index') }}"
                        class="px-4 py-2.5 rounded-lg bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 border border-gray-300 transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('cases.back') }}
                    </a>

                    <button onclick="window.print()"
                        class="px-4 py-2.5 rounded-lg bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 border border-gray-300 transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        {{ __('cases.print') }}
                    </button>
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

        {{-- Quick Access Section --}}
        <div class="p-4 rounded-2xl border border-gray-200 bg-white shadow-sm mb-6">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-sm font-semibold text-gray-900">Quick Access</span>
                </div>
                <span class="text-xs text-gray-500">Jump directly to frequently used sections</span>
            </div>

            <div class="flex flex-wrap gap-3">
                @if($canViewFiles || $canCreateFiles || $canUpdateFiles || $canDeleteFiles)
                <button @click="openSection('uploaded-files')"
                    class="quick-access-btn inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-blue-200 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('cases.navigation.uploaded_files') }}
                </button>
                @endif

                @if($canViewHearings || $canCreateHearings || $canUpdateHearings || $canDeleteHearings)
                <button @click="openSection('hearings')"
                    class="quick-access-btn inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-purple-200 text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 hover:border-purple-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('cases.navigation.hearings') }}
                </button>
                @endif

                <button @click="openSection('messages')"
                    class="quick-access-btn inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-green-200 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 hover:border-green-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    {{ __('cases.navigation.messages') }}
                </button>

                <button @click="openSection('case-details')"
                    class="quick-access-btn inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-amber-200 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 hover:border-amber-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('cases.navigation.case_details') }}
                </button>

                <button @click="openSection('letters')"
                    class="quick-access-btn inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-indigo-200 text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4H8a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V6a2 2 0 00-2-2zM8 4l4 4 4-4" />
                    </svg>
                    Letters
                </button>
            </div>
        </div>

        {{-- Modal for return/reject note --}}
        <div id="review-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-30">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3" id="review-modal-title">Review decision</h3>
                <form method="POST" action="{{ route('cases.review.update', $case->id) }}" id="review-form" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="decision" id="review-decision" value="">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason / note</label>
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
        <div class="p-4 rounded-2xl border border-gray-200 bg-white shadow-sm mb-6">
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
                    <select name="status" class="w-full px-4 py-2.5 rounded-lg bg-white text-gray-900 border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150">
                        @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($currentStatus===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('cases.status.note_to_timeline') }}</label>
                    <input name="note" placeholder="{{ __('cases.status.add_note_placeholder') }}"
                        class="w-full px-4 py-2.5 rounded-lg bg-white text-gray-900 border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150">
                    @error('note') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <button class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('cases.status.update_status') }}
                    </button>
                </div>
            </form>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="lg:col-span-3">
            <div class="p-4 rounded-2xl border border-blue-800 bg-blue-900 text-white shadow-sm sticky top-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg font-semibold">{{ __('cases.navigation.title') }}</span>
                </div>

                <nav class="space-y-3">
                    <ul class="space-y-1">
                        <li>
                            <button type="button" @click="openSection('case-summary')"
                                :class="activeSection === 'case-summary'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <span>{{ __('cases.navigation.case_summary') }}</span>
                            </button>
                        </li>

                        <li>
                            <button type="button" @click="openSection('case-details')"
                                :class="activeSection === 'case-details'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 8h10M5 5h14v14H5z" />
                                </svg>
                                <span>{{ __('cases.navigation.case_details') }}</span>
                            </button>
                        </li>

                        <li>
                            <button type="button" @click="openSection('letters')"
                                :class="activeSection === 'letters'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                                </svg>
                                <span>Letters</span>
                            </button>
                        </li>

                        <li>
                            <button type="button" @click="openSection('audits')"
                                :class="activeSection === 'audits'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m0 0V9m0 6h6m-6-4h6m2 8H7a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z" />
                                </svg>
                                <span>Case Audits</span>
                            </button>
                        </li>
                    </ul>

                    {{-- Quick Access Sections in Sidebar --}}
                    <div class="pt-4 mt-2 border-t border-blue-700/60">
                        <p class="text-[11px] font-semibold text-blue-200 uppercase tracking-[0.08em] mb-2">Quick sections</p>
                        <div class="space-y-1">
                            @if($canViewFiles || $canCreateFiles || $canUpdateFiles || $canDeleteFiles)
                            <button type="button" @click="openSection('uploaded-files')"
                                :class="activeSection === 'uploaded-files'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6M5 5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-.586-1.414l-3-3A2 2 0 0015.586 4H5z" />
                                </svg>
                                {{ __('cases.navigation.uploaded_files') }}
                            </button>
                            @endif

                            @if($canViewHearings || $canCreateHearings || $canUpdateHearings || $canDeleteHearings)
                            <button type="button" @click="openSection('hearings')"
                                :class="activeSection === 'hearings'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ __('cases.navigation.hearings') }}
                            </button>
                            @endif

                            <button type="button" @click="openSection('messages')"
                                :class="activeSection === 'messages'
                                    ? 'bg-white/10 text-white shadow-sm'
                                    : 'text-blue-100 hover:bg-white/5 hover:text-white'"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium leading-5 transition-all duration-150 text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m5 8H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('cases.navigation.messages') }}
                            </button>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
            {{-- Main Content --}}
            <div class="lg:col-span-9 space-y-6">
                {{-- Case Summary --}}
                <section id="case-summary" x-show="activeSection === 'case-summary'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-6 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-6">
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
                                    {{ $case->filing_date ? \App\Support\EthiopianDate::format($case->filing_date) : '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.first_hearing') }}</div>
                                <div class="text-gray-900 font-medium">
                                    {{ $case->first_hearing_date ? \App\Support\EthiopianDate::format($case->first_hearing_date) : '—' }}
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
                                    {{ $case->created_at ? \App\Support\EthiopianDate::format($case->created_at, withTime: true, timeFormat: 'h:i A') : '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.updated') }}</div>
                                <div class="text-gray-900 font-medium">
                                    {{ $case->updated_at ? \App\Support\EthiopianDate::format($case->updated_at, withTime: true, timeFormat: 'h:i A') : '—' }}
                                </div>
                            </div>
                            @if($case->assigned_at)
                            <div class="sm:col-span-2">
                                <div class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">{{ __('cases.summary.assigned_at') }}</div>
                                <div class="text-gray-900 font-medium">
                                    {{ \App\Support\EthiopianDate::format($case->assigned_at, withTime: true, timeFormat: 'h:i A') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Case Audits --}}
                <section id="audits" x-show="activeSection === 'audits'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-6 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-3">
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
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">When</th>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">Action</th>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">Actor</th>
                                    <th class="p-3 text-left font-medium border-b border-gray-200">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($audits as $a)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-gray-700 whitespace-nowrap">
                                        {{ \App\Support\EthiopianDate::format($a->created_at, withTime: true) }}
                                    </td>
                                    <td class="p-3 text-gray-900 font-medium">{{ str_replace('_',' ', ucfirst($a->action)) }}</td>
                                    <td class="p-3 text-gray-700 text-xs">
                                        @if(!empty($a->actor_name))
                                        {{ $a->actor_name }} @if($a->actor_id)(#{{ $a->actor_id }})@endif
                                        @elseif(!empty($a->actor_id))
                                        {{ $a->actor_type ?? 'system' }} (#{{ $a->actor_id }})
                                        @else
                                        {{ $a->actor_type ?? 'system' }}
                                        @endif
                                    </td>
                                    <td class="p-3 text-gray-700 text-xs">
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
                @php
                $reliefHtmlOut = null;
                if (!empty($case->relief_requested_html ?? null)) {
                $reliefHtmlOut = clean($case->relief_requested_html, 'cases');
                } elseif (!empty($case->relief_requested)) {
                $reliefHtmlOut = clean($case->relief_requested, 'cases');
                }
                @endphp
                <section id="case-details" x-show="activeSection === 'case-details'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section rounded-2xl border border-gray-200 bg-white shadow-sm p-6 space-y-6">
                    <h3 class="text-xl font-semibold text-gray-900">Case Details Overview</h3>

                    <div class="grid md:grid-cols-2 gap-4 border border-gray-100 rounded-xl p-4 bg-gray-50">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Applicant</p>
                            <dl class="text-sm text-gray-700 space-y-1">
                                <div>
                                    <dt class="text-xs text-gray-500">Name</dt>
                                    <dd class="font-semibold text-gray-900">{{ $case->applicant_name ?? '&mdash;' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Address</dt>
                                    <dd>{{ $case->applicant_address ?? '&mdash;' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Email</dt>
                                    <dd>{{ $case->applicant_email ?? '&mdash;' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('cases.respondent_defendant') }}</p>
                            <dl class="text-sm text-gray-700 space-y-1">
                                <div>
                                    <dt class="text-xs text-gray-500">Name</dt>
                                    <dd class="font-semibold text-gray-900">{{ $case->respondent_name ?? '&mdash;' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Address</dt>
                                    <dd>{{ $case->respondent_address ?? '&mdash;' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('cases.details.case_details') }}</p>
                            <div class="cms-output mt-2 text-[14px] leading-relaxed">
                                {!! $case->description_html ?? clean($case->description ?? __('cases.details.no_details'), 'cases') !!}
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('cases.details.relief_requested') }}</p>
                            <div class="cms-output mt-2 text-[14px] leading-relaxed">
                                {!! $reliefHtmlOut ?? __('cases.details.no_relief_specified') !!}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('cases.documents.submitted_documents') }}
                            </h4>
                            <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($docs ?? collect())->count() }} {{ __('cases.documents.items') }}</span>
                        </div>
                        @if(($docs ?? collect())->isEmpty())
                        <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                            {{ __('cases.documents.no_documents') }}
                        </div>
                        @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Document</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Type</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Uploaded</th>
                                        <th class="px-3 py-2 text-right font-medium border-b border-gray-200">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($docs as $d)
                                    @php
                                    $filePath = $d->file_path ?? $d->path ?? null;
                                    $docTitle = $d->title ?? ($d->label ?? ($filePath ? basename($filePath) : __('cases.documents.document')));
                                    $fileTime = !empty($d->created_at) ? \App\Support\EthiopianDate::format($d->created_at, withTime: true) : null;
                                    $fileSize = isset($d->size) ? number_format(max(0, (int) $d->size) / 1024, 1) : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 align-top">
                                            <div class="font-medium text-gray-900">{{ $docTitle }}</div>
                                            @if(!empty($d->description))
                                            <div class="text-xs text-gray-600 mt-1 tiny-content">
                                                {!! clean($d->description, 'cases') !!}
                                            </div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $d->mime ?? __('cases.documents.document') }}</td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ $fileTime ?? '&mdash;' }} @if($fileSize)<span class="text-gray-500 text-xs">({{ $fileSize }} KB)</span>@endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <a @if($filePath) href="{{ route('cases.documents.view', [$case->id, $d->id]) }}" target="_blank" @endif
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors duration-150 {{ $filePath ? 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100' : 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed' }}"
                                                @unless($filePath) aria-disabled="true" @endunless>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ __('cases.documents.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 14 0z" />
                                </svg>
                                {{ __('cases.witnesses_section.title') }}
                            </h4>
                            <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">
                                {{ ($witnesses ?? collect())->count() }} {{ __('cases.witnesses_section.total') }}
                            </span>
                        </div>
                        @if(($witnesses ?? collect())->isEmpty())
                        <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                            {{ __('cases.witnesses_section.no_witnesses') }}
                        </div>
                        @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.labels.name') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.labels.phone') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.labels.email') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.labels.address') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($witnesses as $w)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $w->full_name }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $w->phone ?? '&mdash;' }}</td>
                                        <td class="px-3 py-2">
                                            @if(!empty($w->email))
                                            <a href="mailto:{{ $w->email }}" class="text-blue-700 hover:underline">{{ $w->email }}</a>
                                            @else
                                            <span class="text-gray-400">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $w->address ?? '&mdash;' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </section>

                {{-- Letters Section --}}
                @php
                $templates = $letterTemplates ?? collect();
                $selectedTemplate = $selectedInlineTemplate ?? null;
                $recipientName = old('recipient_name', '');
                $recipientTitle = old('recipient_title', '');
                $recipientCompany = old('recipient_company', '');
                $cc = old('cc', '');
                $subject = old('subject', '');
                $body = old('body', '');
                $sendToApplicant = filter_var(old('send_to_applicant', '1'), FILTER_VALIDATE_BOOLEAN);
                $sendToRespondent = filter_var(old('send_to_respondent', '1'), FILTER_VALIDATE_BOOLEAN);
                $caseNumber = $case->case_number ?? '';
                $letterCollection = ($letters ?? collect())->map(function ($item) {
                return (object) $item;
                });
                $letterStatusClasses = [
                'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                'returned' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                ];
                @endphp
                <section id="letters"
                    x-cloak
                    x-show="activeSection === 'letters'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="p-6 space-y-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-6M9 17l3-3-3-3M14 7h7" />
                                    </svg>
                                    Letters
                                </h3>
                                <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">
                                    {{ $letterCollection->count() }} entries
                                </span>
                            </div>
                            @if($letterCollection->isEmpty())
                            <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                                No letters have been logged yet.
                            </div>
                            @else
                            <div class="overflow-x-auto rounded-lg border border-gray-100">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50 text-gray-600">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Reference</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Subject</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Template</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Status</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Date</th>
                                            <th class="px-3 py-2 text-left font-medium border-b border-gray-200">Author</th>
                                            <th class="px-3 py-2 text-right font-medium border-b border-gray-200">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($letterCollection as $letter)
                                        @php
                                        $status = strtolower($letter->approval_status ?? 'pending');
                                        $statusClass = $letterStatusClasses[$status] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                                        try {
                                        $letterDate = \App\Support\EthiopianDate::format($letter->created_at, withTime: true);
                                        } catch (\Throwable $e) {
                                        $letterDate = $letter->created_at ?? null;
                                        }
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $letter->reference_number ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letter->subject ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letter->template_title ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">
                                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letterDate ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $letter->author_name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                @if($canViewLetters)
                                                <a href="{{ route('letters.show', $letter->id) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                                                    {{ __('cases.documents.view') }}
                                                </a>
                                                @else
                                                <span class="text-gray-400 text-xs">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Write Letter (Compose) Section --}}
                <section id="letters-compose"
                    x-cloak
                    x-show="activeSection === 'letters-compose'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="p-6 space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4h10M11 8h10M4 16h16M4 12h16M4 8h4M4 4h4" />
                                </svg>
                                Write letter
                            </h3>

                            <button type="button"
                                @click="openSection('letters')"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Back to list
                            </button>
                        </div>

                        <div id="write-letter-panel" class="max-w-5xl mx-auto space-y-6">
                            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('letters.form.select_template') }}</h2>
                                <p class="text-sm text-gray-500 mb-4">{{ __('letters.description.compose') }}</p>

                                <div class="flex flex-col md:flex-row gap-3">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.template_label') }}</label>
                                        <select name="template_id" id="inline-template-select" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                                            <option value="">{{ __('letters.form.select_placeholder') }}</option>
                                            @foreach($templates as $template)
                                            <option value="{{ $template->id }}" @selected(optional($selectedTemplate)->id === $template->id)>
                                                {{ $template->title }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" id="inline-template-load" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 bg-white">
                                            {{ __('letters.actions.load') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                                <form method="POST" action="{{ route('letters.store') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="template_id" id="inline-template-hidden" value="{{ optional($selectedTemplate)->id }}">

                                    @if(!$selectedTemplate)
                                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                                        {{ __('letters.form.template_notice') }}
                                    </div>
                                    @endif

                                    @if($errors->any())
                                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {{ __('letters.form.validation_notice') }}
                                    </div>
                                    @endif

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.recipient_name') }}<span class="text-red-500">*</span></label>
                                            <input type="text" name="recipient_name" value="{{ $recipientName }}"
                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" required>
                                            @error('recipient_name')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.recipient_title') }}</label>
                                            <input type="text" name="recipient_title" value="{{ $recipientTitle }}"
                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                                            @error('recipient_title')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.recipient_company') }}</label>
                                            <input type="text" name="recipient_company" value="{{ $recipientCompany }}"
                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                                            <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.recipient_company_hint') }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.case_number') }}</label>
                                            <input type="text" name="case_number" value="{{ old('case_number', $caseNumber) }}"
                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 bg-gray-100 text-gray-700"
                                                placeholder="{{ __('letters.form.case_number_placeholder') }}" readonly>
                                            <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.case_number_help') }}</p>
                                            @error('case_number')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.cc') }}</label>
                                            <input type="text" name="cc" value="{{ $cc }}"
                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="{{ __('letters.form.cc_placeholder') }}">
                                            <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.cc_hint') }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.delivery_label') }}</label>
                                            <div class="mt-2 flex flex-wrap items-center gap-4">
                                                <input type="hidden" name="send_to_applicant" value="0">
                                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="checkbox" name="send_to_applicant" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                        @checked($sendToApplicant)>
                                                    <span>{{ __('letters.form.deliver_applicant') }}</span>
                                                </label>
                                                <input type="hidden" name="send_to_respondent" value="0">
                                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="checkbox" name="send_to_respondent" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                        @checked($sendToRespondent)>
                                                    <span>{{ __('letters.form.deliver_respondent') }}</span>
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.delivery_hint') }}</p>
                                            @error('send_to_applicant')
                                            <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.subject') }}</label>
                                        <input type="text" name="subject" value="{{ $subject ?? '' }}"
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                                        @error('subject')
                                        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    @if($caseNumber)
                                    @php
                                    // Find last reference with the same case number
                                    $last = \Illuminate\Support\Facades\DB::table('letters')
                                    ->where('case_number', $caseNumber)
                                    ->orderBy('id', 'desc')
                                    ->first();

                                    if ($last && preg_match('/\/(\d{2})$/', $last->reference_number, $m)) {
                                    $nextSeq = intval($m[1]) + 1;
                                    } else {
                                    $nextSeq = 1;
                                    }

                                    $seq = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);

                                    $nextReference = "{$caseNumber}/{$seq}";
                                    @endphp

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference Number (auto)</label>
                                        <input type="text" value="{{ $nextReference }}" readonly
                                            class="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">

                                        <p class="text-xs text-gray-500 mt-1">
                                            Auto-generated from case number. Final value assigned on save.
                                        </p>
                                    </div>
                                    @endif


                                    <div id="inline-placeholders" class="{{ $selectedTemplate && $selectedTemplate->placeholders ? '' : 'hidden' }} rounded-lg border border-dashed border-blue-300 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                                        <p class="font-semibold mb-1">{{ __('letters.form.placeholders_title') }}</p>
                                        <p>{{ __('letters.form.placeholders_help') }}</p>
                                        <p id="inline-placeholders-text" class="mt-1">
                                            @if($selectedTemplate && $selectedTemplate->placeholders)
                                            {{ implode(', ', $selectedTemplate->placeholders) }}
                                            @endif
                                        </p>
                                    </div>

                                    <div id="inline-template-summary" class="{{ $selectedTemplate ? '' : 'hidden' }} rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('letters.form.selected_template') }}</p>
                                        <p id="inline-template-title" class="font-semibold text-gray-900">{{ $selectedTemplate->title ?? '' }}</p>
                                        <p id="inline-template-category" class="text-xs">{{ $selectedTemplate->category ?? ($selectedTemplate ? __('letters.form.category_fallback') : '') }}</p>
                                        <p id="inline-template-excerpt" class="mt-1 text-xs text-gray-500">{{ $selectedTemplate? Str::limit($selectedTemplate->body, 100, '...') : '' }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.body') }}<span class="text-red-500">*</span></label>
                                        <textarea id="letter-body-editor" name="body" rows="12"
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm">{{ old('body', $body) }}</textarea>
                                        <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.body_hint') }}</p>
                                        @error('body')
                                        <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-end">
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                            {{ __('letters.actions.save_letter') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Hearings Section --}}
                @if($canViewHearings || $canCreateHearings || $canUpdateHearings || $canDeleteHearings)
                <section id="hearings" x-show="activeSection === 'hearings'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-6 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('cases.hearings.title') }}
                        </h3>
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($hearings ?? collect())->count() }} {{ __('cases.hearings.total') }}</span>
                    </div>

                    <div class="space-y-5">
                        {{-- Create --}}
                        @if(!$caseLocked && $canCreateHearings)
                        <div class="pt-1">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('cases.hearings.add_new_hearing') }}</h4>
                            <div class="mb-4 bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-sm font-semibold text-gray-900">Hearing calendar</h5>
                                    <span class="text-xs text-gray-500">Click a date/event to fill the form below.</span>
                                </div>
                                <div id="hearings-calendar" style="min-height: 360px;"></div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('cases.hearings.store', $case->id) }}"
                            class="grid md:grid-cols-5 gap-3" data-hearing-create-form>
                            @csrf
                            <div class="md:col-span-5 grid md:grid-cols-4 gap-3">
                                <input id="hearing_date_new" type="text"
                                    class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 w-full focus:ring-2 focus:ring-emerald-500 focus-border-emerald-500 transition-colors duration-150"
                                    placeholder="{{ __('cases.hearings.add_new_hearing') }}" required autocomplete="off">
                                <input id="hearing_time_new" type="time" min="00:00" max="11:59"
                                    class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 w-full focus:ring-2 focus:ring-emerald-500 focus-border-emerald-500 transition-colors duration-150"
                                    placeholder="HH:MM (AM)" required>
                                <input name="type" placeholder="{{ __('cases.hearings.type_placeholder') }}"
                                    class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus-border-emerald-500 transition-colors duration-150" required>
                                <input name="location" placeholder="{{ __('cases.hearings.location_placeholder') }}"
                                    class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus-border-emerald-500 transition-colors duration-150">
                            </div>
                            <textarea name="notes" rows="2" placeholder="{{ __('cases.hearings.notes_placeholder') }}"
                                class="md:col-span-5 px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus-border-emerald-500 transition-colors duration-150" required></textarea>
                            <input type="hidden" id="hearing_at_greg_new">
                            <input type="hidden" name="hearing_at" id="hearing_at_new">
                            <button class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors duration-150 flex items-center justify-center gap-1 md:col-span-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                {{ __('cases.hearings.add_hearing') }}
                            </button>
                        </form>
                        @endif

                        {{-- Saved hearings table --}}
                        @php
                        $orderedHearings = ($hearings ?? collect())
                        ->sortByDesc(function($h) {
                        try {
                        return \Illuminate\Support\Carbon::parse($h->hearing_at)->timestamp;
                        } catch (\Throwable $e) {
                        return 0;
                        }
                        })
                        ->values();
                        @endphp
                        @if($orderedHearings->isEmpty())
                        <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('cases.hearings.no_hearings') }}
                        </div>
                        @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200 w-12">#</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">When</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.hearings.type_placeholder') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.hearings.location_placeholder') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.hearings.notes_placeholder') }}</th>
                                        <th class="px-3 py-2 text-left font-medium border-b border-gray-200">{{ __('cases.labels.actions') ?? 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($orderedHearings as $index => $h)
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="px-3 py-2 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2">
                                            @php
                                            try {
                                            $hearingDisplay = \App\Support\EthiopianDate::format($h->hearing_at, withTime: true);
                                            } catch (\Throwable $e) {
                                            $hearingDisplay = $h->hearing_at ?? '';
                                            }
                                            @endphp
                                            <span class="font-medium" data-hearing-at="{{ $h->hearing_at }}" data-hearing-display>
                                                {{ $hearingDisplay }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $h->type ?: '-' }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $h->location ?: '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $h->notes ?: '-' }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                @if(!$caseLocked && $canUpdateHearings)
                                                <details class="relative">
                                                    <summary class="px-3 py-1.5 rounded-lg bg-white text-xs cursor-pointer text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        {{ __('cases.general.edit') }}
                                                    </summary>
                                                    <div class="absolute right-0 z-10 mt-2 w-80 p-4 rounded-lg border border-gray-200 bg-white shadow-xl">
                                                        <form method="POST" action="{{ route('cases.hearings.update',$h->id) }}" class="space-y-3" data-hearing-edit-form>
                                                            @csrf @method('PATCH')
                                                            <div class="space-y-2">
                                                                <input id="hearing_date_edit_{{ $h->id }}" type="text" data-hearing-date
                                                                    value="{{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('Y-m-d') }}"
                                                                    placeholder="{{ __('cases.hearings.add_new_hearing') }}"
                                                                    class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                    autocomplete="off">
                                                                <input type="time" id="hearing_time_edit_{{ $h->id }}" data-hearing-time min="00:00" max="11:59"
                                                                    value=""
                                                                    class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                    placeholder="HH:MM (AM)" required>
                                                                <input name="type" value="{{ $h->type ?? '' }}" placeholder="{{ __('cases.hearings.type_placeholder') }}"
                                                                    class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                                                <input name="location" value="{{ $h->location ?? '' }}" placeholder="{{ __('cases.hearings.location_placeholder') }}"
                                                                    class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                                <input type="hidden" name="hearing_at" id="hearing_at_edit_{{ $h->id }}" data-hearing-target
                                                                    value="{{ $h->hearing_at }}">
                                                            </div>
                                                            <textarea name="notes" rows="2" placeholder="{{ __('cases.hearings.notes_placeholder') }}"
                                                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>{{ $h->notes ?? '' }}</textarea>
                                                            <div class="flex justify-end gap-2 pt-1">
                                                                <button class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-xs text-white font-medium transition-colors duration-150">{{ __('cases.general.save') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </details>
                                                @endif

                                                @if(!$caseLocked && $canDeleteHearings)
                                                <form method="POST" action="{{ route('cases.hearings.delete',$h->id) }}"
                                                    onsubmit="return confirm(@json(__('cases.hearings.remove_confirm')))">
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
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </section>
                @endif

                {{-- Messages Section --}}
                <section id="messages" x-show="activeSection === 'messages'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-6 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-4">
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
                        <div class="flex {{ $fromAdmin ? 'justify-end' : 'justify-start' }}">
                            <div class="relative w-full max-w-[78%] rounded-2xl border px-4 py-3 shadow-sm transition hover:shadow-lg
                                {{ $fromAdmin ? 'bg-blue-50 border-blue-200 text-right' : 'bg-white border-gray-200' }}">
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-2 gap-2">
                                    <span class="font-medium text-gray-900">{{ $who }}</span>
                                    <span>{{ \App\Support\EthiopianDate::format($m->created_at, withTime: true) }}</span>
                                </div>
                                <div class="whitespace-pre-wrap text-gray-800 text-sm">
                                    {{ $m->body }}
                                </div>
                            </div>
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

                    @if($canCreateMessage && !$caseLocked)
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
                    @elseif($caseLocked)
                    <div class="mt-3 px-3 py-2 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-sm">
                        Messaging locked because this case is closed and has an active decision.
                    </div>
                    @endif
                </section>

                {{-- Uploaded Files Section --}}
                @if($canViewFiles || $canCreateFiles || $canUpdateFiles || $canDeleteFiles)
                <section id="uploaded-files" x-show="activeSection === 'uploaded-files'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="main-content-section p-6 rounded-2xl border border-gray-200 bg-white shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('cases.files.title') }}
                        </h3>
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($files ?? collect())->count() }} {{ __('cases.files.total') }}</span>
                    </div>

                    @if($canCreateFiles)
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
                                    <span>• {{ \App\Support\EthiopianDate::format($f->created_at, withTime: true) }}</span>
                                    @php $by = $f->uploader_name ?? trim(($f->first_name ?? '').' '.($f->last_name ?? '')); @endphp
                                    @if($by) <span>• {{ __('cases.files.uploaded_by') }} {{ $by }}</span> @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($canViewFiles)
                                <a href="{{ asset('storage/'.$f->path) }}" target="_blank"
                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 text-xs text-gray-700 border border-gray-300 transition-colors duration-150 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ __('cases.documents.view') }}
                                </a>
                                @endif
                                @if($canDeleteFiles)
                                <form method="POST" action="{{ route('cases.files.delete', [$case->id, $f->id]) }}"
                                    onsubmit="return confirm(@json(__('cases.files.remove_confirm')))">
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
                @endif
            </div>
        </div>

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

            window.getHearingTimeHelpers = window.getHearingTimeHelpers || function() {
                if (window.hearingTimeHelpers && typeof window.hearingTimeHelpers.normalizeToAm === 'function') {
                    return window.hearingTimeHelpers;
                }
                const defaultTime = '04:00';
                const normalizeToAm = (timeStr) => {
                    if (!timeStr) return defaultTime;
                    const parts = String(timeStr).split(':').map((p) => parseInt(p, 10));
                    let [h, m] = parts;
                    if (Number.isNaN(h)) return defaultTime;
                    if (Number.isNaN(m)) m = 0;
                    if (h >= 12) h = h - 12;
                    if (h < 0) h = 0;
                    const pad = (n) => String(n).padStart(2, '0');
                    return `${pad(h)}:${pad(m)}`;
                };
                window.hearingTimeHelpers = {
                    defaultTime,
                    normalizeToAm
                };
                return window.hearingTimeHelpers;
            };
            window.caseHearingDateSet = new Set(@json($hearingDateKeys));

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('[data-hearing-create-form]');
                const dateField = document.getElementById('hearing_date_new');
                const gregHidden = document.getElementById('hearing_at_greg_new');
                const {
                    defaultTime,
                    normalizeToAm
                } = window.getHearingTimeHelpers();
                const existingHearingDates = window.caseHearingDateSet || new Set();
                const duplicateDateMessage = 'A hearing already exists for this case on the selected date. Please choose another day.';
                const convertToGregorian = (value) => {
                    if (typeof window.hearingConvertToGregorian === 'function') {
                        return window.hearingConvertToGregorian(value);
                    }
                    return value;
                };
                const isDateUnavailable = (value) => {
                    if (!value) return false;
                    const normalized = value.split('T')[0];
                    return existingHearingDates.has(normalized);
                };
                const formatGregDate = (d) => {
                    if (!(d instanceof Date)) return '';
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                };

                window.hearingDateState = window.hearingDateState || {
                    lastPickedDate: null,
                    setFromDate(date) {
                        this.lastPickedDate = date instanceof Date ? date : null;
                        if (gregHidden) {
                            gregHidden.value = date ? formatGregDate(date) : '';
                        }
                    },
                    getFormatted() {
                        if (gregHidden?.value) return gregHidden.value;
                        if (this.lastPickedDate) return formatGregDate(this.lastPickedDate);
                        return '';
                    }
                };

                if (form && dateField && gregHidden) {
                    dateField.addEventListener('input', () => {
                        if (!dateField.value && window.hearingDateState?.setFromDate) {
                            window.hearingDateState.setFromDate(null);
                        }
                    });

                    form.addEventListener('submit', (e) => {
                        const timeField = document.getElementById('hearing_time_new');
                        const target = document.getElementById('hearing_at_new');
                        let dateVal = window.hearingDateState.getFormatted();
                        if (!dateVal && dateField?.value) {
                            dateVal = convertToGregorian(dateField.value) || '';
                        }
                        if (!dateVal) {
                            e.preventDefault();
                            alert('Please select a hearing date.');
                            return;
                        }
                        if (isDateUnavailable(dateVal)) {
                            e.preventDefault();
                            alert(duplicateDateMessage);
                            return;
                        }
                        const timeVal = normalizeToAm(timeField?.value || defaultTime);
                        if (timeField && !timeField.value) {
                            timeField.value = timeVal;
                        }
                        target.value = `${dateVal}T${timeVal}:00`;
                    });
                }

                // Existing hearing inline edit forms
                const editForms = document.querySelectorAll('[data-hearing-edit-form]');
                const buildDateValue = (dateString, timeString) => {
                    if (!dateString) return '';
                    const t = timeString || '00:00';
                    const toGreg = (typeof window.hearingConvertToGregorian === 'function') ?
                        window.hearingConvertToGregorian :
                        (v) => v;
                    const gregDate = toGreg(dateString) || dateString;
                    return `${gregDate}T${t}:00`;
                };

                editForms.forEach((editForm) => {
                    const editDate = editForm.querySelector('[data-hearing-date]');
                    const editTime = editForm.querySelector('[data-hearing-time]');
                    const editTarget = editForm.querySelector('[data-hearing-target]');
                    if (!editDate || !editTarget) return;

                    const syncHidden = () => {
                        editTarget.value = buildDateValue(editDate.value, editTime?.value);
                    };

                    editDate.addEventListener('input', syncHidden);
                    editTime?.addEventListener('input', syncHidden);
                    syncHidden();

                    editForm.addEventListener('submit', (e) => {
                        if (!editDate.value) {
                            e.preventDefault();
                            alert('Please select a hearing date.');
                            return;
                        }
                        syncHidden();
                    });
                });
            });
        </script>

        @push('scripts')
        <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
        <script>
            (function() {
                const TINY_BASE = "{{ asset('vendor/tinymce') }}";
                const categoryFallback = @json(__('letters.form.category_fallback'));
                const inlineTemplates = @json($inlineTemplatesData) || {};
                let pendingBody = '';

                const common = {
                    base_url: TINY_BASE,
                    suffix: '.min',
                    license_key: 'gpl',
                    branding: false,
                    promotion: false,
                    menubar: true,
                    toolbar_mode: 'wrap',
                    toolbar_sticky: true,
                    plugins: 'lists link table code image advlist charmap fullscreen wordcount',
                    toolbar: [
                        'undo redo |  fontfamily fontsize | bold italic underline strikethrough removeformat',
                        '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                        '| numlist bullist outdent indent  | fullscreen code'
                    ].join(' '),
                    forced_root_block: 'p',
                    forced_root_block_attrs: {
                        style: 'text-align: justify;'
                    },
                    content_style: `
           body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; }
          table{width:100%;border-collapse:collapse}
          td,th{border:1px solid #ddd;padding:4px}
          body{font-size:14px;line-height:1.5}
        `,
                    paste_postprocess(plugin, args) {
                        const blocks = args.node.querySelectorAll('p,div,li,td,th,blockquote');
                        blocks.forEach(el => el.style.textAlign = 'justify');
                    },
                    resize: false,
                    statusbar: true,
                    setup(editor) {
                        editor.on('init', () => editor.execCommand('JustifyFull'));
                    }
                };

                tinymce.init({
                    ...common,
                    selector: '#letter-body-editor',
                    height: 800,
                    min_height: 800,
                    max_height: 800,
                    setup(editor) {
                        common.setup(editor);
                        editor.on('init', () => {
                            if (pendingBody) {
                                editor.setContent(pendingBody);
                                pendingBody = '';
                            }
                        });
                    }
                });

                const initTemplateLoader = () => {
                    const templateSelect = document.getElementById('inline-template-select');
                    const loadButton = document.getElementById('inline-template-load');
                    const inlineForm = document.querySelector('#write-letter-panel form[action*="letters.store"]');
                    const hiddenTemplate = document.getElementById('inline-template-hidden');
                    const subjectInput = inlineForm?.querySelector('input[name=\"subject\"]');
                    const refBlock = document.getElementById('inline-reference-block');
                    const refValue = document.getElementById('inline-reference-value');
                    const placeholderBlock = document.getElementById('inline-placeholders');
                    const placeholderText = document.getElementById('inline-placeholders-text');
                    const summaryBlock = document.getElementById('inline-template-summary');
                    const summaryTitle = document.getElementById('inline-template-title');
                    const summaryCategory = document.getElementById('inline-template-category');
                    const summaryExcerpt = document.getElementById('inline-template-excerpt');

                    const stripHtml = (html) => {
                        const div = document.createElement('div');
                        div.innerHTML = html || '';
                        return div.textContent || div.innerText || '';
                    };

                    const buildReference = (tpl) => {
                        if (!tpl || tpl.reference_sequence === null || tpl.reference_sequence === undefined) return null;
                        const next = (parseInt(tpl.reference_sequence, 10) || 0) + 1;
                        const seq = String(next).padStart(4, '0');
                        return [tpl.subject_prefix || '', seq].filter(Boolean).join('/');
                    };

                    const renderMeta = (tpl) => {
                        const placeholders = Array.isArray(tpl?.placeholders) ? tpl.placeholders : [];
                        if (placeholderBlock) {
                            if (placeholders.length) {
                                placeholderText.textContent = placeholders.join(', ');
                                placeholderBlock.classList.remove('hidden');
                            } else {
                                placeholderText.textContent = '';
                                placeholderBlock.classList.add('hidden');
                            }
                        }

                        if (summaryBlock) {
                            if (tpl) {
                                summaryBlock.classList.remove('hidden');
                                if (summaryTitle) summaryTitle.textContent = tpl.title || '';
                                if (summaryCategory) summaryCategory.textContent = tpl.category || categoryFallback;
                                if (summaryExcerpt) {
                                    const text = stripHtml(tpl.body || '');
                                    summaryExcerpt.textContent = text.length > 120 ? text.slice(0, 120) + '...' : text;
                                }
                            } else {
                                summaryBlock.classList.add('hidden');
                            }
                        }

                        if (refBlock && refValue) {
                            const ref = buildReference(tpl);
                            if (ref) {
                                refValue.value = ref;
                                refBlock.classList.remove('hidden');
                            } else {
                                refValue.value = '';
                                refBlock.classList.add('hidden');
                            }
                        }
                    };

                    const toggleFields = (enabled) => {
                        document.querySelectorAll('[data-letter-field]').forEach(el => {
                            el.removeAttribute('disabled');
                        });
                    };

                    const clearTemplate = () => {
                        if (subjectInput) subjectInput.value = '';
                        if (hiddenTemplate) hiddenTemplate.value = '';
                        const editor = tinymce.get('letter-body-editor');
                        if (editor) {
                            editor.setContent('');
                        } else {
                            const textarea = document.getElementById('letter-body-editor');
                            if (textarea) textarea.value = '';
                        }
                        renderMeta(null);
                    };

                    const fillFromTemplate = (id) => {
                        const tpl = inlineTemplates?.[id] ?? inlineTemplates?.[String(id)] ?? inlineTemplates?.[Number(id)];
                        if (!tpl) {
                            clearTemplate();
                            return;
                        }

                        if (subjectInput) {
                            subjectInput.value = tpl.subject || '';
                        }

                        if (hiddenTemplate) hiddenTemplate.value = id;

                        const editor = tinymce.get('letter-body-editor');
                        if (editor) {
                            editor.setContent(tpl.body || '');
                            editor.focus();
                        } else {
                            const textarea = document.getElementById('letter-body-editor');
                            const bodyText = tpl.body || '';
                            if (textarea) textarea.value = bodyText;
                        }

                        renderMeta(tpl);
                    };

                    templateSelect?.addEventListener('change', (e) => {
                        if (e.target.value) {
                            fillFromTemplate(e.target.value);
                            toggleFields(true);
                        } else {
                            clearTemplate();
                            toggleFields(false);
                        }
                    });

                    loadButton?.addEventListener('click', (e) => {
                        e.preventDefault();
                        const id = templateSelect?.value;
                        if (!id) {
                            alert(@json(__('letters.form.select_placeholder')));
                            return;
                        }
                        fillFromTemplate(id);
                        toggleFields(true);
                    });

                    const ensureEditable = () => {
                        const editor = tinymce.get('letter-body-editor');
                        if (!editor) {
                            setTimeout(ensureEditable, 120);
                        }
                    };
                    ensureEditable();

                    const bodyTextarea = document.getElementById('letter-body-editor');
                    const hasBody = bodyTextarea?.value?.trim().length > 0;
                    const initialTpl = templateSelect?.value;
                    if (initialTpl) {
                        fillFromTemplate(initialTpl);
                        toggleFields(true);
                        if (hiddenTemplate) hiddenTemplate.value = initialTpl;
                    } else {
                        toggleFields(hasBody);
                    }
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initTemplateLoader);
                } else {
                    initTemplateLoader();
                }
            })();
        </script>
        @endpush

        @push('scripts')
        <script src="{{ asset('vendor/modern-ethiopian-calendar/js/modern-calendar.js') }}"></script>
        <script src="{{ asset('vendor/modern-ethiopian-calendar/js/datepicker.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const hasModernPicker = (typeof ModernDatePicker !== 'undefined');
                const ethHelper = (() => {
                    try {
                        return new ModernCalendar(document.createElement('div'), {
                            calendar: 'ethiopian',
                            language: 'am'
                        });
                    } catch (e) {
                        return null;
                    }
                })();
                const {
                    defaultTime,
                    normalizeToAm
                } = window.getHearingTimeHelpers();
                const existingHearingDates = window.caseHearingDateSet || new Set();
                const toEthiopianDateString = (input) => {
                    if (!ethHelper || !input) return input || '';
                    const d = (input instanceof Date) ? input : new Date(input);
                    if (Number.isNaN(d.getTime())) return input || '';
                    const eth = ethHelper.gregorianToEthiopian(d);
                    return `${eth.year}-${String(eth.month).padStart(2, '0')}-${String(eth.day).padStart(2, '0')}`;
                };
                const toGregorianDate = (ethString) => {
                    if (!ethHelper || !ethString) return null;
                    const parts = ethString.split('-').map((p) => parseInt(p, 10));
                    if (parts.length !== 3 || parts.some((p) => Number.isNaN(p))) return null;
                    const [y, m, d] = parts;
                    try {
                        const jd = ethHelper.ethiopianToJD(y, m, d);
                        const g = ethHelper.jdToGregorian(jd);
                        return new Date(g.year, g.month - 1, g.day);
                    } catch (e) {
                        return null;
                    }
                };
                const toGregorianString = (ethString) => {
                    const g = toGregorianDate(ethString);
                    if (!g || Number.isNaN(g.getTime())) return '';
                    const yyyy = g.getFullYear();
                    const mm = String(g.getMonth() + 1).padStart(2, '0');
                    const dd = String(g.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                };
                window.hearingConvertToGregorian = toGregorianString;
                const formatTime = (d) => {
                    if (!(d instanceof Date)) return '';
                    const hh = String(d.getHours()).padStart(2, '0');
                    const mi = String(d.getMinutes()).padStart(2, '0');
                    return `${hh}:${mi}`;
                };
                const toDateKey = (input) => {
                    if (!input) return '';
                    const d = (input instanceof Date) ? input : new Date(input);
                    if (Number.isNaN(d.getTime())) return '';
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                };
                const toTimeKey = (input) => {
                    if (!input) return '';
                    const d = (input instanceof Date) ? input : new Date(input);
                    if (Number.isNaN(d.getTime())) return '';
                    return formatTime(d);
                };

                const minDate = new Date();
                minDate.setHours(0, 0, 0, 0);
                const formatDate = (d) => {
                    if (!(d instanceof Date)) return '';
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                };

                const attachPicker = (input, onValidPick) => {
                    if (!input || !hasModernPicker) return null;
                    return new ModernDatePicker(input, {
                        calendar: 'ethiopian',
                        language: 'am',
                        theme: 'modern',
                        format: 'yyyy-mm-dd',
                        closeOnSelect: false,
                        onSelect: (date, formatted) => {
                            const picked = new Date(date);
                            picked.setHours(0, 0, 0, 0);
                            if (picked < minDate) {
                                alert('Please select today or a future date.');
                                input.value = '';
                                onValidPick?.(null, '');
                                return;
                            }
                            const value = formatted || formatDate(picked);
                            input.value = value;
                            onValidPick?.(picked, value);
                            if (input && input.blur) input.blur();
                        }
                    });
                };

                const dateInput = document.getElementById('hearing_date_new');
                const timeInput = document.getElementById('hearing_time_new');
                if (dateInput) {
                    attachPicker(dateInput, (picked, value) => {
                        if (!picked || !value) {
                            window.hearingDateState?.setFromDate?.(null);
                            return;
                        }
                        const dateSet = window.caseHearingDateSet || new Set();
                        const gregorianValue = (typeof window.hearingConvertToGregorian === 'function') ?
                            window.hearingConvertToGregorian(value) :
                            value;
                        const normalized = (gregorianValue || '').split('T')[0];
                        if (normalized && dateSet.has(normalized)) {
                            alert('A hearing already exists for this case on the selected date. Please choose another day.');
                            window.hearingDateState?.setFromDate?.(null);
                            dateInput.value = '';
                            return;
                        }
                        if (window.hearingDateState?.setFromDate) {
                            window.hearingDateState.setFromDate(picked);
                        }
                    });
                }
                if (timeInput && !timeInput.value) {
                    timeInput.value = defaultTime;
                }

                // Inline edit pickers
                if (hasModernPicker) {
                    document.querySelectorAll('[data-hearing-edit-form]').forEach((form) => {
                        const dateEl = form.querySelector('[data-hearing-date]');
                        const timeEl = form.querySelector('[data-hearing-time]');
                        const target = form.querySelector('[data-hearing-target]');
                        if (target && dateEl) {
                            const existing = target.value || dateEl.value;
                            const ethVal = toEthiopianDateString(existing);
                            if (ethVal) dateEl.value = ethVal;
                        }
                        if (timeEl) {
                            const baseTime = target?.value ? normalizeToAm(toTimeKey(target.value)) : defaultTime;
                            if (!timeEl.value) timeEl.value = baseTime;
                        }
                        attachPicker(dateEl, (picked, value) => {
                            if (!target) return;
                            if (!picked || !value) {
                                target.value = '';
                                return;
                            }
                            const timeVal = normalizeToAm(timeEl?.value || defaultTime);
                            const gregDateStr = convertToGregorian(value) || value;
                            target.value = `${gregDateStr}T${timeVal}:00`;
                            if (timeEl && !timeEl.value) {
                                timeEl.value = timeVal;
                            }
                        });
                    });
                }

                // Hearing calendar using Ethiopian calendar
                const calendarEl = document.getElementById('hearings-calendar');
                if (calendarEl && typeof ModernCalendar !== 'undefined') {
                    @php
                    $fcEvents = ($hearings ?? collect())
                        ->map(function ($h) {
                            $start = null;
                            try {
                                $start = \Illuminate\Support\Carbon::parse($h->hearing_at)->toIso8601String();
                            } catch (\Throwable $e) {
                                if (is_string($h->hearing_at)) {
                                    $clean = trim($h->hearing_at);
                                    foreach (['d-m-Y H:i:s', 'd-m-Y H:i'] as $fmt) {
                                        try {
                                            $start = \Illuminate\Support\Carbon::createFromFormat($fmt, $clean)->toIso8601String();
                                            break;
                                        } catch (\Throwable $e2) {
                                            $start = null;
                                        }
                                    }
                                }
                            }
                            if (!$start) return null;
                            return [
                                'title' => trim(($h->type ?? '') . ' ' . ($h->location ?? '')),
                                'start' => $start,
                                'allDay' => false,
                                'extendedProps' => [
                                    'location' => $h->location ?? null,
                                    'notes'    => $h->notes ?? null,
                                ],
                            ];
                        })
                        ->filter()
                        ->values()
                        ->toArray();
                    @endphp
                    const fcEvents = @json($fcEvents);
                    const eventMetaByDate = {};
                    const eventDateSet = new Set(
                        (fcEvents || [])
                        .map((ev) => {
                            const key = toDateKey(ev.start);
                            if (key && !eventMetaByDate[key]) {
                                eventMetaByDate[key] = {
                                    location: ev.extendedProps?.location || null,
                                    notes: ev.extendedProps?.notes || null,
                                    time: normalizeToAm(toTimeKey(ev.start)) || null,
                                };
                            }
                            return key;
                        })
                        .filter(Boolean)
                    );

                    const dateField = document.getElementById('hearing_date_new');
                    const timeField = document.getElementById('hearing_time_new');
                    const setFormFromDate = (d, opts = {}) => {
                        if (!(d instanceof Date)) return;
                        const yyyy = d.getFullYear();
                        const mm = String(d.getMonth() + 1).padStart(2, '0');
                        const dd = String(d.getDate()).padStart(2, '0');
                        const dateStr = `${yyyy}-${mm}-${dd}`;
                        if (existingHearingDates.has(dateStr)) {
                            alert('A hearing already exists for this case on the selected date. Please choose another day.');
                            if (dateField) {
                                dateField.value = '';
                            }
                            if (window.hearingDateState?.setFromDate) {
                                window.hearingDateState.setFromDate(null);
                            }
                            return;
                        }
                        if (dateField) {
                            const displayVal = toEthiopianDateString(d) || dateStr;
                            dateField.value = displayVal;
                        }
                        if (timeField && opts.setTime) {
                            timeField.value = normalizeToAm(opts.setTime);
                        } else if (timeField && !timeField.value) {
                            timeField.value = defaultTime;
                        }
                        if (window.hearingDateState?.setFromDate) {
                            window.hearingDateState.setFromDate(new Date(d));
                        }
                    };

                    const highlightEventDays = () => {
                        calendarEl.querySelectorAll('.day.has-event').forEach((el) => {
                            el.classList.remove('has-event');
                            el.querySelector('.event-dot')?.remove();
                        });
                        calendarEl.querySelectorAll('.day[data-date]').forEach((el) => {
                            const dateStr = el.getAttribute('data-date');
                            if (eventDateSet.has(dateStr)) {
                                el.classList.add('has-event');
                                const dot = document.createElement('span');
                                dot.className = 'event-dot';
                                el.appendChild(dot);
                            }
                        });
                    };

                    const calendar = new ModernCalendar(calendarEl, {
                        calendar: 'ethiopian',
                        language: 'am',
                        theme: 'modern',
                        firstDayOfWeek: 0,
                        onDateSelect: (date) => {
                            if (date instanceof Date) {
                                const dateKey = toDateKey(date);
                                const meta = eventMetaByDate[dateKey];
                                setFormFromDate(date, {
                                    setTime: meta?.time || defaultTime
                                });
                                timeField?.focus();

                                if (meta) {
                                    const locationInput = document.querySelector('[name="location"]');
                                    if (locationInput && !locationInput.value && meta.location) {
                                        locationInput.value = meta.location;
                                    }
                                    const notesInput = document.querySelector('[name="notes"]');
                                    if (notesInput && !notesInput.value && meta.notes) {
                                        notesInput.value = meta.notes;
                                    }
                                }
                            }
                            requestAnimationFrame(highlightEventDays);
                        },
                        onMonthChange: () => {
                            requestAnimationFrame(highlightEventDays);
                        }
                    });

                    // Highlight existing hearings after initial render
                    requestAnimationFrame(highlightEventDays);
                } else if (calendarEl) {
                    calendarEl.innerHTML = '<div class="text-sm text-red-600">Calendar library failed to load.</div>';
                }

                // Convert displayed hearing dates to Ethiopian calendar (UI only)
                (() => {
                    if (!ethHelper) return;
                    const fmt = (val) => {
                        const d = val ? new Date(val) : null;
                        if (!d || Number.isNaN(d.getTime())) return val;
                        const eth = ethHelper.gregorianToEthiopian(d);
                        const pad = (n) => String(n).padStart(2, '0');
                        const monthNames = ethHelper.getMonthNames?.() || [];
                        const monthName = monthNames[eth.month - 1] || pad(eth.month);
                        const rawHour = d.getHours();
                        const hour = rawHour % 12 === 0 ? 12 : rawHour % 12;
                        const time = `${hour}፡${pad(d.getMinutes())}`;
                        return `${monthName}-${pad(eth.day)}-${eth.year} ዓ.ም ${time} ሰዓት`;
                    };
                    document.querySelectorAll('[data-hearing-display]').forEach((el) => {
                        const val = el.getAttribute('data-hearing-at');
                        const out = fmt(val);
                        if (out) el.textContent = out;
                    });
                })();
            });
        </script>
        @endpush

    </div>
</x-admin-layout>
