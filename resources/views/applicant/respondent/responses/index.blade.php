@php use Illuminate\Support\Str; @endphp

<x-applicant-layout title="{{ __('respondent.responses') }}" :as-respondent-nav="true">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-slate-500">{{ __('respondent.responses') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('respondent.responses') }}</h1>
            </div>
            <a href="{{ route('respondent.responses.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-700 text-white text-sm font-semibold hover:bg-blue-800">
                {{ __('respondent.create_response') }}
            </a>
        </div>

        @if($responses->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 bg-white p-6 text-center text-slate-600">
            {{ __('respondent.no_responses') }}
        </div>
        @else
        <div class="space-y-4">
            @foreach($responses as $response)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('respondent.responses') }}</p>
                        <h2 class="text-xl font-semibold text-slate-900">{{ $response->title }}</h2>
                        @if($response->case_number)
                        <p class="text-xs text-slate-500">{{ __('respondent.case_number_label') }}: {{ $response->case_number }}</p>
                        @endif
                        <p class="text-sm text-slate-600 mt-2">{{ Str::limit($response->description, 120) }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <a href="{{ route('respondent.responses.show', $response) }}"
                            class="text-sm text-blue-700 hover:underline">{{ __('respondent.view_details') }}</a>
                        <a href="{{ route('respondent.responses.edit', $response) }}"
                            class="text-sm text-blue-700 hover:underline">{{ __('respondent.edit_response') }}</a>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                    <span>{{ __('respondent.uploaded') }} {{ optional($response->created_at)->format('M d, Y') }}</span>
                    @if($response->pdf_path)
                    <span>{{ __('respondent.pdf_submitted') }}</span>
                    @endif
                    <form method="POST" action="{{ route('respondent.responses.destroy', $response) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="text-red-600 hover:text-red-800 text-xs font-semibold"
                            onclick="return confirm('{{ __('respondent.delete') }}')">
                            {{ __('respondent.delete') }}
                        </button>
                    </form>
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </div>
</x-applicant-layout>
