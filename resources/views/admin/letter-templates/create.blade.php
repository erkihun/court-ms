{{-- resources/views/letter-templates/create.blade.php --}}
<x-admin-layout title="New Letter Template">
    @section('page_header','New Letter Template')

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Create Template</h2>
            <p class="text-sm text-gray-500 mb-4">Define the title, optional category, placeholders, and body content.</p>

            <form method="POST" action="{{ route('letter-templates.store') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @include('admin.letter-templates._form', ['template' => $template])
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('letter-templates.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
