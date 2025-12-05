<x-admin-layout title="{{ __('bench.title') }}">
    @section('page_header', __('bench.page_header.edit'))

    @push('styles')
    <style>
    .form-container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .form-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .card-header {
        padding: 1.75rem 2rem;
        border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(to right, #fefce8, #ffffff);
    }

    .card-body {
        padding: 2rem;
    }

    .card-footer {
        padding: 1.5rem 2rem;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }

    @media (min-width: 768px) {
        .form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-label-required:after {
        content: " *";
        color: #dc2626;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.75rem;
        background: white;
        color: #111827;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.75rem;
        background: white;
        color: #111827;
        font-size: 0.875rem;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 1rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 3rem;
    }

    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-textarea {
        min-height: 400px;
        resize: vertical;
    }

    .form-helper {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }

    .form-error {
        font-size: 0.75rem;
        color: #dc2626;
        margin-top: 0.5rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border-color: #f59e0b;
        box-shadow: 0 1px 3px rgba(245, 158, 11, 0.2);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #d97706, #b45309);
        box-shadow: 0 4px 6px rgba(245, 158, 11, 0.25);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: white;
        color: #374151;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .btn-danger {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-danger:hover {
        background: #fee2e2;
    }

    .editor-container {
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #d1d5db;
    }

    .tox-tinymce {
        border: none !important;
        border-radius: 0 0 0.75rem 0.75rem !important;
    }

    .editor-header {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 0.75rem 1rem;
    }

    .editor-header h3 {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin: 0;
    }

    .note-meta {
        background: #f0f9ff;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e0f2fe;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
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
        background: white;
        border-radius: 0.5rem;
        flex-shrink: 0;
        border: 1px solid #e0f2fe;
    }

    .meta-icon svg {
        width: 1.25rem;
        height: 1.25rem;
        color: #0ea5e9;
    }

    .meta-content {
        flex: 1;
    }

    .meta-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.125rem;
    }

    .meta-value {
        font-size: 0.875rem;
        color: #0f172a;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
    }

    .left-actions,
    .right-actions {
        display: flex;
        gap: 0.75rem;
    }

    @media (max-width: 768px) {

        .card-header,
        .card-body,
        .card-footer {
            padding: 1.5rem;
        }

        .action-buttons {
            flex-direction: column;
            gap: 1rem;
        }

        .left-actions,
        .right-actions {
            width: 100%;
            justify-content: stretch;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .meta-grid {
            grid-template-columns: 1fr;
        }
    }

    .delete-form {
        margin: 0;
    }
    </style>
    @endpush

    <div class="form-container">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('bench.headings.edit') }}</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ __('bench.descriptions.edit') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('bench-notes.index', ['case_id' => $benchNote->case_id]) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('bench.buttons.back_list') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('bench.headings.edit_note', ['title' => $benchNote->title]) }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('bench.headings.edit_intro') }}</p>
            </div>

            <form method="POST" action="{{ route('bench-notes.update', $benchNote->id) }}">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <div class="note-meta">
                        <div class="meta-grid">
                            <div class="meta-item">
                                <div class="meta-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </div>
                                <div class="meta-content">
                                    <div class="meta-label">{{ __('bench.labels.author') }}</div>
                                    <div class="meta-value">{{ $benchNote->user?->name ?? __('bench.meta.unknown') }}</div>
                                </div>
                            </div>

                            <div class="meta-item">
                                <div class="meta-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <div class="meta-content">
                                    <div class="meta-label">{{ __('bench.labels.created') }}</div>
                                    <div class="meta-value">{{ $benchNote->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>

                            @if($benchNote->updated_at != $benchNote->created_at)
                            <div class="meta-item">
                                <div class="meta-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </div>
                                <div class="meta-content">
                                    <div class="meta-label">{{ __('bench.labels.last_updated') }}</div>
                                    <div class="meta-value">{{ $benchNote->updated_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">{{ __('bench.sections.basic_info') }}</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label form-label-required">{{ __('bench.labels.case') }}</label>
                                <select name="case_id" class="form-select" required>
                                    <option value="">{{ __('bench.placeholders.select_case') }}</option>
                                    @foreach($cases as $case)
                                    <option value="{{ $case->id }}" @selected(old('case_id', $benchNote->
                                        case_id)===$case->id)>
                                        {{ $case->case_number }} — {{ $case->title }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('case_id')
                                <p class="form-error">{{ $message }}</p>
                                @enderror
                                <p class="form-helper">{{ __('bench.helpers.select_case') }}</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label form-label-required">{{ __('bench.labels.title') }}</label>
                                <input type="text" name="title" value="{{ old('title', $benchNote->title) }}"
                                    class="form-input" required maxlength="255" placeholder="{{ __('bench.placeholders.title') }}">
                                @error('title')
                                <p class="form-error">{{ $message }}</p>
                                @enderror
                                <p class="form-helper">{{ __('bench.helpers.title') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">{{ __('bench.sections.note_content') }}</h3>
                        <div class="form-group">
                            <div class="editor-header">
                                <h3>{{ __('bench.labels.note_editor') }}</h3>
                            </div>
                            <div class="editor-container">
                                <textarea id="bench-note-editor" name="note"
                                    class="form-input form-textarea">{{ old('note', $benchNote->note) }}</textarea>
                            </div>
                            @error('note')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                            <p class="form-helper mt-3">
                                {{ __('bench.helpers.note_editor') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="action-buttons">
                        <div class="left-actions">
                            <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('bench.buttons.cancel') }}
                            </button>
                        </div>

                        <div class="right-actions">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ __('bench.buttons.update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <div class="mt-6 rounded-lg bg-amber-50 border border-amber-200 p-4">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-amber-900">{{ __('bench.headings.editing_note') }}</h4>
                    <p class="text-sm text-amber-700 mt-1">
                        {{ __('bench.descriptions.editing_notice') }}
                        {{ __('bench.descriptions.created_meta', ['author' => $benchNote->user?->name ?? __('bench.meta.unknown'), 'date' => $benchNote->created_at->format('F d, Y')]) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Load LOCAL TinyMCE --}}
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
    <script>
    (function() {
        const TINY_BASE = "{{ asset('vendor/tinymce') }}";

        const common = {
            base_url: TINY_BASE,
            suffix: '.min',
            license_key: 'gpl',
            branding: false,
            promotion: false,
            menubar: true,

            // Show all toolbar items (no overflow chevron)
            toolbar_mode: 'wrap',
            toolbar_sticky: true,

            plugins: 'lists link table code image advlist charmap fullscreen wordcount',
            toolbar: [
                'undo redo |  fontfamily fontsize | bold italic underline strikethrough removeformat',
                '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                '| numlist bullist outdent indent  | fullscreen code'
            ].join(' '),

            // Make every new block justified
            forced_root_block: 'p',
            forced_root_block_attrs: {
                style: 'text-align: justify;'
            },

            // Enforce justified look in the editor for common blocks
            content_style: `
            body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; }
            table{width:100%;border-collapse:collapse}
            td,th{border:1px solid #ddd;padding:4px}
            body{font-size:14px;line-height:1.5}
        `,

            // Fix pasted content that brings its own alignment
            paste_postprocess(plugin, args) {
                const blocks = args.node.querySelectorAll('p,div,li,td,th,blockquote');
                blocks.forEach(el => {
                    el.style.textAlign = 'justify';
                });
            },

            // Fixed-height editors
            resize: false,
            statusbar: true,

            setup(editor) {
                // Ensure initial content shows as justified on init
                editor.on('init', () => {
                    editor.execCommand('JustifyFull');
                });
            }
        };

        // Bench note editor
        tinymce.init({
            ...common,
            selector: '#bench-note-editor',
            height: 400,
            min_height: 400,
            max_height: 400
        });
    })();
    </script>
    @endpush
</x-admin-layout>
