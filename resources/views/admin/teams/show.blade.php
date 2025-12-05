{{-- resources/views/admin/teams/show.blade.php --}}
<x-admin-layout title="{{ __('teams.headings.overview') }}">
    @section('page_header', __('teams.page_header.show'))

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $team->name }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ $team->parent?->name ? __('teams.labels.reports_to', ['team' => $team->parent->name]) : __('teams.labels.top_level') }}
                    </p>
                </div>
                <a href="{{ route('teams.index') }}"
                    class="rounded border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    {{ __('teams.buttons.back_to_list') }}
                </a>
            </div>
            <div class="mt-3 text-sm text-gray-600">
                {{ __('teams.labels.leader_prefix') }}
                <span class="font-semibold text-gray-900">
                    {{ $team->leader?->name ?? __('teams.meta.unassigned') }}
                </span>
            </div>
            <p class="mt-4 text-sm text-gray-700">
                {{ $team->description ?? __('teams.descriptions.description_missing') }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ __('teams.headings.members') }}</h3>
                    <p class="text-xs text-gray-500">{{ $team->users->count() }} {{ __('teams.labels.assigned') }}</p>
                </div>
                <a href="{{ route('teams.edit', $team) }}"
                    class="rounded border border-blue-600 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                    {{ __('teams.buttons.manage_members') }}
                </a>
            </div>
            @if($team->users->isEmpty())
            <p class="text-sm text-gray-500">{{ __('teams.descriptions.members_empty') }}</p>
            @else
            <div class="divide-y divide-gray-100 text-sm text-gray-700">
                @foreach($team->users as $user)
                <div class="flex items-center justify-between py-2">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
