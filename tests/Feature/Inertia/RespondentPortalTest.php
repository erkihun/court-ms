<?php

declare(strict_types=1);

use App\Models\Respondent;
use App\Models\RespondentResponse;
use App\Services\Party\ActingRespondent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function respondentWithCase(string $tag, string $reviewStatus = 'awaiting_review'): array
{
    $actor = partyApplicant($tag);
    $owner = partyApplicant($tag.'-owner');
    $caseId = partyCase($owner, 'RP-'.$tag, ['code' => '12345']);
    $respondent = app(ActingRespondent::class)->resolve($actor);
    DB::table('respondent_case_views')->insert([
        'respondent_id' => $respondent->id, 'case_id' => $caseId, 'case_number' => 'RP-'.$tag,
        'viewed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    $response = RespondentResponse::create([
        'respondent_id' => $respondent->id,
        'case_number' => 'RP-'.$tag,
        'title' => 'Answer',
        'description' => '<p>Original</p>',
        'pdf_path' => 'respondent/responses/x.pdf',
        'review_status' => $reviewStatus,
    ]);

    return [$actor, $response];
}

test('an accepted response can no longer be edited or deleted', function (): void {
    [$actor, $response] = respondentWithCase('locked', 'accepted');

    $this->actingAs($actor, 'applicant')->get(route('respondent.responses.edit', $response))->assertForbidden();
    $this->patch(route('respondent.responses.update', $response), ['title' => 'Changed'])->assertForbidden();
    $this->delete(route('respondent.responses.destroy', $response))->assertForbidden();

    expect($response->fresh()->title)->toBe('Answer');
    $this->get(route('respondent.responses.show', $response))
        ->assertInertia(fn (Assert $page) => $page->component('Respondent/Responses/Show')->where('response.locked', true));
});

test('editing a returned response sends it back for review', function (): void {
    [$actor, $response] = respondentWithCase('returned', 'returned');
    $response->forceFill(['review_note' => 'Attach the signed copy'])->save();

    $this->actingAs($actor, 'applicant')
        ->patch(route('respondent.responses.update', $response), ['title' => 'Corrected', 'description' => '<p>New</p><script>x</script>', 'case_number' => 'RP-returned'])
        ->assertRedirect(route('respondent.responses.show', $response));

    $fresh = $response->fresh();
    expect($fresh->title)->toBe('Corrected')
        ->and($fresh->review_status)->toBe('awaiting_review')
        ->and($fresh->review_note)->toBeNull()
        ->and($fresh->description)->not->toContain('<script');
});

test('case search posts the access code and redirects without it', function (): void {
    $actor = partyApplicant('search');
    partyCase(partyApplicant('search-owner'), 'RP-SEARCH-1', ['code' => '54321']);

    $this->actingAs($actor, 'applicant')
        ->post(route('respondent.case.search.submit'), ['case_number' => 'RP-SEARCH-1', 'case_code' => '00000'])
        ->assertRedirect(route('respondent.case.search', ['case_number' => 'RP-SEARCH-1']));
    $this->get(route('respondent.case.search', ['case_number' => 'RP-SEARCH-1']))
        ->assertInertia(fn (Assert $page) => $page->where('case', null)->where('codeError', true));

    $this->post(route('respondent.case.search.submit'), ['case_number' => 'RP-SEARCH-1', 'case_code' => '54321'])
        ->assertRedirect(route('respondent.case.search', ['case_number' => 'RP-SEARCH-1']));
    $this->get(route('respondent.case.search', ['case_number' => 'RP-SEARCH-1']))
        ->assertInertia(fn (Assert $page) => $page->where('case.caseNumber', 'RP-SEARCH-1')->where('case.href', route('respondent.cases.show', 'RP-SEARCH-1')));

    // Access was recorded, so the case page and "my cases" work without the code.
    $this->get(route('respondent.cases.show', 'RP-SEARCH-1'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Respondent/Cases/Show')->missing('case.code'));
    $this->get(route('respondent.cases.my'))
        ->assertInertia(fn (Assert $page) => $page->where('cases.0.href', route('respondent.cases.show', 'RP-SEARCH-1')));
});

test('a case page is not reachable without the access code or a recorded view', function (): void {
    partyCase(partyApplicant('private-owner'), 'RP-PRIVATE-1', ['code' => '99999']);

    $this->actingAs(partyApplicant('private'), 'applicant')
        ->get(route('respondent.cases.show', 'RP-PRIVATE-1'))
        ->assertNotFound();
});

test('the respondent record is created once and reused', function (): void {
    $actor = partyApplicant('acting');

    $first = app(ActingRespondent::class)->resolve($actor);
    $second = app(ActingRespondent::class)->resolve($actor);

    expect($first->id)->toBe($second->id)
        ->and(Respondent::query()->where('email', $actor->email)->count())->toBe(1);
});

test('the response form keeps the case number from the case page', function (): void {
    [$actor] = respondentWithCase('form');

    $this->actingAs($actor, 'applicant')
        ->get(route('respondent.responses.create', ['case_number' => 'RP-form']))
        ->assertInertia(fn (Assert $page) => $page->component('Respondent/Responses/Form')->where('caseNumber', 'RP-form')->where('response', null));
});

test('responses can be filed for an authorised case', function (): void {
    [$actor] = respondentWithCase('file');

    $this->actingAs($actor, 'applicant')
        ->post(route('respondent.responses.store'), [
            'title' => 'Second answer',
            'case_number' => 'RP-file',
            'description' => '<p>Text</p>',
            'pdf' => UploadedFile::fake()->create('answer.pdf', 50, 'application/pdf'),
        ])
        ->assertRedirect();

    expect(RespondentResponse::query()->where('title', 'Second answer')->value('review_status'))->toBe('awaiting_review');
});
