<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with(['parent', 'children', 'users', 'leader'])->orderBy('name')->get();

        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        $teams = Team::orderBy('name')->get();
        $users = $this->availableUsers();

        return view('admin.teams.create', compact('teams', 'users'));
    }

    public function show(Team $team)
    {
        $team->load(['parent', 'children', 'users', 'leader']);

        return view('admin.teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $teams = Team::where('id', '!=', $team->id)->orderBy('name')->get();
        $users = $this->availableUsers($team);

        return view('admin.teams.edit', compact('team', 'teams', 'users'));
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

        return redirect()->route('teams.index')->with('success', 'Team created.');
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

        return redirect()->route('teams.index')->with('success', 'Team updated.');
    }

    public function destroy(Team $team)
    {
        if ($team->users()->exists()) {
            return redirect()->route('teams.index')->with('error', 'Team still has members; remove them before deleting.');
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted.');
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

        $message = 'Team membership updated.';
        if ($leaderAdded) {
            $message .= ' Leader ' . ($team->leader?->name ?? 'assigned leader') . ' remains on the roster.';
        }

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
