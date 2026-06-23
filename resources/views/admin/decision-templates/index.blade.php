{{-- resources/views/admin/decision-templates/index.blade.php --}}
@php use Illuminate\Support\Str; @endphp
@php
$canCreateTemplate = function_exists('userHasPermission')
    ? userHasPermission('decision.templet.create')
    : (auth()->user()?->hasPermission('decision.templet.create') ?? false);
$canUpdateTemplate = function_exists('userHasPermission')
    ? userHasPermission('decision.templet.update')
    : (auth()->user()?->hasPermission('decision.templet.update') ?? false);
$canDeleteTemplate = function_exists('userHasPermission')
    ? userHasPermission('decision.templet.delete')
    : (auth()->user()?->hasPermission('decision.templet.delete') ?? false);
@endphp

<x-admin-layout title="{{ __('decision_templates.title') }}">
    @section('page_header', __('decision_templates.title'))

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('decision_templates.title') }}</h2>
                <p class="text-sm text-gray-500">{{ __('decision_templates.description') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($canCreateTemplate)
                <a href="{{ route('decision-templates.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('decision_templates.actions.new_template') }}
                </a>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @if($templates->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">
                {{ __('decision_templates.table.empty') }}
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3 font-medium">{{ __('decision_templates.table.title') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('decision_templates.table.category') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('decision_templates.table.placeholders') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('decision_templates.table.updated') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('decision_templates.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($templates as $template)
                        @php
                            $placeholderList = $template->placeholders ? implode(', ', $template->placeholders) : '';
                            $placeholderDisplay = $placeholderList ? Str::limit($placeholderList, 60, '...') : __('decision_templates.table.none');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $template->title }}
                                @if($template->is_default)
                                <span class="ml-1 inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                                    {{ __('decision_templates.output.default_layout') }}
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $template->category ?? __('decision_templates.table.missing') }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="text-xs text-gray-500"
                                    title="{{ $placeholderList ?: __('decision_templates.table.none') }}">
                                    {{ $placeholderDisplay }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ \App\Support\EthiopianDate::smartRelative($template->updated_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if($canUpdateTemplate)
                                    <a href="{{ route('decision-templates.edit', $template) }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                        {{ __('decision_templates.actions.edit') }}
                                    </a>
                                    @endif
                                    @if($canDeleteTemplate)
                                    <form method="POST" action="{{ route('decision-templates.destroy', $template) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('{{ __('decision_templates.actions.delete_template') }}')"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                            {{ __('decision_templates.actions.delete') }}
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
            <div class="px-4 py-3 border-t border-gray-200">{!! $templates->links() !!}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
