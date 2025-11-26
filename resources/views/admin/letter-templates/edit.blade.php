{{-- resources/views/letter-templates/edit.blade.php --}}
<x-admin-layout title="Edit Letter Template">
    @section('page_header','Edit Letter Template')

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Edit Template</h2>
            <p class="text-sm text-gray-500 mb-4">Update the template details below.</p>

            <form method="POST" action="{{ route('letter-templates.update', $template) }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.letter-templates._form', ['template' => $template])
                <div class="flex items-center justify-between">
                    <a href="{{ route('letter-templates.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back</a>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
