<x-applicant-layout title="{{ __('cases.submit_case') }}">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900">
            {{ __('cases.submit_case') }}
        </h1>

    </div>

    {{-- Top-level validation errors --}}
    @if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3  text-red-700">
        <div class="font-semibold mb-1 flex items-center gap-2">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M12 9v3m0 3h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
            </svg>
            <span>{{ __('cases.please_fix_errors') }}</span>
        </div>
        <ul class="list-disc list-inside mt-1 space-y-0.5">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $applicantUser = auth('applicant')->user();
        $isLawyer = (bool) ($applicantUser?->is_lawyer);
    @endphp

    <form id="applicant-case-create-form" method="POST"
        action="{{ route('applicant.cases.store') }}"
        enctype="multipart/form-data"
        class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-8">
        @csrf
        <input type="hidden" name="filing_date" id="filing_date_field" value="{{ old('filing_date', now()->toDateString()) }}">

        {{-- Basic meta --}}
        <section class="space-y-4">

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block  font-medium text-slate-700">
                        {{ __('cases.applicant_name') }} <span class="text-red-600">*</span>
                    </label>
                    <input
                        name="title"
                        value="{{ $isLawyer ? old('title') : old('title', $applicantUser->full_name ?? $applicantUser->name ?? '') }}"
                        placeholder="{{ __('cases.applicant_name_placeholder') }}"
                        @unless($isLawyer) readonly @endunless
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                               {{ $isLawyer ? 'bg-white' : 'bg-slate-100' }} focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    @error('title')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block  font-medium text-slate-700">
                        {{ __('cases.applicant_address') }} <span class="text-red-600">*</span>
                    </label>
                    <input
                        name="applicant_address"
                        value="{{ $isLawyer ? old('applicant_address') : old('applicant_address', $applicantUser->address ?? '') }}"
                        placeholder="{{ __('cases.applicant_address_placeholder') }}"
                        @unless($isLawyer) readonly @endunless
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                               {{ $isLawyer ? 'bg-white' : 'bg-slate-100' }} focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    @error('applicant_address')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Respondent --}}
        <section class="space-y-4">

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block  font-medium text-slate-700">
                        {{ __('cases.respondent_name') }} <span class="text-red-600">*</span>
                    </label>
                    <input
                        name="respondent_name"
                        value="{{ old('respondent_name') }}"
                        placeholder="{{ __('cases.respondent_name_placeholder') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    @error('respondent_name')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block  font-medium text-slate-700">
                        {{ __('cases.respondent_address') }} <span class="text-red-600">*</span>
                    </label>
                    <input
                        name="respondent_address"
                        value="{{ old('respondent_address') }}"
                        placeholder="{{ __('cases.respondent_address_placeholder') }}"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    @error('respondent_address')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Type / Court --}}
        <section class="space-y-4" x-data="{
            prefix: '',
            updatePrefix() {
                const sel = $refs.caseTypeSelect;
                const opt = sel.options[sel.selectedIndex];
                const pref = opt?.dataset?.prefix || '';
                const cleaned = (pref.match(/[A-Za-z0-9]+/g) || []).join('');
                this.prefix = (cleaned.slice(0,4) || 'CASE').toUpperCase();
            }
        }" x-init="updatePrefix()">

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block  font-medium text-slate-700">
                        {{ __('cases.case_type') }} <span class="text-red-600">*</span>
                    </label>
                    <select
                        x-ref="caseTypeSelect"
                        @change="updatePrefix()"
                        name="case_type_id"
                        required
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        <option value="">-- {{ __('cases.select_option') }} --</option>
                        @foreach($types as $t)
                        <option value="{{ $t->id }}" data-prefix="{{ $t->prefix ?? $t->name }}" @selected(old('case_type_id')==$t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('case_type_id')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                  
                </div>
            </div>
        </section>

        {{-- Case details (Word-like editor) --}}
        <section class="space-y-2">
            <div class="flex items-center justify-between gap-2">
                <label class="block  font-medium text-slate-700">
                    {{ __('cases.case_details') }} <span class="text-red-600">*</span>
                </label>
            </div>
            <textarea
                id="editor-description"
                name="description"
                rows="16"
                class="mt-1 w-full rounded-lg border border-slate-300  text-slate-900">{{ old('description') }}</textarea>
            @error('description')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </section>

        {{-- Relief (Word-like editor) --}}
        <section class="space-y-2">
            <div class="flex items-center justify-between gap-2">
                <label class="block  font-medium text-slate-700">
                    {{ __('cases.relief_requested') }} <span class="text-red-600">*</span>
                </label>
            </div>
            <textarea
                id="editor-relief"
                name="relief_requested"
                rows="12"
                class="mt-1 w-full rounded-lg border border-slate-300  text-slate-900">{{ old('relief_requested') }}</textarea>
            @error('relief_requested')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror

            <label class="flex items-start gap-2  text-slate-700 mt-3">
                <input type="checkbox" name="certify_appeal" value="1"
                    class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    {{ old('certify_appeal') ? 'checked' : '' }} required>
                <span>I certify the validity of my appeal in accordance with F/S/S/No. 92.</span>
            </label>
            @error('certify_appeal')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </section>

        {{-- Documents --}}
        <section x-data="{ rows: 1 }" class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 md:p-5 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class=" font-semibold text-slate-800">
                        {{ __('cases.evidence_documents') }} <span class="text-red-600">*</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('cases.file_requirements') }}
                    </p>
                </div>
                <button type="button" @click="rows++"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md bg-orange-500 text-white font-medium hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('cases.add_document') }}
                </button>
            </div>

            <template x-for="i in rows" :key="i">
                <div class="grid md:grid-cols-2 gap-3 mb-2">
                    <input
                        type="text"
                        name="evidence_titles[]"
                        placeholder="{{ __('cases.document_title_placeholder') }}"
                        required
                        class="px-3 py-2.5 rounded-lg border border-slate-300  text-slate-900
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    <input
                        type="file"
                        name="evidence_files[]"
                        accept="application/pdf"
                        required
                        class="px-3 py-2.5 rounded-lg border border-slate-300  bg-white text-slate-900
                               focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </template>

            @error('evidence_files.*')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </section>

        @php($oldWitnessCount = max(1, count((array) old('witnesses', []))))

        {{-- Witnesses --}}
        <section x-data="{ rows: {{ $oldWitnessCount }} }" class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 md:p-5 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class=" font-semibold text-slate-800">
                        {{ __('cases.witnesses_section.title') }} <span class="text-red-600">*</span>
                    </h3>

                </div>
                <button type="button" @click="rows++"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md bg-orange-500 text-white font-medium hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('cases.add_witness') }}
                </button>
            </div>

            <template x-for="i in rows" :key="'w'+i">
                <div class="grid md:grid-cols-6 gap-3 mb-2">
                    <input
                        :name="'witnesses['+(i-1)+'][full_name]'"
                        required
                        value="{{ old('witnesses.0.full_name') }}"
                        placeholder="{{ __('cases.witnesses_section.full_name_placeholder') }}"
                        class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900 md:col-span-2
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">

                    <input
                        :name="'witnesses['+(i-1)+'][phone]'"
                        required
                        value="{{ old('witnesses.0.phone') }}"
                        placeholder="{{ __('cases.witnesses_section.phone_placeholder') }}"
                        class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">

                    <input
                        :name="'witnesses['+(i-1)+'][address]'"
                        required
                        value="{{ old('witnesses.0.address') }}"
                        placeholder="{{ __('cases.witnesses_section.address_placeholder') }}"
                        class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900 md:col-span-2
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                </div>
            </template>

            @error('witnesses.*.full_name')
            <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
            @enderror
            @error('witnesses_duplicate_phone')
            <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
            @enderror
        </section>

        <label class="flex items-start gap-2  text-slate-700">
            <input type="checkbox" name="certify_evidence" value="1"
                class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                {{ old('certify_evidence') ? 'checked' : '' }} required>
            <span>I certify that the evidence I have presented is true in accordance with F.S./S.H./No. 92.</span>
        </label>
        @error('certify_evidence')
        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
        @enderror

        @if(!empty($activeTerms))
        <section x-data="{ showTerms: false }" class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 md:p-5 space-y-3 relative">
            <div>
                <h3 class=" font-semibold text-slate-800">
                    {{ __('terms.applicant_card_title') }}
                </h3>
                <p class="text-xs text-slate-600 mt-1">
                    {{ __('terms.applicant_card_help') }}
                    <button type="button" @click="showTerms = true"
                        class="text-blue-600 hover:underline font-medium">
                        {{ __('terms.applicant_view_full') }}
                    </button>
                </p>
            </div>
            <label class="flex items-start gap-2  text-slate-700">
                <input type="checkbox" name="accept_terms" value="1"
                    class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    {{ old('accept_terms') ? 'checked' : '' }} required>
                <span>{{ __('terms.applicant_checkbox') }}</span>
            </label>
            @error('accept_terms')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror

            <template x-if="showTerms">
                <div x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @keydown.escape.window="showTerms=false">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[80vh] overflow-y-auto p-6 relative">
                        <button type="button" class="absolute top-3 right-3 text-slate-500 hover:text-slate-700"
                            @click="showTerms=false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <h2 class="text-xl font-semibold text-slate-900 mb-2">{{ $activeTerms->title }}</h2>
                        <p class="text-xs text-slate-500 mb-4">
                            {{ \App\Support\EthiopianDate::format($activeTerms->published_at, withTime: true) }}
                        </p>
                        <div class=" text-slate-800 whitespace-pre-line leading-relaxed tiny-content">
                            {!! clean(nl2br(e($activeTerms->body)), 'cases') !!}
                        </div>
                        <div class="mt-4 text-right">
                            <button type="button" @click="showTerms=false"
                                class="inline-flex items-center px-4 py-2 rounded-md bg-orange-500 text-white  font-semibold hover:bg-orange-600">
                                {{ __('terms.modal_close') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </section>
        @endif

        {{-- Actions --}}
        <div class="pt-2 flex flex-wrap gap-3">
            <button
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-orange-500 text-white  font-semibold
                       hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                {{ __('cases.submit_case_button') }}
            </button>
            <a href="{{ route('applicant.cases.index') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-slate-200 text-slate-700  font-medium
                      hover:bg-slate-300 focus:outline-none focus:ring-1 focus:ring-slate-400">
                {{ __('cases.cancel') }}
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('applicant-case-create-form');
            const filingInput = document.getElementById('filing_date_field');
            if (form && filingInput) {
                form.addEventListener('submit', function() {
                    filingInput.value = new Date().toISOString().split('T')[0];
                });
            }
        });
    </script>

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

                plugins: 'lists link table code image advlist charmap fullscreen',
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

            // CASE DETAILS — taller
            tinymce.init({
                ...common,
                selector: '#editor-description',
                height: 520,
                min_height: 520,
                max_height: 520
            });

            // RELIEF — shorter
            tinymce.init({
                ...common,
                selector: '#editor-relief',
                height: 380,
                min_height: 380,
                max_height: 380
            });
        })();
    </script>
</x-applicant-layout>
