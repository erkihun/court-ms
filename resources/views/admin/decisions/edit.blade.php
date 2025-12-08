{{-- resources/views/admin/decisions/edit.blade.php --}}
<x-admin-layout title="{{ __('decisions.edit.title') }}">
    @section('page_header', __('decisions.edit.title'))

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ __('decisions.edit.title') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('decisions.edit.subtitle', ['name' => $decision->name ?? '']) }}</p>
            </div>
            <a href="{{ route('decisions.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('decisions.edit.back') }}
            </a>
        </div>

        @php
        $reviewersDefault = old('reviewing_admin_user_names', $decision->reviewing_admin_user_names ?? [$decision->reviewing_admin_user_name ?? auth()->user()?->name ?? '']);
        @endphp

        <form method="POST" action="{{ route('decisions.update', $decision) }}" class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            @csrf
            @method('PATCH')
            <div class="grid gap-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Case</label>
                        <select name="case_id"
                            id="decision-case-select"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            <option value="">Select a case</option>
                            @foreach($cases as $case)
                            <option value="{{ $case->id }}"
                                data-applicant="{{ $case->applicant?->full_name ?? '' }}"
                                data-respondent="{{ $case->respondent_name ?? '' }}"
                                @selected(old('case_id', $decision->court_case_id)==$case->id)>
                                {{ $case->case_number }} - {{ \Illuminate\Support\Str::limit($case->title, 60) }}
                            </option>
                            @endforeach
                        </select>
                        @error('case_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('decisions.fields.name') }}</label>
                        <input name="name" value="{{ old('name', $decision->name) }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Applicant name</label>
                        <input id="applicant-name-display" type="text" readonly
                            value="{{ old('applicant_full_name', $decision->applicant_full_name ?? '') }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <input type="hidden" name="applicant_full_name" id="applicant-name-hidden" value="{{ old('applicant_full_name', $decision->applicant_full_name ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Respondent name</label>
                        <input id="respondent-name-display" type="text" readonly
                            value="{{ old('respondent_full_name', $decision->respondent_full_name ?? '') }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <input type="hidden" name="respondent_full_name" id="respondent-name-hidden" value="{{ old('respondent_full_name', $decision->respondent_full_name ?? '') }}">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Decision date</label>
                        <input type="date" name="decision_date"
                            value="{{ old('decision_date', optional($decision->decision_date)->format('Y-m-d')) }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        @error('decision_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reviewers</label>
                        <select name="reviewing_admin_user_names[]" multiple
                            class="mt-1 w-full min-h-[120px] rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            @foreach($adminUsers as $admin)
                            <option value="{{ $admin->name }}"
                                @selected(in_array($admin->name, $reviewersDefault, true))>
                                {{ $admin->name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Use Ctrl/Cmd+click to select multiple reviewers.</p>
                        @error('reviewing_admin_user_names') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Decision content</label>
                    <textarea id="decision-content-editor" name="decision_content" rows="6"
                        class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">{{ old('decision_content', $decision->decision_content) }}</textarea>
                    @error('decision_content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('decisions.fields.status') }}</label>
                    <select name="status"
                        class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <option value="draft" @selected(old('status', $decision->status)=='draft')>{{ __('decisions.status.draft') }}</option>
                        <option value="active" @selected(old('status', $decision->status)=='active')>{{ __('decisions.status.active') }}</option>
                        <option value="archived" @selected(old('status', $decision->status)=='archived')>{{ __('decisions.status.archived') }}</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('decisions.index') }}"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200">
                    {{ __('decisions.edit.cancel') }}
                </a>
                <button class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">
                    {{ __('decisions.edit.save') }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>

@push('scripts')
@once
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
@endonce
<script>
    document.addEventListener('DOMContentLoaded', () => {
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

        tinymce.init({
            ...common,
            selector: '#decision-content-editor',
            height: 800,
            min_height: 800,
            max_height: 800
        });

        const caseSelect = document.getElementById('decision-case-select');
        const applicantDisplay = document.getElementById('applicant-name-display');
        const respondentDisplay = document.getElementById('respondent-name-display');
        const applicantHidden = document.getElementById('applicant-name-hidden');
        const respondentHidden = document.getElementById('respondent-name-hidden');

        const updatePartyFields = () => {
            if (!caseSelect) return;
            const selected = caseSelect.selectedOptions[0];
            const applicant = selected ? selected.dataset.applicant ?? '' : '';
            const respondent = selected ? selected.dataset.respondent ?? '' : '';
            applicantDisplay.value = applicant;
            respondentDisplay.value = respondent;
            applicantHidden.value = applicant;
            respondentHidden.value = respondent;
        };

        updatePartyFields();
        caseSelect?.addEventListener('change', updatePartyFields);
    });
</script>
@endpush
