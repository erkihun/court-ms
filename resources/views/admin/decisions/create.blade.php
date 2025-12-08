{{-- resources/views/admin/decisions/create.blade.php --}}
<x-admin-layout title="{{ __('decisions.create.title') }}">
    @section('page_header', __('decisions.create.title'))

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ __('decisions.create.title') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('decisions.create.subtitle') }}</p>
            </div>

            <a href="{{ route('decisions.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('decisions.create.back') }}
            </a>
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('decisions.store') }}"
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            @csrf

            <div class="grid gap-4">

                <!-- Case + Date -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Case Field -->
                    <div>
                        <label for="decision-case-select" class="block text-sm font-semibold text-gray-800">
                            {{ __('decisions.fields.case') }}
                        </label>

                        <div class="relative mt-1">
                            <select name="case_id" id="decision-case-select"
                                class="w-full appearance-none px-3 py-2.5 rounded-lg bg-white text-gray-900 text-sm 
                                    border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 
                                    cursor-pointer transition hover:border-gray-400">

                                <option value="">{{ __('decisions.fields.select_case') }}</option>

                                @foreach($cases as $case)
                                <option value="{{ $case->id }}"
                                    data-applicant="{{ $case->applicant?->full_name ?? '' }}"
                                    data-respondent="{{ $case->respondent_name ?? '' }}"
                                    @selected(old('case_id')==$case->id)>
                                    {{ $case->case_number }} — {{ \Illuminate\Support\Str::limit($case->title, 60) }}
                                </option>
                                @endforeach
                            </select>

                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </span>
                        </div>

                        @error('case_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Decision Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('decisions.fields.decision_date') }}
                        </label>
                        <input type="date" name="decision_date"
                            value="{{ old('decision_date', now()->toDateString()) }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300
                                      focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        @error('decision_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Applicant / Respondent -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Applicant -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('decisions.fields.applicant_name') }}
                        </label>
                        <input id="applicant-name-display" type="text" readonly
                            value="{{ old('applicant_full_name', '') }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-200">
                        <input type="hidden" name="applicant_full_name" id="applicant-name-hidden"
                            value="{{ old('applicant_full_name', '') }}">
                    </div>

                    <!-- Respondent -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('decisions.fields.respondent_name') }}
                        </label>
                        <input id="respondent-name-display" type="text" readonly
                            value="{{ old('respondent_full_name', '') }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-200">
                        <input type="hidden" name="respondent_full_name" id="respondent-name-hidden"
                            value="{{ old('respondent_full_name', '') }}">
                    </div>
                </div>

                <!-- Reviewer -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('decisions.fields.reviewer') }}
                        </label>

                        <div class="relative mt-1">
                            <select name="reviewing_admin_user_id"
                                class="w-full appearance-none px-3 py-2.5 rounded-lg bg-white text-gray-900 text-sm
                                    border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200
                                    cursor-pointer transition hover:border-gray-400">

                                <option value="">{{ __('decisions.fields.select_reviewer') }}</option>

                                @foreach($adminUsers as $admin)
                                <option value="{{ $admin->id }}" @selected(old('reviewing_admin_user_id')==$admin->id)>
                                    {{ $admin->name }}
                                </option>
                                @endforeach
                            </select>

                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </span>
                        </div>

                        @error('reviewing_admin_user_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Decision Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ __('decisions.fields.decision_content') }}
                    </label>

                    <textarea id="decision-content-editor" name="decision_content" rows="12"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm">{{ old('decision_content') }}</textarea>


                    @error('decision_content')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ __('decisions.fields.status') }}
                    </label>

                    <select name="status"
                        class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300
                                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <option value="draft" @selected(old('status')==='draft' )>{{ __('decisions.status.draft') }}</option>
                        <option value="active" @selected(old('status')==='active' )>{{ __('decisions.status.active') }}</option>
                        <option value="archived" @selected(old('status')==='archived' )>{{ __('decisions.status.archived') }}</option>
                    </select>

                    @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Sticky Footer Buttons -->
            <div class="flex items-center justify-end gap-2 pt-4 sticky bottom-0 bg-white border-t py-3">
                <a href="{{ route('decisions.index') }}"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200">
                    {{ __('decisions.create.cancel') }}
                </a>

                <button class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                    {{ __('decisions.create.save') }}
                </button>
            </div>
        </form>
    </div>

    <!-- TinyMCE -->
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
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
                toolbar_mode: 'wrap',
                toolbar_sticky: false, // FIXED: prevents hiding the Save button
                plugins: 'lists link table code image advlist charmap fullscreen wordcount',
                toolbar: [
                    'undo redo | fontfamily fontsize | bold italic underline strikethrough removeformat',
                    '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                    '| numlist bullist outdent indent | fullscreen code'
                ].join(' '),
                forced_root_block: 'p',
                forced_root_block_attrs: {
                    style: 'text-align: justify;'
                },
                content_style: `
                    body, p, div, li, td, th, blockquote {
                        text-align: justify; text-justify: inter-word;
                    }
                    table { width: 100%; border-collapse: collapse }
                    td, th { border: 1px solid #ddd; padding: 4px }
                    body { font-size: 14px; line-height: 1.5 }
                `,
                resize: false,
                statusbar: true
            };

            tinymce.init({
                ...common,
                selector: '#decision-content-editor',
                height: 400
            });
            const form = document.querySelector("form");

            form.addEventListener("submit", function(e) {
                const content = tinymce.get("decision-content-editor").getContent({
                    format: "text"
                }).trim();

                if (!content) {
                    e.preventDefault();
                    alert("Please write a decision content before saving.");
                    tinymce.get("decision-content-editor").focus();
                }
            });

            // Autofill applicant/respondent on case selection
            const caseSelect = document.getElementById('decision-case-select');
            const applicantDisplay = document.getElementById('applicant-name-display');
            const respondentDisplay = document.getElementById('respondent-name-display');
            const applicantHidden = document.getElementById('applicant-name-hidden');
            const respondentHidden = document.getElementById('respondent-name-hidden');

            const updatePartyFields = () => {
                const selected = caseSelect?.selectedOptions[0];
                const applicant = selected?.dataset.applicant ?? '';
                const respondent = selected?.dataset.respondent ?? '';
                applicantDisplay.value = applicant;
                respondentDisplay.value = respondent;
                applicantHidden.value = applicant;
                respondentHidden.value = respondent;
            };

            updatePartyFields();
            caseSelect?.addEventListener('change', updatePartyFields);

        });
    </script>
</x-admin-layout>