<x-applicant-layout title="{{ __('respondent.edit_response') }}" :as-respondent-nav="true">

    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.edit_response') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('respondent.response_edit_description') }}</p>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">{{ __('cases.please_fix_errors') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('respondent.responses.update', $response) }}"
              enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-6">
            @csrf
            @method('PATCH')

            <div class="px-6 md:px-8 pt-6 pb-6 md:pb-8 space-y-6">

            {{-- Title --}}
            <div>
                <label for="resp_title" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('respondent.response_title_label') }} <span class="text-red-500">*</span>
                </label>
                <input id="resp_title" type="text" name="title" value="{{ old('title', $response->title) }}" required
                       autocomplete="off"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                              @error('title') border-red-400 bg-red-50 @enderror">
                @error('title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Case number --}}
            <div>
                <label for="resp_case_number" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('respondent.case_number_label') }}
                </label>
                <input id="resp_case_number" type="text" name="case_number"
                       value="{{ old('case_number', $response->case_number) }}"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                              @error('case_number') border-red-400 bg-red-50 @enderror">
                @error('case_number')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Main content (TinyMCE) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('respondent.description_label') }}
                </label>
                <textarea id="resp-description" name="description"
                          class="w-full rounded-xl border border-slate-300 text-sm @error('description') border-red-400 @enderror">{{ old('description', $response->description) }}</textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PDF upload --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('respondent.replace_pdf') }}
                </label>
                <div class="mt-1 flex items-center gap-3 rounded-xl border border-slate-300 px-4 py-3
                            @error('pdf') border-red-400 bg-red-50 @enderror">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <input type="file" name="pdf" accept="application/pdf"
                           class="text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100 cursor-pointer w-full">
                </div>
                @error('pdf')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <a href="{{ route('respondent.responses.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-300
                          text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-blue-600 text-white
                               text-sm font-semibold hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('respondent.submit_response') }}
                </button>
            </div>

            </div>{{-- end inner padded div --}}
        </form>
    </div>

    @push('head')
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
    @endpush

    @push('scripts')
    <script>
    (function () {
        const TINY_BASE = "{{ asset('vendor/tinymce') }}";
        tinymce.init({
            base_url: TINY_BASE,
            suffix: '.min',
            license_key: 'gpl',
            branding: false,
            promotion: false,
            selector: '#resp-description',
            menubar: false,
            plugins: 'lists link table code advlist charmap fullscreen',
            toolbar: [
                'undo redo | fontfamily fontsize | bold italic underline strikethrough removeformat',
                '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                '| numlist bullist outdent indent | fullscreen code'
            ].join(' '),
            toolbar_mode: 'wrap',
            toolbar_sticky: true,
            height: 420,
            min_height: 420,
            resize: false,
            statusbar: false,
            forced_root_block: 'p',
            forced_root_block_attrs: { style: 'text-align: justify;' },
            content_style: `
                body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; }
                table { width: 100%; border-collapse: collapse; }
                td, th { border: 1px solid #ddd; padding: 4px; }
                body { font-size: 14px; line-height: 1.6; }
            `,
            paste_postprocess(plugin, args) {
                args.node.querySelectorAll('p,div,li,td,th,blockquote').forEach(el => {
                    el.style.textAlign = 'justify';
                });
            },
            setup(editor) {
                editor.on('init', () => editor.execCommand('JustifyFull'));
            }
        });
    })();
    </script>
    @endpush

</x-applicant-layout>
