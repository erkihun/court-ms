{{-- resources/views/letters/compose.blade.php --}}
@php use Illuminate\Support\Str; @endphp
<x-admin-layout title="{{ __('letters.titles.compose') }}">
    @section('page_header', __('letters.titles.compose'))

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('letters.form.select_template') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('letters.description.compose') }}</p>

            <form method="GET" action="{{ route('letters.compose') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.template_label') }}</label>
                    <select name="template_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" onchange="this.form.submit()">
                        <option value="">{{ __('letters.form.select_placeholder') }}</option>
                        @foreach($templates as $template)
                        <option value="{{ $template->id }}" @selected(optional($selectedTemplate)->id === $template->id)>
                            {{ $template->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">{{ __('letters.actions.load') }}</button>
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('letters.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="template_id" value="{{ optional($selectedTemplate)->id }}">

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
                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.cc') }}</label>
                        <input type="text" name="cc" value="{{ $cc }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="{{ __('letters.form.cc_placeholder') }}">
                        <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.cc_hint') }}</p>
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

                @if($selectedTemplate)
                @php
                $nextSeq = ($selectedTemplate->reference_sequence ?? 0) + 1;
                $nextReference = implode('/', array_filter([
                    $selectedTemplate->subject_prefix,
                    str_pad($nextSeq, 4, '0', STR_PAD_LEFT),
                ]));
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reference Number (auto)</label>
                    <input type="text" value="{{ $nextReference }}" readonly
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700">
                    <p class="text-xs text-gray-500 mt-1">
                        Based on template prefix and next sequence; final value is assigned on save.
                    </p>
                </div>
                @endif

                @if($selectedTemplate && $selectedTemplate->placeholders)
                <div class="rounded-lg border border-dashed border-blue-300 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                    <p class="font-semibold mb-1">{{ __('letters.form.placeholders_title') }}</p>
                    <p>{{ __('letters.form.placeholders_help') }}</p>
                    <p class="mt-1">{{ implode(', ', $selectedTemplate->placeholders) }}</p>
                </div>
                @endif

                @if($selectedTemplate)
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('letters.form.selected_template') }}</p>
                    <p class="font-semibold text-gray-900">{{ $selectedTemplate->title }}</p>
                    <p class="text-xs">{{ $selectedTemplate->category ?? __('letters.form.category_fallback') }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ Str::limit($selectedTemplate->body, 100, '...') }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.body') }}<span class="text-red-500">*</span></label>
                    <textarea id="letter-body-editor" name="body" rows="12"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm" required>{{ old('body', $body) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.body_hint') }}</p>
                    @error('body')
                    <p class="text-xs text-red-600 mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                        @if(!$selectedTemplate) disabled @endif>
                        {{ __('letters.actions.save_letter') }}
                    </button>
                </div>
            </form>
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
                selector: '#letter-body-editor',
                height: 800,
                min_height: 800,
                max_height: 800
            });
        })();
    </script>

</x-admin-layout>
