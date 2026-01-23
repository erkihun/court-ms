@php
$canCreateBench = function_exists('userHasPermission')
    ? userHasPermission('bench-notes.create')
    : (auth()->user()?->hasPermission('bench-notes.create') ?? false);
$canUpdateBench = function_exists('userHasPermission')
    ? userHasPermission('bench-notes.update')
    : (auth()->user()?->hasPermission('bench-notes.update') ?? false);
$canDeleteBench = function_exists('userHasPermission')
    ? userHasPermission('bench-notes.delete')
    : (auth()->user()?->hasPermission('bench-notes.delete') ?? false);
$canViewBench = function_exists('userHasPermission')
    ? userHasPermission('bench-notes.view')
    : (auth()->user()?->hasPermission('bench-notes.view') ?? true);
@endphp

@if($canViewBench)
<x-admin-layout title="{{ __('bench.title') }}">
    @section('page_header', __('bench.page_header.index'))

    @push('styles')
    <style>
    .cms-output {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 0.75rem;
        color: #111827;
        font-size: 0.925rem;
        line-height: 1.6;
    }

    .cms-output p {
        margin: 0 0 0.55rem;
    }

    .cms-output ul,
    .cms-output ol {
        margin: 0.4rem 0 0.6rem 1.25rem;
        padding-left: 1.25rem;
    }

    .cms-output ul {
        list-style: disc;
    }

    .cms-output ol {
        list-style: decimal;
    }

    .cms-output li {
        margin: 0.15rem 0;
    }

    .cms-output blockquote {
        border-left: 3px solid #e5e7eb;
        padding-left: 0.75rem;
        margin: 0.5rem 0;
        color: #374151;
    }

    .cms-output img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 0.5rem 0;
    }

    /* Card styles */
    .notes-container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .note-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        overflow: hidden;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .note-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .card-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(to bottom, #fafbfc, #ffffff);
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
    }

    .case-badge {
        display: inline-block;
        padding: 0.375rem 1rem;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2);
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f3f4f6;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .meta-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        background: #f3f4f6;
        border-radius: 0.5rem;
        flex-shrink: 0;
    }

    .meta-icon svg {
        width: 1.25rem;
        height: 1.25rem;
        color: #6b7280;
    }

    .meta-content {
        flex: 1;
    }

    .meta-label {
        font-size: 0.75rem;
        color: #9ca3af;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.125rem;
    }

    .meta-value {
        font-size: 0.875rem;
        color: #374151;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .btn-view {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #dbeafe;
    }

    .btn-view:hover {
        background: #dbeafe;
    }

    .btn-edit {
        background: #f9fafb;
        color: #374151;
        border-color: #e5e7eb;
    }

    .btn-edit:hover {
        background: #f3f4f6;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-delete:hover {
        background: #fee2e2;
    }

    .empty-state {
        max-width: 28rem;
        margin: 4rem auto;
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1.5rem;
        color: #d1d5db;
    }

    @media (max-width: 768px) {

        .card-header,
        .card-body,
        .card-footer {
            padding: 1.25rem;
        }

        .meta-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .action-buttons {
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
    }
    </style>
    @endpush

    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('bench.page_header.index') }}</h1>
            <p class="text-sm text-gray-600">{{ __('bench.descriptions.index') }}</p>
        </div>
        <div class="flex gap-2">
            @if($canCreateBench)
            <a href="{{ route('bench-notes.create', ['case_id' => $caseId]) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('bench.buttons.new_note') }}
            </a>
            @endif
            <a href="{{ route('cases.index') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50">
                {{ __('bench.labels.cases') }}
            </a>
        </div>
    </div>

    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" class="grid gap-3 md:grid-cols-[2fr_auto_auto] md:items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('bench.labels.filter_by_case') }}</label>
                <select name="case_id"
                    class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('bench.options.all_cases') }}</option>
                    @foreach($cases as $c)
                    <option value="{{ $c->id }}" @selected($caseId===$c->id)>
                        {{ $c->case_number }} — {{ $c->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                {{ __('bench.buttons.apply') }}
            </button>
            @if($caseId)
            <a href="{{ route('bench-notes.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50">
                {{ __('bench.buttons.clear') }}
            </a>
            @endif
        </form>
    </div>

    @if($benchNotes->count() > 0)
    <div class="notes-container">
        @foreach($benchNotes as $note)
        @php
        $safeTitle = clean($note->title ?? '', 'cases');
        $safeCaseTitle = clean($note->case?->title ?? '', 'cases');
        $safeNote = clean($note->note ?? '', 'default');
        @endphp
        <div class="note-card">
            <div class="card-header">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="mb-3">
                            <span class="case-badge">
                                {{ $note->case?->case_number ?? __('bench.meta.na') }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">
                            {!! $safeTitle !!}
                        </h2>
                        @if($note->case?->title)
                        <p class="text-sm text-gray-600 bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                            <span class="font-medium text-blue-700">{{ __('bench.labels.case_prefix') }}</span> {!! $safeCaseTitle !!}
                        </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                            {{ \App\Support\EthiopianDate::format($note->created_at) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-6 text-center space-y-1 text-sm text-gray-700">
                    <div>{{ $note->judgeOne?->name ?? __('bench.meta.unknown') }}</div>
                    <div>{{ $note->judgeTwo?->name ?? __('bench.meta.unknown') }}</div>
                    <div>{{ $note->judgeThree?->name ?? __('bench.meta.unknown') }}</div>
                </div>

                <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="grid gap-3 text-sm text-gray-700 sm:grid-cols-4">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('bench.labels.author') }}</div>
                            <div class="font-medium">{{ $note->user?->name ?? __('bench.meta.unknown') }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('bench.labels.created_date') }}</div>
                            <div class="font-medium">{{ \App\Support\EthiopianDate::format($note->created_at) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('bench.labels.created_time') }}</div>
                            <div class="font-medium">{{ \App\Support\EthiopianDate::formatTime($note->created_at, timeFormat: 'g:i A') }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('bench.labels.last_updated') }}</div>
                            <div class="font-medium">
                                @if($note->updated_at && $note->updated_at != $note->created_at)
                                {{ \App\Support\EthiopianDate::format($note->updated_at, withTime: true) }}
                                @else
                                -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Note Content</h3>
                    <div class="cms-output">
                        @if($note->note)
                        {!! $safeNote !!}
                        @else
                        <p class="text-gray-500 italic">{{ __('bench.helpers.empty_content') }}</p>
                        @endif
                    </div>
                </div>

                <div class="meta-grid"></div>
            </div>

            <div class="card-footer">
                <div class="mb-4 grid grid-cols-3 gap-6 text-center">
                    <div>
                        <div class="text-sm font-medium text-gray-700">{{ $note->judgeOne?->name ?? __('bench.meta.unknown') }}</div>
                        @if(!empty($note->judgeOne?->signature_url))
                        <img src="{{ $note->judgeOne?->signature_url }}" alt="{{ $note->judgeOne?->name }}" class="mx-auto mt-2 max-h-16 w-auto">
                        @else
                        <div class="mt-2 text-sm text-gray-400">Judge 1 signature</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-700">{{ $note->judgeTwo?->name ?? __('bench.meta.unknown') }}</div>
                        @if(!empty($note->judgeTwo?->signature_url))
                        <img src="{{ $note->judgeTwo?->signature_url }}" alt="{{ $note->judgeTwo?->name }}" class="mx-auto mt-2 max-h-16 w-auto">
                        @else
                        <div class="mt-2 text-sm text-gray-400">Judge 2 signature</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-700">{{ $note->judgeThree?->name ?? __('bench.meta.unknown') }}</div>
                        @if(!empty($note->judgeThree?->signature_url))
                        <img src="{{ $note->judgeThree?->signature_url }}" alt="{{ $note->judgeThree?->name }}" class="mx-auto mt-2 max-h-16 w-auto">
                        @else
                        <div class="mt-2 text-sm text-gray-400">Judge 3 signature</div>
                        @endif
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('bench-notes.show', $note->id) }}" class="btn btn-view">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        View
                    </a>
                    @if($canUpdateBench)
                    <a href="{{ route('bench-notes.edit', $note->id) }}" class="btn btn-edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('bench.buttons.edit') }}
                    </a>
                    @endif
                    @if($canDeleteBench)
                    <form method="POST" action="{{ route('bench-notes.destroy', $note->id) }}"
                        onsubmit="return confirm('{{ __('bench.confirmations.delete') }}')"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('bench.buttons.delete') }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $benchNotes->links() }}
    </div>
    @else
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.801 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.801 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('bench.empty.title') }}</h3>
        <p class="text-gray-600 mb-6">
            @if($caseId)
            {{ __('bench.empty.description_case') }}
            @else
            {{ __('bench.empty.description_general') }}
            @endif
        </p>
        @if($canCreateBench)
        <a href="{{ route('bench-notes.create', ['case_id' => $caseId]) }}"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('bench.buttons.create_new') }}
        </a>
        @endif
    </div>
    @endif
</x-admin-layout>
@endif
