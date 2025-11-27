<x-admin-layout title="Create Terms">
    <div class="max-w-4xl mx-auto bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">Create Terms &amp; Conditions</h1>
        <form method="POST" action="{{ route('terms.store') }}" class="space-y-6">
            @include('admin.terms._form', ['term' => null])
            <div class="flex justify-end gap-3">
                <a href="{{ route('terms.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">Cancel</a>
                <button class="px-4 py-2 rounded-md bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600">
                    Save
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
