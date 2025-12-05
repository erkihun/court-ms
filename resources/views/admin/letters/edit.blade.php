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
                <p class="text-xs text-gray-500">
                    {{ __('letters.form.subject') }} prefix: {{ $letter->template->subject_prefix ?? __('letters.cards.missing') }} | {{ __('letters.table.reference') }}: {{ $letter->reference_number ?? __('letters.cards.missing') }}
                </p>
            </div>

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

                <div class="grid md:grid-cols-2 gap-4">
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

    @include('admin.letters._tinymce-script')
</x-admin-layout>
