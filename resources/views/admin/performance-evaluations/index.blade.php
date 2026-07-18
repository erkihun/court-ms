<x-admin-layout title="{{ __('performance.title') }}">
@section('page_header', __('performance.title'))

<div class="space-y-6"
    x-data="{
        criterionModal: {{ old('modal') === 'criterion' && $errors->any() ? 'true' : 'false' }},
        categoryModal: {{ old('modal') === 'category' && $errors->any() ? 'true' : 'false' }}
    }">
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

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach([
            ['label' => __('performance.stats.total'), 'value' => $stats['total'], 'color' => 'bg-slate-50 border-slate-200 text-slate-700'],
            ['label' => __('performance.stats.draft'), 'value' => $stats['draft'], 'color' => 'bg-amber-50 border-amber-200 text-amber-700'],
            ['label' => __('performance.stats.submitted'), 'value' => $stats['submitted'], 'color' => 'bg-blue-50 border-blue-200 text-blue-700'],
            ['label' => __('performance.stats.reviewed'), 'value' => $stats['reviewed'], 'color' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
            ['label' => __('performance.stats.avg_score'), 'value' => $stats['avg_score'].'%', 'color' => 'bg-violet-50 border-violet-200 text-violet-700'],
        ] as $stat)
        <div class="rounded-xl border {{ $stat['color'] }} p-4 text-center">
            <div class="text-2xl font-extrabold">{{ $stat['value'] }}</div>
            <div class="mt-0.5 text-xs font-semibold uppercase tracking-wide opacity-70">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    @php
        $statusTabs = [
            '' => ['label' => __('performance.tabs.all'), 'count' => $stats['total']],
            'draft' => ['label' => __('performance.statuses.draft'), 'count' => $stats['draft']],
            'submitted' => ['label' => __('performance.statuses.submitted'), 'count' => $stats['submitted']],
            'reviewed' => ['label' => __('performance.statuses.reviewed'), 'count' => $stats['reviewed']],
        ];
        $activeStatus = request('status', '');
    @endphp

    {{-- Status tabs --}}
    <div class="overflow-x-auto border-b border-gray-200">
        <div class="flex min-w-max gap-1">
            @foreach($statusTabs as $status => $tab)
            @php
                $isActive = $activeStatus === $status;
                $params = array_filter(array_merge(request()->except(['status', 'page']), ['status' => $status]), fn ($value) => filled($value));
            @endphp
            <a href="{{ route('performance-evaluations.index', $params) }}"
                class="inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors
                {{ $isActive ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-800' }}">
                {{ $tab['label'] }}
                <span class="rounded-full {{ $isActive ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 text-xs">
                    {{ $tab['count'] }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Filter + action bar --}}
    <div class="flex flex-wrap items-end gap-3">
        <form method="GET" action="{{ route('performance-evaluations.index') }}" class="flex flex-wrap gap-2 flex-1">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <select name="user_id" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('performance.filters.all_members') }}</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <select name="period_type" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('performance.filters.all_periods') }}</option>
                @foreach(['monthly','quarterly','annual'] as $p)
                <option value="{{ $p }}" @selected(request('period_type') === $p)>{{ __("performance.periods.$p") }}</option>
                @endforeach
            </select>
            @if(request()->hasAny(['status','user_id','period_type']))
            <a href="{{ route('performance-evaluations.index') }}"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">{{ __('performance.filters.clear') }}</a>
            @endif
        </form>
        @if(auth()->user()?->hasPermission('settings.manage'))
        <button type="button" @click="criterionModal = true"
           class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('performance.settings.actions.add_criterion') }}
        </button>
        <button type="button" @click="categoryModal = true"
           class="inline-flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"/></svg>
            {{ __('performance.settings.actions.add_category') }}
        </button>
        @endif
        @if(auth()->user()?->hasPermission('performance-evaluations.create'))
        <a href="{{ route('performance-evaluations.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('performance.actions.new') }}
        </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('performance.fields.member') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('performance.fields.period') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('performance.fields.type') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('performance.fields.score') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('performance.fields.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('performance.fields.evaluator') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('performance.fields.date') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($evaluations as $ev)
            @php
                $scoreColor = match(true) {
                    $ev->overall_score >= 85 => 'text-emerald-600',
                    $ev->overall_score >= 70 => 'text-blue-600',
                    $ev->overall_score >= 50 => 'text-amber-600',
                    default                  => 'text-red-600',
                };
                $statusColor = match($ev->status) {
                    'submitted' => 'bg-blue-50 text-blue-700 border border-blue-200',
                    'reviewed'  => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                    default     => 'bg-amber-50 text-amber-700 border border-amber-200',
                };
            @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        @if($ev->evaluatedUser?->avatar_path)
                        <img src="{{ asset('storage/'.$ev->evaluatedUser->avatar_path) }}"
                             class="h-8 w-8 rounded-full object-cover" alt="">
                        @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                            {{ strtoupper(substr($ev->evaluatedUser?->name ?? '?', 0, 1)) }}
                        </div>
                        @endif
                        <span class="font-medium text-gray-900">{{ $ev->evaluatedUser?->name ?? __('performance.not_available') }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-600">
                    {{ \App\Support\EthiopianDate::smartFormat($ev->period_start, false, __('performance.not_available'), 'h:i A', 'M d') }} -
                    {{ \App\Support\EthiopianDate::smartFormat($ev->period_end, false, __('performance.not_available'), 'h:i A', 'M d, Y') }}
                </td>
                <td class="px-4 py-3">
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ __("performance.periods.{$ev->period_type}") }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-lg font-extrabold {{ $scoreColor }}">{{ number_format($ev->overall_score, 1) }}%</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                        {{ __("performance.statuses.{$ev->status}") }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $ev->evaluator?->name ?? __('performance.not_available') }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ \App\Support\EthiopianDate::smartFormat($ev->created_at, false, __('performance.not_available'), 'h:i A', 'M d, Y') }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('performance-evaluations.show', $ev) }}"
                           class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('performance.actions.view') }}</a>
                        @if($ev->status !== 'reviewed' && auth()->user()?->hasPermission('performance-evaluations.update'))
                        <a href="{{ route('performance-evaluations.edit', $ev) }}"
                           class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">{{ __('performance.actions.edit') }}</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="h-10 w-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm font-medium">{{ __('performance.empty.evaluations') }}</p>
                        @if(auth()->user()?->hasPermission('performance-evaluations.create'))
                        <a href="{{ route('performance-evaluations.create') }}" class="text-blue-600 hover:underline text-xs">{{ __('performance.actions.create_first') }}</a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-slate-50 px-4 py-3">
            <p class="text-xs text-gray-500">
                @if($evaluations->total() > 0)
                {{ __('performance.pagination.showing', [
                    'from' => $evaluations->firstItem(),
                    'to' => $evaluations->lastItem(),
                    'total' => $evaluations->total(),
                ]) }}
                @else
                {{ __('performance.empty.evaluations') }}
                @endif
            </p>
            <div class="min-w-0">
                {{ $evaluations->links() }}
            </div>
        </div>
    </div>

    @if(auth()->user()?->hasPermission('settings.manage'))
    {{-- Add Criterion popup --}}
    <div x-show="criterionModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
        @keydown.escape.window="criterionModal = false">
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl" @click.outside="criterionModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ __('performance.settings.create_criterion_title') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('performance.settings.create_criterion_subtitle') }}</p>
                </div>
                <button type="button" @click="criterionModal = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
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
                        <input name="name" value="{{ old('modal') === 'criterion' ? old('name') : '' }}" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name_am') }}</label>
                        <input name="name_am" value="{{ old('modal') === 'criterion' ? old('name_am') : '' }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.category') }}</label>
                        <select name="category" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(old('modal') === 'criterion' && old('category') === $category->slug)>{{ $category->local_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.weight') }}</label>
                        <input name="weight" type="number" min="0" max="100" value="{{ old('modal') === 'criterion' ? old('weight', 10) : 10 }}" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.order') }}</label>
                        <input name="sort_order" type="number" min="0" max="65535" value="{{ old('modal') === 'criterion' ? old('sort_order', 0) : 0 }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <label class="mt-7 inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="active" value="1" @checked(old('modal') !== 'criterion' || old('active'))>
                        {{ __('performance.settings.active') }}
                    </label>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.description') }}</label>
                        <textarea name="description" rows="3"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ old('modal') === 'criterion' ? old('description') : '' }}</textarea>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="criterionModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                        {{ __('performance.actions.cancel') }}
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ __('performance.settings.actions.add_criterion') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Category popup --}}
    <div x-show="categoryModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
        @keydown.escape.window="categoryModal = false">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-xl" @click.outside="categoryModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ __('performance.settings.create_category_title') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('performance.settings.create_category_subtitle') }}</p>
                </div>
                <button type="button" @click="categoryModal = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
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
                        <input name="name" value="{{ old('modal') === 'category' ? old('name') : '' }}" required
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.name_am') }}</label>
                        <input name="name_am" value="{{ old('modal') === 'category' ? old('name_am') : '' }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.slug') }}</label>
                        <input name="slug" value="{{ old('modal') === 'category' ? old('slug') : '' }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g. efficiency">
                        <p class="mt-1 text-xs text-gray-500">{{ __('performance.settings.slug_hint') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ __('performance.settings.fields.order') }}</label>
                        <input name="sort_order" type="number" min="0" max="65535" value="{{ old('modal') === 'category' ? old('sort_order', 0) : 0 }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="active" value="1" @checked(old('modal') !== 'category' || old('active'))>
                        {{ __('performance.settings.active') }}
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="categoryModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                        {{ __('performance.actions.cancel') }}
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ __('performance.settings.actions.add_category') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
</x-admin-layout>
