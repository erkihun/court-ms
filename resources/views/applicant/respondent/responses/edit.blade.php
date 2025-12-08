<x-applicant-layout title="{{ __('respondent.edit_response') }}" :as-respondent-nav="true">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow p-6">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-slate-900">{{ __('respondent.edit_response') }}</h1>
            <p class="text-sm text-slate-600">{{ __('respondent.response_edit_description') }}</p>
        </div>

        <form method="POST" action="{{ route('respondent.responses.update', $response) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('respondent.response_title_label') }}</label>
                <input type="text" name="title" value="{{ old('title', $response->title) }}" required
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('respondent.description_label') }}</label>
                <textarea name="description" rows="4"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">{{ old('description', $response->description) }}</textarea>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('respondent.case_number_label') }}</label>
                <input type="text" name="case_number" value="{{ old('case_number', $response->case_number) }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                @error('case_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('respondent.replace_pdf') }}</label>
                <input type="file" name="pdf" accept="application/pdf"
                    class="mt-1 w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('pdf') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="text-right flex flex-wrap gap-2 justify-end">
                <a href="{{ route('respondent.responses.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    {{ __('respondent.back_to_responses') }}
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                    {{ __('respondent.submit_response') }}
                </button>
            </div>
        </form>
    </div>
</x-applicant-layout>
