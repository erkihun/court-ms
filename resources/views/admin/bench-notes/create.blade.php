<x-admin-layout title="{{ __('bench.title') }}">
    @section('page_header', __('bench.page_header.create'))

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
        background: linear-gradient(to right, #f8fafc, #ffffff);
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
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        border-color: #2563eb;
        box-shadow: 0 1px 3px rgba(59, 130, 246, 0.2);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb, #1e40af);
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25);
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

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {

        .card-header,
        .card-body,
        .card-footer {
            padding: 1.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    @endpush

    <div class="form-container">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('bench.headings.create') }}</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ __('bench.descriptions.create') }}</p>
                </div>
                <a href="{{ route('bench-notes.index', ['case_id' => $caseId]) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('bench.buttons.back') }}
                </a>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('bench.headings.new') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('bench.headings.create_intro') }}</p>
            </div>

            <form method="POST" action="{{ route('bench-notes.store') }}">
                @csrf

                <div class="card-body">
                    <div class="form-section">
                        <h3 class="section-title">{{ __('bench.sections.basic_info') }}</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label form-label-required">{{ __('bench.labels.case') }}</label>
                                <select name="case_id" class="form-select" required>
                                    <option value="">{{ __('bench.placeholders.select_case') }}</option>
                                    @foreach($cases as $case)
                                    <option value="{{ $case->id }}" @selected(old('case_id', $caseId)===$case->id)>
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
                                <input type="text" name="title" value="{{ old('title') }}" class="form-input" required
                                    maxlength="255" placeholder="{{ __('bench.placeholders.title') }}">
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
                                    class="form-input form-textarea">{{ old('note') }}</textarea>
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
                        <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ __('bench.buttons.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('bench.buttons.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-900">{{ __('bench.headings.about') }}</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        {{ __('bench.descriptions.about') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

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
            height: 800,
            min_height: 800,
            max_height: 800
        });
    })();
    </script>

</x-admin-layout>
