<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with(['parent', 'children', 'users', 'leader'])->orderBy('name')->get();

        // The register (React, Court/Teams/Index).
        return Inertia::render('Court/Teams/Index', [
            'teams' => $teams->map(fn (Team $team): array => [
                'id' => (int) $team->id,
                'name' => (string) $team->name,
                'parent_name' => $team->parent?->name,
                'description' => $team->description,
                'members_count' => $team->users->count(),
                'leader_name' => $team->leader?->name,
                'leader_email' => $team->leader?->email,
            ])->values()->all(),
        ]);
    }

    public function create()
    {
        // The create form (React, Court/Teams/Create): possible parents and the people free to lead.
        return Inertia::render('Court/Teams/Create', [
            'teams' => $this->teamOptions(Team::orderBy('name')->get()),
            'users' => $this->userOptions($this->availableUsers()),
        ]);
    }

    public function show(Team $team)
    {
        $team->load(['parent', 'children', 'users', 'leader']);

        // The team page (React, Court/Teams/Show) with its members.
        return Inertia::render('Court/Teams/Show', [
            'team' => [
                'id' => (int) $team->id,
                'name' => (string) $team->name,
                'parent_name' => $team->parent?->name,
                'description' => $team->description,
                'leader_name' => $team->leader?->name,
                'members' => $this->userOptions($team->users),
            ],
        ]);
    }

    public function edit(Team $team)
    {
        $team->load('users');

        // The edit form (React, Court/Teams/Edit): details on one side, membership on the other.
        // The people offered are those in no other team, so the current members stay listed.
        return Inertia::render('Court/Teams/Edit', [
            'team' => [
                'id' => (int) $team->id,
                'name' => (string) $team->name,
                'parent_id' => $team->parent_id !== null ? (int) $team->parent_id : null,
                'description' => $team->description,
                'team_leader_id' => $team->team_leader_id !== null ? (int) $team->team_leader_id : null,
                'user_ids' => $team->users->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            ],
            'teams' => $this->teamOptions(Team::where('id', '!=', $team->id)->orderBy('name')->get()),
            'users' => $this->userOptions($this->availableUsers($team)),
        ]);
    }

    /** Teams as select options. */
    private function teamOptions($teams): array
    {
        return collect($teams)
            ->map(fn (Team $team): array => ['id' => (int) $team->id, 'name' => (string) $team->name])
            ->values()
            ->all();
    }

    /** People as select options and member rows. */
    private function userOptions($users): array
    {
        return collect($users)
            ->map(fn (User $user): array => ['id' => (int) $user->id, 'name' => (string) $user->name, 'email' => (string) $user->email])
            ->values()
            ->all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:teams,id'],
            'description' => ['nullable', 'string'],
            'team_leader_id' => ['nullable', 'exists:users,id'],
        ]);

        $team = Team::create($data);

        if (!empty($data['team_leader_id'])) {
            $this->ensureSingleTeamForUser($data['team_leader_id'], $team->id);
            $team->users()->syncWithoutDetaching([$data['team_leader_id']]);
        }

        return redirect()->route('teams.index')->with('success', [
            'key' => 'messages.success.created',
            'replace' => ['resource' => __('messages.resources.team')],
        ]);
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:teams,id'],
            'description' => ['nullable', 'string'],
            'team_leader_id' => ['nullable', 'exists:users,id'],
        ]);

        $team->update($data);

        if (!empty($data['team_leader_id'])) {
            $this->ensureSingleTeamForUser($data['team_leader_id'], $team->id);
            $team->users()->syncWithoutDetaching([$data['team_leader_id']]);
        }

        return redirect()->route('teams.index')->with('success', [
            'key' => 'messages.success.updated',
            'replace' => ['resource' => __('messages.resources.team')],
        ]);
    }

    public function destroy(Team $team)
    {
        if ($team->users()->exists()) {
            return redirect()->route('teams.index')->with('error', 'messages.error.team_has_members');
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', [
            'key' => 'messages.success.deleted',
            'replace' => ['resource' => __('messages.resources.team')],
        ]);
    }

    public function updateUsers(Request $request, Team $team)
    {
        $userIds = collect($request->input('users', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $leaderAdded = false;
        if ($team->team_leader_id && !in_array($team->team_leader_id, $userIds, true)) {
            $userIds[] = $team->team_leader_id;
            $leaderAdded = true;
        }

        DB::transaction(function () use ($team, $userIds) {
            if (!empty($userIds)) {
                DB::table('team_user')
                    ->whereIn('user_id', $userIds)
                    ->where('team_id', '!=', $team->id)
                    ->delete();
            }
            $team->users()->sync($userIds);
        });

        $message = $leaderAdded
            ? [
                'key' => 'messages.success.team_membership_updated_leader',
                'replace' => ['leader' => $team->leader?->name ?? __('messages.resources.user')],
            ]
            : 'messages.success.team_membership_updated';

        return redirect()->route('teams.edit', $team)->with('success', $message);
    }

    private function ensureSingleTeamForUser(int $userId, ?int $currentTeamId = null): void
    {
        $query = DB::table('team_user')->where('user_id', $userId);
        if ($currentTeamId !== null) {
            $query->where('team_id', '!=', $currentTeamId);
        }
        $query->delete();
    }

    private function availableUsers(?Team $team = null)
    {
        $assigned = DB::table('team_user')
            ->when($team, fn($q) => $q->where('team_id', '!=', $team->id))
            ->pluck('user_id');

        return User::whereNotIn('id', $assigned)
            ->orderBy('name')
            ->get();
    }
}
