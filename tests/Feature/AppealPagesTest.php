<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

test('an appeal is filed, shown, edited, submitted and decided on the React pages', function (): void {
    $admin = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $admin->roles()->syncWithoutDetaching([Role::query()->firstOrCreate(['name' => 'admin'])->id]);
    $type = CaseType::query()->create(['name' => 'Appeal type '.Str::random(4)]);
    $case = CourtCase::query()->create(['case_number' => 'AP-'.Str::upper(Str::random(8)), 'title' => 'Appealed case', 'case_type_id' => $type->id, 'filing_date' => now()->toDateString(), 'status' => 'closed']);

    $this->actingAs($admin)->get(route('appeals.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Appeals/Form')->where('appeal', null)->where('cases.0.case_number', $case->case_number));

    $this->actingAs($admin)->post(route('appeals.store'), ['court_case_id' => $case->id, 'title' => 'Contest the ruling', 'grounds' => 'Procedure.'])->assertRedirect();
    $id = (int) DB::table('appeals')->where('title', 'Contest the ruling')->value('id');

    $this->actingAs($admin)->get(route('appeals.show', $id))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Appeals/Show')->where('appeal.status', 'draft')->where('appeal.case_number', $case->case_number)->where('can.decide', true));
    $this->actingAs($admin)->get(route('appeals.edit', $id))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Appeals/Form')->where('appeal.title', 'Contest the ruling'));

    $this->actingAs($admin)->post(route('appeals.submit', $id))->assertRedirect();
    $this->actingAs($admin)->post(route('appeals.decide', $id), ['decision' => 'approved', 'decision_notes' => 'Granted.'])->assertRedirect();
    $this->actingAs($admin)->get(route('appeals.show', $id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('appeal.status', 'approved')->where('appeal.decision_notes', 'Granted.')->where('appeal.decided_by', $admin->name));
});
