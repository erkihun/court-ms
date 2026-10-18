<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        $teams = Team::with(['parent:id,name', 'leader:id,name,email'])->withCount('users')->orderBy('name')->get();

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams->map(fn (Team $team): array => [
                'id' => (int) $team->id,
                'name' => (string) $team->name,
                'parentName' => $team->parent?->name,
                'leaderName' => $team->leader?->name,
                'leaderEmail' => $team->leader?->email,
                'description' => $team->description ? Str::limit($team->description, 120) : null,
                'membersCount' => (int) $team->users_count,
            ])->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Teams/Form', [
            'team' => null,
            'parents' => $this->teamOptions(Team::orderBy('name')->get(['id', 'name'])),
            'users' => $this->userOptions($this->availableUsers()),
        ]);
    }

    public function show(Team $team): Response
    {
        $team->load(['parent:id,name', 'leader:id,name', 'users' => fn ($query) => $query->orderBy('name')]);

        return Inertia::render('Admin/Teams/Show', [
            'team' => [
                'id' => (int) $team->id,
                'name' => (string) $team->name,
                'parentName' => $team->parent?->name,
                'leaderName' => $team->leader?->name,
                'description' => $team->description,
                'members' => $team->users->map(fn (User $user): array => [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                ])->values(),
            ],
        ]);
    }

    public function edit(Team $team): Response
    {
        $excluded = $this->descendantIds($team)->push($team->id);
        $users = $this->availableUsers($team);

        return Inertia::render('Admin/Teams/Form', [
            'team' => [
                'id' => (int) $team->id,
                'name' => (string) $team->name,
                'parentId' => $team->parent_id ? (int) $team->parent_id : null,
                'description' => (string) ($team->description ?? ''),
                'leaderId' => $team->team_leader_id ? (int) $team->team_leader_id : null,
                'memberIds' => $team->users()->pluck('users.id')->map(fn ($id): int => (int) $id)->values(),
            ],
            'parents' => $this->teamOptions(Team::whereNotIn('id', $excluded)->orderBy('name')->get(['id', 'name'])),
            'users' => $this->userOptions($users),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

    public function update(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:teams,id'],
            'description' => ['nullable', 'string'],
            'team_leader_id' => ['nullable', 'exists:users,id'],
        ]);

        // A team cannot report to itself or to one of its own sub-teams.
        if (! empty($data['parent_id']) && $this->descendantIds($team)->push($team->id)->contains((int) $data['parent_id'])) {
            throw ValidationException::withMessages(['parent_id' => __('teams.errors.parent_cycle')]);
        }

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

    public function destroy(Team $team): RedirectResponse
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

    public function updateUsers(Request $request, Team $team): RedirectResponse
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

    /** @return \Illuminate\Support\Collection<int, int> */
    private function descendantIds(Team $team): \Illuminate\Support\Collection
    {
        $ids = collect();
        $frontier = [$team->id];

        while ($frontier !== []) {
            $children = Team::whereIn('parent_id', $frontier)->whereNotIn('id', $ids->all())->pluck('id')->map(fn ($id): int => (int) $id);
            $ids = $ids->merge($children);
            $frontier = $children->all();
        }

        return $ids->values();
    }

    /** @return list<array{id: int, name: string}> */
    private function teamOptions(Collection $teams): array
    {
        return $teams->map(fn (Team $team): array => ['id' => (int) $team->id, 'name' => (string) $team->name])->values()->all();
    }

    /** @return list<array{id: int, name: string, email: string}> */
    private function userOptions(Collection $users): array
    {
        return $users->map(fn (User $user): array => [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
        ])->values()->all();
    }

    private function availableUsers(?Team $team = null): Collection
    {
        $assigned = DB::table('team_user')
            ->when($team, fn($q) => $q->where('team_id', '!=', $team->id))
            ->pluck('user_id');

        return User::whereNotIn('id', $assigned)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
