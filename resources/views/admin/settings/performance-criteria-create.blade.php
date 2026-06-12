<x-admin-layout title="{{ __('performance.settings.create_criterion_title') }}">
@section('page_header', __('performance.settings.create_criterion_title'))

<div class="mx-auto max-w-3xl px-5 py-6">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-[var(--text)]">{{ __('performance.settings.create_criterion_title') }}</h1>
            <p class="mt-1 text-sm text-[var(--text-subtle)]">{{ __('performance.settings.create_criterion_subtitle') }}</p>
        </div>
        <a href="{{ route('settings.performance-evaluation.index') }}"
            class="rounded-lg border border-[var(--border)] px-3 py-2 text-sm text-[var(--text-muted)] hover:bg-[var(--surface-soft)]">
            {{ __('performance.actions.back') }}
        </a>
    </div>

    @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('settings.performance-evaluation.criteria.store') }}"
        class="rounded-xl border border-[var(--border)] bg-[var(--surface-strong)] p-5 shadow-sm">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-[var(--text-muted)]">{{ __('performance.settings.fields.name') }}</label>
                <input name="name" value="{{ old('name') }}" required
                    class="mt-1 w-full rounded-lg border border-[var(--border)] bg-white px-3 py-2 text-sm text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text-muted)]">{{ __('performance.settings.fields.name_am') }}</label>
                <input name="name_am" value="{{ old('name_am') }}"
                    class="mt-1 w-full rounded-lg border border-[var(--border)] bg-white px-3 py-2 text-sm text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text-muted)]">{{ __('performance.settings.fields.category') }}</label>
                <select name="category" required
                    class="mt-1 w-full rounded-lg border border-[var(--border)] bg-white px-3 py-2 text-sm text-gray-900">
                    @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected(old('category') === $category->slug)>{{ $category->local_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text-muted)]">{{ __('performance.settings.fields.weight') }}</label>
                <input name="weight" type="number" min="0" max="100" value="{{ old('weight', 10) }}" required
                    class="mt-1 w-full rounded-lg border border-[var(--border)] bg-white px-3 py-2 text-sm text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text-muted)]">{{ __('performance.settings.fields.order') }}</label>
                <input name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}"
                    class="mt-1 w-full rounded-lg border border-[var(--border)] bg-white px-3 py-2 text-sm text-gray-900">
            </div>
            <label class="mt-6 inline-flex items-center gap-2 text-sm text-[var(--text-muted)]">
                <input type="checkbox" name="active" value="1" checked>
                {{ __('performance.settings.active') }}
            </label>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-[var(--text-muted)]">{{ __('performance.settings.fields.description') }}</label>
                <textarea name="description" rows="3"
                    class="mt-1 w-full rounded-lg border border-[var(--border)] bg-white px-3 py-2 text-sm text-gray-900">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="mt-5 flex justify-end gap-2">
            <a href="{{ route('settings.performance-evaluation.index') }}"
                class="rounded-lg border border-[var(--border)] px-4 py-2 text-sm text-[var(--text-muted)] hover:bg-[var(--surface-soft)]">
                {{ __('performance.actions.cancel') }}
            </a>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ __('performance.settings.actions.add_criterion') }}
            </button>
        </div>
    </form>
</div>
</x-admin-layout>
