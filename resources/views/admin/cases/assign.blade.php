<x-admin-layout title="Assign Case">
    @section('page_header','Assign Case')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Case summary --}}
        <div class="lg:col-span-1 p-6 rounded-xl border border-gray-300 bg-white">
            <h3 class="text-sm text-gray-700 mb-3 font-medium">Case</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600">Case #:</span> <span
                        class="font-mono text-gray-900">{{ $case->case_number }}</span></div>
                <div><span class="text-gray-600">Title:</span> <span class="text-gray-900">{{ $case->title }}</span>
                </div>
                <div><span class="text-gray-600">Status:</span> <span
                        class="capitalize text-gray-900">{{ $case->status }}</span></div>
                <div><span class="text-gray-600">Current Assignee:</span>
                    @if($case->assignee_name)
                    <span class="text-gray-900">{{ $case->assignee_name }}</span>
                    <span class="text-gray-500 text-xs">({{ $case->assignee_email }})</span>
                    @else
                    <span class="text-gray-500">Unassigned</span>
                    @endif
                </div>
                @if($case->assigned_at)
                <div><span class="text-gray-600">Assigned at:</span>
                    <span
                        class="text-gray-900">{{ \Illuminate\Support\Carbon::parse($case->assigned_at)->format('M d, Y h:i A') }}</span>
                </div>
                @endif
            </div>
            <div class="mt-4">
                <a href="{{ route('cases.index') }}"
                    class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800">Back</a>
            </div>
        </div>

        {{-- Assign form --}}
        <div class="lg:col-span-2 p-6 rounded-xl border border-gray-300 bg-white">
            <form method="POST" action="{{ route('cases.assign.update',$case->id) }}" class="space-y-4">
                @csrf @method('PATCH')

                @php
                $mode = $assignmentMode ?? 'admin';
                $canAssignLeaders = auth()->user()?->hasPermission('cases.assign.team');
                @endphp
                @if(!$canAssignLeaders)
                <div class="mb-4 rounded-md border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm text-yellow-800">
                    You don’t have the `cases.assign.team` permission, so assignments are limited to your active team members.
                </div>
                @endif
                @if($mode === 'leader' && $leaderTeam)
                <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700">
                    Assigning as leader of <span class="font-semibold text-blue-900">{{ $leaderTeam->name }}</span>;
                    you can route cases to your team members only.
                </div>
                @else
                <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">
                    Admins assign cases to the listed team leaders; a team leader can then reassign to their members.
                </div>
                @endif

                <div>
                    <label class="block text-sm text-gray-700 mb-1 font-medium">Assign to</label>
                    <select name="assigned_user_id"
                        class="w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300">
                        <option value="">Select a user</option>
                        @if($mode === 'leader' && $leaderTeam)
                        <optgroup label="Team members">
                            @foreach($leaderTeam->users as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_user_id', $case->
                                assigned_user_id)==$user->id)>
                                {{ $user->name }} - {{ $user->email }}
                            </option>
                            @endforeach
                        </optgroup>
                        @else
                        @foreach(($teams ?? collect()) as $team)
                        @if($team->leader)
                        <optgroup label="{{ $team->name }} / Leader">
                            <option value="{{ $team->leader->id }}" @selected(old('assigned_user_id', $case->
                                assigned_user_id)==$team->leader->id)>
                                {{ $team->leader->name }} - {{ $team->leader->email }}
                            </option>
                        </optgroup>
                        @endif
                        @endforeach
                        @endif
                    </select>
                    @error('assigned_user_id')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Unassign toggle --}}
                @if($case->assigned_user_id)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="unassign" value="1" class="rounded border-gray-300 bg-white">
                    Unassign this case
                </label>
                @endif

                <div class="pt-2">
                    <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Save</button>
                    <a href="{{ route('cases.index') }}"
                        class="ml-2 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
