{{-- resources/views/letters/edit.blade.php --}}
<x-admin-layout title="{{ __('letters.titles.edit') }}">
    @section('page_header', __('letters.titles.edit'))

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('letters.titles.edit') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('letters.description.edit') }}</p>

            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('letters.form.template_label') }}</p>
                <p class="font-semibold text-gray-900">{{ $letter->template->title }}</p>
                @php
                $nextSeq = ($letter->template->reference_sequence ?? 0) + 1;
                $nextReference = implode('/', array_filter([
                    $letter->template->subject_prefix,
                    str_pad($nextSeq, 4, '0', STR_PAD_LEFT),
                ]));
                @endphp
                <p class="text-xs text-gray-500">
                    {{ __('letters.table.reference') }}: {{ $letter->reference_number ?? __('letters.cards.missing') }}<br>
                    {{ __('letters.form.subject') }} prefix: {{ $letter->template->subject_prefix ?? __('letters.cards.missing') }}<br>
                    {{ __('letters.table.reference') }} (next): {{ $nextReference }}
                </p>
            </div>

            @php
            $sendToApplicant = filter_var(old('send_to_applicant', $letter->send_to_applicant ?? true), FILTER_VALIDATE_BOOLEAN);
            $sendToRespondent = filter_var(old('send_to_respondent', $letter->send_to_respondent ?? true), FILTER_VALIDATE_BOOLEAN);
            @endphp

            <form method="POST" action="{{ route('letters.update', $letter) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.recipient_name') }}<span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $letter->recipient_name) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                            required>
                        @error('recipient_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.recipient_title') }}</label>
                        <input type="text" name="recipient_title" value="{{ old('recipient_title', $letter->recipient_title) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('recipient_title')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.recipient_company') }}</label>
                        <input type="text" name="recipient_company" value="{{ old('recipient_company', $letter->recipient_company) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.cc') }}</label>
                        <input type="text" name="cc" value="{{ old('cc', $letter->cc) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="{{ __('letters.form.cc_placeholder') }}">
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
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Approved By (Name)</label>
                        <input type="text" name="approved_by_name" value="{{ old('approved_by_name', $letter->approved_by_name) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Approved By (Title)</label>
                        <input type="text" name="approved_by_title" value="{{ old('approved_by_title', $letter->approved_by_title) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.subject') }}</label>
                    <input type="text" name="subject" value="{{ old('subject', $letter->subject) }}"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('subject')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('letters.form.body') }}<span class="text-red-500">*</span></label>
                    <textarea id="letter-body-editor" name="body" rows="12"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm" required>{{ old('body', $letter->body) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('letters.form.body_preview_hint') }}</p>
                    @error('body')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('letters.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">{{ __('letters.actions.back') }}</a>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">{{ __('letters.actions.update_letter') }}</button>
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
