{{-- resources/views/admin/teams/edit.blade.php --}}
<x-admin-layout title="Edit team">
    @section('page_header', 'Edit team')

    @if(session('success'))
    <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-4 py-2">
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-500">Changes here are reflected in case assignment dropdowns and reports.</p>
        <a href="{{ route('teams.index') }}"
            class="rounded border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back to list
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Team details</h2>
            <form method="POST" action="{{ route('teams.update', $team) }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name', $team->name) }}"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Parent team</label>
                    <select name="parent_id"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">None</option>
                        @foreach($teams as $parent)
                        <option value="{{ $parent->id }}"
                            @selected(old('parent_id', $team->parent_id ?? '') == $parent->id)>
                            {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                        rows="4">{{ old('description', $team->description) }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Team leader</label>
                    <select name="team_leader_id"
                        class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">None</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            @selected(old('team_leader_id', $team->team_leader_id) == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Optional; leader will receive focal notifications.</p>
                </div>

                <button type="submit"
                    class="w-full rounded bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Save changes
                </button>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Members</h2>
            <p class="text-sm text-gray-500">Assign users to this team (each user may belong to only one team).</p>
            <p class="text-xs text-gray-500">Team leader must always remain a member; unchecking them here will have no effect.</p>
            <form method="POST" action="{{ route('teams.users.update', $team) }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')
                @php
                $selectedUsers = old('users', $team->users->pluck('id')->toArray());
                @endphp
                <div>
                    <div class="mt-1 grid gap-2 sm:grid-cols-2 max-h-60 overflow-y-auto rounded border border-gray-200 bg-white p-2 text-sm">
                        @foreach($users as $user)
                        <label class="flex items-center gap-2 rounded px-2 py-1 hover:bg-gray-50">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                @checked(in_array($user->id, $selectedUsers))>
                            <span>{{ $user->name }} ({{ $user->email }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit"
                    class="w-full rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Save members
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
