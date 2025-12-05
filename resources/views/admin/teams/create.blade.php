{{-- resources/views/admin/teams/create.blade.php --}}
<x-admin-layout title="{{ __('teams.buttons.add_new') }}">
    @section('page_header', __('teams.page_header.create'))

    @if(session('success'))
    <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-4 py-2">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">{{ __('teams.errors.fix') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-4">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('teams.headings.details') }}</h2>
        <p class="text-sm text-gray-600">{{ __('teams.descriptions.details') }}</p>

        <form method="POST" action="{{ route('teams.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.name') }}</label>
                <input name="name" value="{{ old('name') }}"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.parent') }}</label>
                <select name="parent_id"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('teams.labels.none') }}</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" @selected(old('parent_id') == $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.team_leader') }}</label>
                <select name="team_leader_id"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('teams.labels.none') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('team_leader_id') == $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">{{ __('teams.descriptions.leader_optional') }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">{{ __('teams.labels.description') }}</label>
                <textarea name="description"
                    class="mt-1 block w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    rows="4">{{ old('description') }}</textarea>
            </div>

            <button type="submit"
                class="w-full rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ __('teams.buttons.create') }}
            </button>
        </form>
    </div>
</x-admin-layout>
