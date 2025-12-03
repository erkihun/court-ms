{{-- resources/views/admin/teams/index.blade.php --}}
<x-admin-layout title="Teams">
    @section('page_header', 'Team Management')

    @if(session('success'))
    <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-4 py-2">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-2">
        {{ session('error') }}
    </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Current teams</h2>
                <p class="text-sm text-gray-600">Only one team per user is allowed, and assignments happen inside the
                    team detail views.</p>
            </div>
            <a href="{{ route('teams.create') }}"
                class="inline-flex items-center rounded border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Add new team
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600">
                    <tr>
                        <th class="px-3 py-2 font-medium">Team</th>
                        <th class="px-3 py-2 font-medium">Members</th>
                        <th class="px-3 py-2 font-medium">Leader</th>
                        <th class="px-3 py-2 font-medium">Description</th>
                        <th class="px-3 py-2 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($teams as $team)
                    <tr>
                        <td class="px-3 py-4 align-top">
                            <div class="font-semibold text-gray-900">{{ $team->name }}</div>
                            <p class="text-xs text-gray-500">
                                {{ $team->parent?->name ? 'Reports to ' . $team->parent->name : 'Top-level team' }}
                            </p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <div class="text-sm font-semibold text-gray-900">{{ $team->users->count() }}</div>
                            <p class="text-xs text-gray-500">members</p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $team->leader?->name ?? 'Unassigned' }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $team->leader?->email ?? '' }}</p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <p class="text-sm text-gray-700">
                                {{ $team->description ? \Illuminate\Support\Str::limit($team->description, 120) : 'No description supplied.' }}
                            </p>
                        </td>
                        <td class="px-3 py-4 align-top">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('teams.show', $team) }}"
                                    class="rounded border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    View
                                </a>
                                <a href="{{ route('teams.edit', $team) }}"
                                    class="rounded border border-gray-300 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                                    Edit
                                </a>
                                @if($team->users->isNotEmpty())
                                <div class="flex flex-col items-end gap-1">
                                    <button type="button"
                                        class="cursor-not-allowed rounded border border-red-200 bg-white px-3 py-1 text-xs font-semibold text-red-300"
                                        disabled>
                                        Delete
                                    </button>
                                </div>
                                @else
                                <form method="POST" action="{{ route('teams.destroy', $team) }}"
                                    onsubmit="return confirm('Delete this team?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded border border-red-600 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-gray-500">
                            No teams defined yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
