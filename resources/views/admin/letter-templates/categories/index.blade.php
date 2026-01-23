{{-- resources/views/admin/letter-templates/categories/index.blade.php --}}
@php
$canCreateCategory = function_exists('userHasPermission')
    ? userHasPermission('letters.templet.create')
    : (auth()->user()?->hasPermission('letters.templet.create') ?? false);
$canUpdateCategory = function_exists('userHasPermission')
    ? userHasPermission('letters.templet.update')
    : (auth()->user()?->hasPermission('letters.templet.update') ?? false);
$canDeleteCategory = function_exists('userHasPermission')
    ? userHasPermission('letters.templet.delete')
    : (auth()->user()?->hasPermission('letters.templet.delete') ?? false);
@endphp

<x-admin-layout title="Letter categories">
    @section('page_header', 'Letter categories')

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Letter categories</h2>
                <p class="text-sm text-gray-500">Manage the categories used by letter templates.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('letter-templates.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    Back to templates
                </a>
                @if($canCreateCategory)
                <a href="{{ route('letter-categories.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New category
                </a>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @if($categories->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">
                No categories yet.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $category->description ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if($canUpdateCategory)
                                    <a href="{{ route('letter-categories.edit', $category) }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                        Edit
                                    </a>
                                    @endif
                                    @if($canDeleteCategory)
                                    <form method="POST" action="{{ route('letter-categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this category?')"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">{!! $categories->links() !!}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
