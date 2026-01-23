{{-- resources/views/admin/letter-templates/categories/create.blade.php --}}
<x-admin-layout title="New letter category">
    @section('page_header', 'New letter category')

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Create category</h2>
            <p class="text-sm text-gray-500 mb-4">Add a category to organize letter templates.</p>

            <form method="POST" action="{{ route('letter-categories.store') }}" class="space-y-6">
                @csrf
                @include('admin.letter-templates.categories._form', ['category' => $category])
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('letter-categories.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Save category</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
