<?php

declare(strict_types=1);

use App\Models\BenchNote;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->allPermissions = ['bench-notes.manage', 'bench-notes.view', 'bench-notes.create', 'bench-notes.update', 'bench-notes.delete'];
    $this->caseId = partyCase(partyApplicant('bench'), 'BN/00001/26');
    $this->makeNote = fn (User $author, array $overrides = []): BenchNote => BenchNote::create([
        'case_id' => $this->caseId,
        'user_id' => $author->id,
        'note' => '<p>Kept</p><script>alert(1)</script>',
        'judge_one_id' => $author->id,
        ...$overrides,
    ]);
});

test('the bench note list renders through Inertia, sanitised and filtered by case', function (): void {
    $user = inertiaStaff($this->allPermissions);
    ($this->makeNote)($user);
    $otherCase = partyCase(partyApplicant('bench-other'), 'BN/00002/26');
    ($this->makeNote)($user, ['case_id' => $otherCase]);

    $this->actingAs($user)
        ->get(route('bench-notes.index', ['case_id' => $this->caseId]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/BenchNotes/Index')
            ->where('filters.case_id', (string) $this->caseId)
            ->has('notes.data', 1)
            ->where('notes.data.0.caseNumber', 'BN/00001/26')
            ->where('notes.data.0.judges.0', $user->name)
            ->where('notes.data.0.noteHtml', fn (string $html): bool => str_contains($html, 'Kept') && ! str_contains($html, '<script'))
            ->has('cases', 2));
});

test('each bench note action needs its own permission, not only bench-notes.manage', function (): void {
    $author = inertiaStaff($this->allPermissions);
    $note = ($this->makeNote)($author);
    $manager = inertiaStaff(['bench-notes.manage']);

    $this->actingAs($manager)->get(route('bench-notes.index'))->assertForbidden();
    $this->actingAs($manager)->get(route('bench-notes.show', $note))->assertForbidden();
    $this->actingAs($manager)->get(route('bench-notes.create'))->assertForbidden();
    $this->actingAs($manager)->post(route('bench-notes.store'), ['case_id' => $this->caseId, 'title' => 'x', 'note' => 'y'])->assertForbidden();
    $this->actingAs($manager)->patch(route('bench-notes.update', $note), ['case_id' => $this->caseId, 'title' => 'x', 'note' => 'y'])->assertForbidden();
    $this->actingAs($manager)->delete(route('bench-notes.destroy', $note))->assertForbidden();

    expect($note->fresh())->not->toBeNull()->note->toContain('Kept');

    // Without bench-notes.manage the route middleware turns staff away first.
    $this->actingAs(inertiaStaff(['bench-notes.view']))->get(route('bench-notes.index'))->assertRedirect(route('dashboard'));
});

test('a bench note can be created with the author in the presiding seat', function (): void {
    $user = inertiaStaff($this->allPermissions);
    $second = User::factory()->create();

    $this->actingAs($user)
        ->get(route('bench-notes.create', ['case_id' => $this->caseId]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/BenchNotes/Form')
            ->where('note', null)
            ->where('defaultCaseId', $this->caseId)
            ->where('currentUser.id', $user->id)
            ->has('judges'));

    $this->actingAs($user)
        ->post(route('bench-notes.store'), [
            'case_id' => $this->caseId,
            'title' => 'Fresh note',
            'note_date' => '2026-01-15',
            'note' => '<p>Body</p><img src=x onerror=alert(1)>',
            'judges' => [['admin_user_id' => $user->id], ['admin_user_id' => $second->id], ['admin_user_id' => null]],
        ])
        ->assertRedirect(route('bench-notes.index', ['case_id' => $this->caseId]));

    $note = BenchNote::query()->where('note', 'like', '%Body%')->sole();
    expect($note->judge_one_id)->toBe($user->id)
        ->and($note->judge_two_id)->toBe($second->id)
        ->and($note->judge_three_id)->toBeNull()
        ->and($note->note)->not->toContain('onerror');
});

test('a bench note can be edited, viewed and deleted', function (): void {
    $user = inertiaStaff($this->allPermissions);
    $note = ($this->makeNote)($user, ['note_date' => '2026-02-01']);

    $this->actingAs($user)
        ->get(route('bench-notes.edit', $note))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/BenchNotes/Form')
            ->where('note.id', $note->id)
            ->where('note.noteDate', '2026-02-01')
            ->where('note.judgeIds.0', $user->id));

    $this->actingAs($user)
        ->patch(route('bench-notes.update', $note), [
            'case_id' => $this->caseId,
            'title' => 'Edited note',
            'note' => '<p>Edited</p>',
            'judges' => [['admin_user_id' => $user->id], ['admin_user_id' => null], ['admin_user_id' => null]],
        ])
        ->assertRedirect();

    expect($note->fresh()->note)->toBe('<p>Edited</p>');

    $this->actingAs($user)
        ->get(route('bench-notes.show', $note))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/BenchNotes/Show')->where('note.noteHtml', '<p>Edited</p>'));

    $this->actingAs($user)->delete(route('bench-notes.destroy', $note))->assertRedirect();

    expect(BenchNote::query()->find($note->id))->toBeNull();
});
