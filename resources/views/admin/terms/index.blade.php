@php use Illuminate\Support\Str; @endphp

<x-admin-layout title="{{ __('terms.title') }}">
    @section('page_header', __('terms.title'))

    @php
        $publishedOnPage = $terms->where('is_published', true)->count();
        $draftsOnPage = $terms->count() - $publishedOnPage;
    @endphp

    <div class="space-y-6">
        @if(session('success'))
        <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
            <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div class="flex-1">
                {{ session('success') }}
            </div>
        </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">Policy controls</p>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('terms.title') }}</h1>
                    <p class="text-sm text-gray-600 max-w-2xl">{{ __('terms.manage_description') }}</p>
                    <p class="text-sm text-gray-500 max-w-2xl">Keep your latest policies clear and easy to find. Published entries are what users will see on the public terms page.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border border-orange-100 bg-orange-50 px-4 py-3 text-orange-800">
                            <div class="text-xs uppercase tracking-wide text-orange-600">On this page</div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-semibold">Published</span>
                                <span class="text-lg font-bold">{{ $publishedOnPage }}</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500">On this page</div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-semibold">Drafts</span>
                                <span class="text-lg font-bold">{{ $draftsOnPage }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('public.terms') }}" target="_blank" rel="noreferrer"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-800 text-sm font-semibold hover:bg-gray-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0-7L10 14m0 0H3m7 0v7" />
                        </svg>
                        Preview public page
                    </a>
                    <a href="{{ route('terms.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold shadow-sm hover:bg-orange-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" />
                        </svg>
                        {{ __('terms.create_new') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if($terms->isEmpty())
            <div class="p-10 text-center space-y-3">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 text-orange-500 border border-orange-100">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                    </svg>
                </div>
                <div class="text-lg font-semibold text-gray-900">{{ __('terms.empty_state') }}</div>
                <p class="text-sm text-gray-600">Start by adding your first set of terms and conditions.</p>
                <div>
                    <a href="{{ route('terms.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold shadow-sm hover:bg-orange-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" />
                        </svg>
                        {{ __('terms.create_new') }}
                    </a>
                </div>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('terms.table_title') }}</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('terms.table_published') }}</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('terms.table_updated') }}</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700">{{ __('terms.table_actions') ?? 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($terms as $term)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm font-semibold text-gray-900">{{ $term->title }}</div>
                                    <p class="text-xs text-gray-600 leading-relaxed">
                                        {{ Str::limit(strip_tags($term->body), 120) }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($term->is_published)
                                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    {{ __('terms.status_published') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ optional($term->published_at)->format('M d, Y H:i') }}
                                </div>
                                @else
                                <div class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                    {{ __('terms.status_draft') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Last edited {{ $term->updated_at->diffForHumans() }}
                                </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top text-gray-700">
                                <div class="text-sm font-medium text-gray-900">{{ $term->updated_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $term->updated_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('terms.edit', $term) }}"
                                        class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                        {{ __('terms.action_edit') }}
                                    </a>
                                    <form action="{{ route('terms.destroy', $term) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ __('terms.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            {{ __('terms.action_delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                {{ $terms->links() }}
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
