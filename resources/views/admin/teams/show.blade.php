{{-- resources/views/admin/teams/show.blade.php --}}
<x-admin-layout title="Team overview">
    @section('page_header', 'Team detail')

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $team->name }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ $team->parent?->name ? 'Reports to ' . $team->parent->name : 'Top-level team' }}
                    </p>
                </div>
                <a href="{{ route('teams.index') }}"
                    class="rounded border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Back to list
                </a>
            </div>
            <div class="mt-3 text-sm text-gray-600">
                Leader:
                <span class="font-semibold text-gray-900">
                    {{ $team->leader?->name ?? 'Unassigned' }}
                </span>
            </div>
            <p class="mt-4 text-sm text-gray-700">
                {{ $team->description ?? 'No description provided for this team.' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Members</h3>
                    <p class="text-xs text-gray-500">{{ $team->users->count() }} assigned</p>
                </div>
                <a href="{{ route('teams.edit', $team) }}"
                    class="rounded border border-blue-600 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                    Manage members
                </a>
            </div>
            @if($team->users->isEmpty())
            <p class="text-sm text-gray-500">No members assigned yet.</p>
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
