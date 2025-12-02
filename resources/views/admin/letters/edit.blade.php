{{-- resources/views/letters/edit.blade.php --}}
<x-admin-layout title="Edit Letter">
    @section('page_header','Edit Letter')

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Edit Letter</h2>
            <p class="text-sm text-gray-500 mb-4">Update the recipient, subject, or body of the generated letter.</p>

            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                <p class="text-xs uppercase tracking-wide text-gray-500">Template</p>
                <p class="font-semibold text-gray-900">{{ $letter->template->title }}</p>
                <p class="text-xs text-gray-500">
                    Prefix: {{ $letter->template->subject_prefix ?? '—' }} · Reference: {{ $letter->reference_number }}
                </p>
            </div>

            <form method="POST" action="{{ route('letters.update', $letter) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Name<span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $letter->recipient_name) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                            required>
                        @error('recipient_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Title</label>
                        <input type="text" name="recipient_title" value="{{ old('recipient_title', $letter->recipient_title) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('recipient_title')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Company</label>
                        <input type="text" name="recipient_company" value="{{ old('recipient_company', $letter->recipient_company) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CC</label>
                        <input type="text" name="cc" value="{{ old('cc', $letter->cc) }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="e.g. Jane Smith">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject', $letter->subject) }}"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('subject')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Body<span class="text-red-500">*</span></label>
                    <textarea id="letter-body-editor" name="body" rows="12"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm" required>{{ old('body', $letter->body) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Changes are reflected immediately in the preview.</p>
                    @error('body')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('letters.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back</a>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Update Letter</button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.letters._tinymce-script')
</x-admin-layout>
