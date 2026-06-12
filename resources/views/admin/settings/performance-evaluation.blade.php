<x-admin-layout title="{{ __('performance.settings.title') }}">
@section('page_header', __('performance.settings.title'))

<div class="px-5 pb-12 space-y-6"
    x-data="{
        viewCriterion: null,
        editCriterion: null,
        createCriterion: {{ old('modal') === 'criterion' && $errors->any() ? 'true' : 'false' }},
        createCategory: {{ old('modal') === 'category' && $errors->any() ? 'true' : 'false' }}
    }">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--border)] py-6">
        <div>
            <h1 class="text-xl font-bold text-[var(--text)]">{{ __('performance.settings.title') }}</h1>
            <p class="mt-1 text-sm text-[var(--text-subtle)]">{{ __('performance.settings.subtitle') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="createCriterion = true"
                class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                {{ __('performance.settings.actions.add_criterion') }}
            </button>
            <button type="button" @click="createCategory = true"
                class="inline-flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-100">
                {{ __('performance.settings.actions.add_category') }}
            </button>
            <a href="{{ route('performance-evaluations.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface-strong)] px-3 py-2 text-sm font-medium text-[var(--text-muted)] hover:bg-[var(--surface-soft)]">
                {{ __('performance.settings.back_to_evaluations') }}
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div>
        <section class="rounded-xl border border-[var(--border)] bg-[var(--surface-strong)] p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[var(--text)]">{{ __('performance.settings.criteria_title') }}</h2>
                    <p class="mt-1 text-xs text-[var(--text-subtle)]">{{ __('performance.settings.criteria_hint') }}</p>
                </div>
                <div class="rounded-lg border px-3 py-2 text-sm font-semibold
                    {{ $totalActiveWeight === 100 ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                    {{ __('performance.settings.active_weight') }}: {{ $totalActiveWeight }}%
                </div>
            </div>

            <div class="mt-5 overflow-x-auto rounded-lg border border-[var(--border)]">
                <table class="min-w-[1180px] w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.name') }}</th>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.name_am') }}</th>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.category') }}</th>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.weight') }}</th>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.order') }}</th>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.status') }}</th>
                            <th class="px-3 py-3">{{ __('performance.settings.fields.description') }}</th>
                            <th class="px-3 py-3 text-center">{{ __('performance.settings.used_scores') }}</th>
                            <th class="px-3 py-3 text-right">{{ __('performance.fields.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)] bg-white">
                        @forelse($criteria as $criterion)
                        @php
                            $updateFormId = "criterion-update-{$criterion->id}";
                            $deleteFormId = "criterion-delete-{$criterion->id}";
                            $criterionPayload = [
                                'id' => $criterion->id,
                                'name' => $criterion->name,
                                'name_am' => $criterion->name_am,
                                'category' => $criterion->category,
                                'category_label' => $categories->firstWhere('slug', $criterion->category)?->local_name ?? $criterion->category,
                                'weight' => $criterion->weight,
                                'sort_order' => $criterion->sort_order,
                                'active' => $criterion->active,
                                'description' => $criterion->description,
                                'scores_count' => $criterion->scores_count ?? $criterion->scores()->count(),
                                'update_url' => route('settings.performance-evaluation.criteria.update', $criterion),
                            ];
                        @endphp
                        <tr class="align-top hover:bg-slate-50">
                            <td class="px-3 py-3 font-semibold text-gray-900">
                                {{ $criterion->name }}
                            </td>
                            <td class="px-3 py-3 text-gray-700">
                                {{ $criterion->name_am ?: __('performance.not_available') }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $criterionPayload['category_label'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-gray-700">
                                {{ $criterion->weight }}%
                            </td>
                            <td class="px-3 py-3 text-gray-700">
                                {{ $criterion->sort_order }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $criterion->active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $criterion->active ? __('performance.settings.active') : __('performance.settings.inactive') }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-gray-600">
                                <div class="max-w-xs truncate" title="{{ $criterion->description }}">
                                    {{ $criterion->description ?: __('performance.not_available') }}
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center text-sm font-semibold text-gray-600">
                                {{ $criterionPayload['scores_count'] }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        @click="viewCriterion = {{ \Illuminate\Support\Js::from($criterionPayload) }}"
                                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        {{ __('performance.actions.view') }}
                                    </button>
                                    <button type="button"
                                        @click="editCriterion = {{ \Illuminate\Support\Js::from($criterionPayload) }}"
                                        class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                        {{ __('performance.actions.edit') }}
                                    </button>
                                    <button type="submit" form="{{ $deleteFormId }}"
                                        onclick="return confirm({{ \Illuminate\Support\Js::from(__('performance.settings.confirm_delete')) }})"
                                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                        {{ __('performance.settings.actions.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-[var(--text-subtle)]">
                                {{ __('performance.settings.empty') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @foreach($criteria as $criterion)
                <form id="criterion-delete-{{ $criterion->id }}" method="POST" action="{{ route('settings.performance-evaluation.criteria.destroy', $criterion) }}">
                    @csrf
                    @method('DELETE')
                </form>
                @endforeach
                </div>
        </section>
    </div>

    {{-- Add criterion popup --}}
    <div x-show="createCriterion" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" @keydown.escape.window="createCriterion = false">
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl" @click.outside="createCriterion = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ __('performance.settings.create_criterion_title') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('performance.settings.create_criterion_subtitle') }}</p>
                </div>
                <button type="button" @click="createCriterion = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.performance-evaluation.criteria.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="modal" value="criterion">
                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name') }}</label>
                        <input name="name" value="{{ old('modal') === 'criterion' ? old('name') : '' }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name_am') }}</label>
                        <input name="name_am" value="{{ old('modal') === 'criterion' ? old('name_am') : '' }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.category') }}</label>
                        <select name="category" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(old('modal') === 'criterion' && old('category') === $category->slug)>{{ $category->local_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.weight') }}</label>
                        <input name="weight" type="number" min="0" max="100" value="{{ old('modal') === 'criterion' ? old('weight', 10) : 10 }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.order') }}</label>
                        <input name="sort_order" type="number" min="0" max="65535" value="{{ old('modal') === 'criterion' ? old('sort_order', 0) : 0 }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <label class="mt-7 inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="active" value="1" @checked(old('modal') !== 'criterion' || old('active'))>
                        {{ __('performance.settings.active') }}
                    </label>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.description') }}</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ old('modal') === 'criterion' ? old('description') : '' }}</textarea>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="createCriterion = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">{{ __('performance.actions.cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ __('performance.settings.actions.add_criterion') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add category popup --}}
    <div x-show="createCategory" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" @keydown.escape.window="createCategory = false">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-xl" @click.outside="createCategory = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ __('performance.settings.create_category_title') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('performance.settings.create_category_subtitle') }}</p>
                </div>
                <button type="button" @click="createCategory = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.performance-evaluation.categories.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="modal" value="category">
                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name') }}</label>
                        <input name="name" value="{{ old('modal') === 'category' ? old('name') : '' }}" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name_am') }}</label>
                        <input name="name_am" value="{{ old('modal') === 'category' ? old('name_am') : '' }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.slug') }}</label>
                        <input name="slug" value="{{ old('modal') === 'category' ? old('slug') : '' }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. efficiency">
                        <p class="mt-1 text-xs text-gray-500">{{ __('performance.settings.slug_hint') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.order') }}</label>
                        <input name="sort_order" type="number" min="0" max="65535" value="{{ old('modal') === 'category' ? old('sort_order', 0) : 0 }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="active" value="1" @checked(old('modal') !== 'category' || old('active'))>
                        {{ __('performance.settings.active') }}
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="createCategory = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">{{ __('performance.actions.cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ __('performance.settings.actions.add_category') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- View popup --}}
    <div x-show="viewCriterion" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" @keydown.escape.window="viewCriterion = null">
        <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl" @click.outside="viewCriterion = null">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-bold text-gray-900">{{ __('performance.settings.view_criterion_title') }}</h2>
                <button type="button" @click="viewCriterion = null" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.fields.name') }}</p><p class="mt-1 text-sm text-gray-900" x-text="viewCriterion?.name"></p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.fields.name_am') }}</p><p class="mt-1 text-sm text-gray-900" x-text="viewCriterion?.name_am || $el.dataset.empty" data-empty="{{ __('performance.not_available') }}"></p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.fields.category') }}</p><p class="mt-1 text-sm text-gray-900" x-text="viewCriterion?.category_label"></p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.fields.weight') }}</p><p class="mt-1 text-sm text-gray-900"><span x-text="viewCriterion?.weight"></span>%</p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.fields.order') }}</p><p class="mt-1 text-sm text-gray-900" x-text="viewCriterion?.sort_order"></p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.used_scores') }}</p><p class="mt-1 text-sm text-gray-900" x-text="viewCriterion?.scores_count"></p></div>
                <div class="sm:col-span-2"><p class="text-xs font-semibold uppercase text-gray-500">{{ __('performance.settings.fields.description') }}</p><p class="mt-1 whitespace-pre-line text-sm text-gray-900" x-text="viewCriterion?.description || $el.dataset.empty" data-empty="{{ __('performance.not_available') }}"></p></div>
            </div>
        </div>
    </div>

    {{-- Edit popup --}}
    <div x-show="editCriterion" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" @keydown.escape.window="editCriterion = null">
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl" @click.outside="editCriterion = null">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-bold text-gray-900">{{ __('performance.settings.edit_criterion_title') }}</h2>
                <button type="button" @click="editCriterion = null" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" :action="editCriterion?.update_url" class="p-5">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name') }}</label>
                        <input name="name" x-model="editCriterion.name" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name_am') }}</label>
                        <input name="name_am" x-model="editCriterion.name_am" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.category') }}</label>
                        <select name="category" x-model="editCriterion.category" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach($categories as $category)
                            <option value="{{ $category->slug }}">{{ $category->local_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.weight') }}</label>
                        <input name="weight" type="number" min="0" max="100" x-model="editCriterion.weight" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.order') }}</label>
                        <input name="sort_order" type="number" min="0" max="65535" x-model="editCriterion.sort_order" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <label class="mt-7 inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="active" value="1" x-model="editCriterion.active">
                        {{ __('performance.settings.active') }}
                    </label>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.description') }}</label>
                        <textarea name="description" rows="3" x-model="editCriterion.description" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="editCriterion = null" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">{{ __('performance.actions.cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ __('performance.settings.actions.update') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
</x-admin-layout>
