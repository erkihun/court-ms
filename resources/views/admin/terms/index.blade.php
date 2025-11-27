<x-admin-layout title="Terms">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Terms &amp; Conditions</h1>
            <p class="text-sm text-gray-600">Manage the Terms applicants must accept.</p>
        </div>
        <a href="{{ route('terms.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600">
            Create New
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-3 py-2 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Title</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Published</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Updated</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($terms as $term)
                <tr>
                    <td class="px-4 py-2 font-medium text-gray-900">{{ $term->title }}</td>
                    <td class="px-4 py-2 text-gray-700">
                        @if($term->is_published)
                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Published</span>
                        <div class="text-xs text-gray-500 mt-0.5">{{ optional($term->published_at)->format('M d, Y H:i') }}</div>
                        @else
                        <span class="text-xs text-gray-500">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-gray-700">{{ $term->updated_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <a href="{{ route('terms.edit', $term) }}" class="text-blue-600 text-sm hover:underline">Edit</a>
                        <form action="{{ route('terms.destroy', $term) }}" method="POST" class="inline"
                            onsubmit="return confirm('Delete this Terms & Conditions entry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No terms created yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3">
            {{ $terms->links() }}
        </div>
    </div>
</x-admin-layout>
