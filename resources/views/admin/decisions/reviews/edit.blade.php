{{-- resources/views/admin/decisions/reviews/edit.blade.php --}}
<x-admin-layout title="{{ __('decisions.reviews.edit_title') }}">
    @section('page_header', __('decisions.reviews.edit_title'))
    @include('admin.decisions.partials.font-style')

    <div class="decision-ethiopic-font max-w-3xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ __('decisions.reviews.edit_title') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('decisions.reviews.edit_subtitle', ['case' => $decision->case_number, 'reviewer' => $review->reviewer?->name ?? '—']) }}</p>
            </div>
            <a href="{{ route('decisions.show', $decision) }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('decisions.reviews.back') }}
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            <form method="POST" action="{{ route('decisions.reviews.update', [$decision, $review]) }}" class="space-y-4"
                x-data="{
                    outcome: '{{ old('outcome', $review->outcome) }}',
                    get needsNote() { return this.outcome === 'reject' || this.outcome === 'improve'; },
                    get notePlaceholder() {
                        return this.outcome === 'improve'
                            ? @js(__('decisions.reviews.note_placeholder_improve'))
                            : @js(__('decisions.reviews.note_placeholder_reject'));
                    }
                }">
                @csrf
                @method('PATCH')

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('decisions.reviews.outcome') }}</label>
                        <select name="outcome" x-model="outcome"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="approve" @selected(old('outcome', $review->outcome)==='approve')>{{ __('decisions.reviews.approve') }}</option>
                            <option value="reject" @selected(old('outcome', $review->outcome)==='reject')>{{ __('decisions.reviews.reject') }}</option>
                            <option value="improve" @selected(old('outcome', $review->outcome)==='improve')>{{ __('decisions.reviews.improve') }}</option>
                        </select>
                        @error('outcome') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('decisions.reviews.case_number') }}</label>
                        <input type="text" readonly value="{{ $decision->case_number }}"
                            class="mt-1 w-full px-3 py-2 rounded-lg bg-gray-50 text-gray-900 border border-gray-200">
                    </div>
                </div>

                <div x-show="needsNote" x-cloak>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ __('decisions.reviews.review_note') }}
                        <span class="text-red-500" x-show="needsNote">*</span>
                    </label>
                    <textarea name="review_note" rows="4" x-bind:required="needsNote"
                        x-bind:placeholder="notePlaceholder"
                        class="mt-1 w-full px-3 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">{{ old('review_note', $review->review_note) }}</textarea>
                    @error('review_note') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('decisions.show', $decision) }}"
                        class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200">
                        {{ __('decisions.reviews.cancel') }}
                    </a>
                    <button class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">
                        {{ __('decisions.reviews.update_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

