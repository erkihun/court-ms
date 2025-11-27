<x-public-layout title="{{ $term->title }}">
    <div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-10">
        <h1 class="text-3xl font-semibold text-slate-900">{{ $term->title }}</h1>
        <p class="text-sm text-slate-500 mt-1">
            {{ optional($term->published_at)->format('M d, Y H:i') }}
        </p>
        <div class="mt-6 prose max-w-none text-slate-800 tiny-content">
            {!! clean($term->body, 'cases') !!}
        </div>
    </div>
</x-public-layout>
