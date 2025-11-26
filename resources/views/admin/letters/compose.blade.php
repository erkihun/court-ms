{{-- resources/views/letters/compose.blade.php --}}
<x-admin-layout title="Compose Letter">
    @section('page_header','Compose Letter')

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Select Template</h2>
            <p class="text-sm text-gray-500 mb-4">Choose a template to auto-fill the body. You can edit the letter before generating the final copy.</p>

            <form method="GET" action="{{ route('letters.compose') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Template</label>
                    <select name="template_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" onchange="this.form.submit()">
                        <option value="">-- Select --</option>
                        @foreach($templates as $template)
                        <option value="{{ $template->id }}" @selected(optional($selectedTemplate)->id === $template->id)>
                            {{ $template->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Load</button>
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('letters.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="template_id" value="{{ optional($selectedTemplate)->id }}">

                @if(!$selectedTemplate)
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Select a template above to enable the letter fields.
                </div>
                @endif

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Name<span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_name" value="{{ $recipientName }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Title</label>
                        <input type="text" name="recipient_title" value="{{ $recipientTitle }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recipient Company</label>
                        <input type="text" name="recipient_company" value="{{ $recipientCompany }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CC</label>
                        <input type="text" name="cc" value="{{ $cc }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="e.g. Jane Smith">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" value="{{ $subject ?? '' }}"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>

                @if($selectedTemplate && $selectedTemplate->placeholders)
                <div class="rounded-lg border border-dashed border-blue-300 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                    <p class="font-semibold mb-1">Placeholders</p>
                    <p>Use the following keys inside your letter body:</p>
                    <p class="mt-1">{{ implode(', ', $selectedTemplate->placeholders) }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Body<span class="text-red-500">*</span></label>
                    <textarea name="body" rows="12" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm" required>{{ old('body', $body) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">The preview page will render this on A4 paper with the template header/footer.</p>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                        @if(!$selectedTemplate) disabled @endif>
                        Save Letter
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
