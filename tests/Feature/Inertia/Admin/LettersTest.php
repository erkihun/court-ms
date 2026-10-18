<?php

declare(strict_types=1);

use App\Models\DecisionTemplate;
use App\Models\Letter;
use App\Models\LetterCategory;
use App\Models\LetterTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->template = LetterTemplate::create([
        'title' => 'Hearing notice',
        'category' => 'Hearing',
        'placeholders' => ['{case_number}', '{applicant_name}'],
        'body' => '<p>Dear {applicant_name}, case {case_number}.</p>',
    ]);

    $this->makeLetter = fn (array $overrides = []): Letter => Letter::create([
        'letter_template_id' => $this->template->id,
        'user_id' => User::factory()->create(['name' => 'Clerk One', 'position' => 'Registrar'])->id,
        'recipient_name' => 'Applicant',
        'subject' => 'Notice',
        'reference_number' => 'REF/'.uniqid(),
        'body' => '<p>Body</p>',
        'send_to_applicant' => true,
        'send_to_respondent' => true,
        ...$overrides,
    ]);
});

test('letter categories render, redirect without permission and can be managed', function (): void {
    LetterCategory::create(['name' => 'Hearing', 'description' => 'Hearing letters']);

    $this->actingAs(inertiaStaff([]))->get(route('letter-categories.index'))->assertRedirect(route('dashboard'));

    $staff = inertiaStaff(['letters.templet.view', 'letters.templet.create', 'letters.templet.update', 'letters.templet.delete']);
    $this->actingAs($staff)
        ->get(route('letter-categories.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/LetterCategories/Index')
            ->where('categories.data.0.name', 'Hearing')
            ->where('categories.data.0.description', 'Hearing letters'));

    $this->get(route('letter-categories.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/LetterCategories/Form')->where('category', null));

    $this->post(route('letter-categories.store'), ['name' => 'Appeal'])->assertRedirect(route('letter-categories.index'));
    $appeal = LetterCategory::where('name', 'Appeal')->firstOrFail();

    $this->get(route('letter-categories.edit', $appeal))
        ->assertInertia(fn (Assert $page) => $page->where('category.id', $appeal->id)->where('category.name', 'Appeal'));

    $this->patch(route('letter-categories.update', $appeal), ['name' => 'Appeals', 'description' => 'x'])->assertRedirect(route('letter-categories.index'));
    expect($appeal->fresh()->name)->toBe('Appeals');

    $this->post(route('letter-categories.store'), ['name' => 'Appeals'])->assertSessionHasErrors('name');

    $this->delete(route('letter-categories.destroy', $appeal))->assertRedirect(route('letter-categories.index'));
    expect(LetterCategory::whereKey($appeal->id)->exists())->toBeFalse();
});

test('letter templates list and edit through Inertia, with header images uploaded and removed', function (): void {
    Storage::fake('public');
    LetterCategory::create(['name' => 'Hearing']);

    $this->actingAs(inertiaStaff(['letters.view']))->get(route('letter-templates.index'))->assertRedirect(route('dashboard'));

    $this->actingAs(inertiaStaff(['letters.templet.view', 'letters.templet.create', 'letters.templet.update']));

    $this->get(route('letter-templates.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/LetterTemplates/Index')
            ->where('templates.data.0.title', 'Hearing notice')
            ->where('templates.data.0.placeholders', ['{case_number}', '{applicant_name}']));

    $this->get(route('letter-templates.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/LetterTemplates/Form')->where('template', null)->where('categories', ['Hearing']));

    $this->post(route('letter-templates.store'), [
        'title' => 'Summons',
        'placeholders' => "{case_number}\n{respondent_name}, {case_number}",
        'body' => 'Please attend.',
        'header_image' => UploadedFile::fake()->image('header.png'),
    ])->assertRedirect(route('letter-templates.index'));

    $summons = LetterTemplate::where('title', 'Summons')->firstOrFail();
    expect($summons->placeholders)->toBe(['{case_number}', '{respondent_name}'])
        ->and($summons->header_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($summons->header_image_path);

    $this->get(route('letter-templates.edit', $summons))
        ->assertInertia(fn (Assert $page) => $page
            ->where('template.placeholders', '{case_number}, {respondent_name}')
            ->where('template.headerImage.name', basename($summons->header_image_path))
            ->where('template.footerImage', null));

    $path = $summons->header_image_path;
    $this->post(route('letter-templates.update', $summons), [
        '_method' => 'patch',
        'title' => 'Summons v2',
        'body' => 'Please attend.',
        'remove_header_image' => '1',
    ])->assertRedirect(route('letter-templates.index'));

    expect($summons->fresh()->title)->toBe('Summons v2')->and($summons->fresh()->header_image_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('decision templates are sanitised on save and keep a single default', function (): void {
    $this->actingAs(inertiaStaff(['letters.templet.view']))->get(route('decision-templates.index'))->assertRedirect(route('dashboard'));

    $old = DecisionTemplate::create(['title' => 'Old default', 'body' => '<p>x</p>', 'is_default' => true]);
    $this->actingAs(inertiaStaff(['decision.templet.view', 'decision.templet.create', 'decision.templet.update']));

    $this->get(route('decision-templates.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/DecisionTemplates/Index')
            ->where('templates.data.0.title', 'Old default')
            ->where('templates.data.0.isDefault', true));

    $this->post(route('decision-templates.store'), [
        'title' => 'Final judgment',
        'is_default' => '1',
        'body' => '<p onclick="alert(1)">Ruling {{decision_content}}</p><script>alert(1)</script>',
    ])->assertRedirect(route('decision-templates.index'));

    $new = DecisionTemplate::where('title', 'Final judgment')->firstOrFail();
    expect($new->is_default)->toBeTrue()
        ->and($old->fresh()->is_default)->toBeFalse()
        ->and($new->body)->not->toContain('<script')->not->toContain('onclick')->toContain('Ruling');

    $this->get(route('decision-templates.edit', $new))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/DecisionTemplates/Form')->where('template.isDefault', true)->where('template.body', $new->body));
});

test('the letters list shows stats, filters by status and searches', function (): void {
    ($this->makeLetter)(['reference_number' => 'CIVI/1/26/01', 'approval_status' => 'approved', 'subject' => 'Approved one']);
    ($this->makeLetter)(['reference_number' => 'CIVI/2/26/01', 'subject' => 'Pending one']);
    ($this->makeLetter)(['reference_number' => 'CIVI/3/26/01', 'approval_status' => 'rejected', 'subject' => 'Rejected one']);

    $this->actingAs(inertiaStaff(['letters.create']))->get(route('letters.index'))->assertRedirect(route('dashboard'));

    $this->actingAs(inertiaStaff(['letters.view']))
        ->get(route('letters.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Letters/Index')
            ->where('stats.total', 3)
            ->where('stats.approved', 1)
            ->where('stats.rejected', 1)
            ->where('stats.topCategory', 'Hearing')
            ->where('stats.topCategoryCount', 3)
            ->has('letters.data', 3)
            ->where('filters', ['status' => '', 'q' => '']));

    $this->get(route('letters.index', ['status' => 'pending']))
        ->assertInertia(fn (Assert $page) => $page->has('letters.data', 1)->where('letters.data.0.subject', 'Pending one')->where('letters.data.0.status', null));

    $this->get(route('letters.index', ['q' => 'CIVI/3']))
        ->assertInertia(fn (Assert $page) => $page->has('letters.data', 1)->where('letters.data.0.status', 'rejected'));

    $this->get(route('letters.index', ['status' => 'bogus', 'q' => '100%']))
        ->assertInertia(fn (Assert $page) => $page->where('filters.status', '')->has('letters.data', 0));
});

test('composing a letter prefills the case and template and stores it with a case reference', function (): void {
    $applicant = partyApplicant('letters-compose');
    $caseId = partyCase($applicant, 'CIVI/00009/26', ['status' => 'active']);

    $this->actingAs(inertiaStaff(['letters.create', 'letters.view']))
        ->get(route('letters.compose', ['template_id' => $this->template->id, 'case_id' => $caseId]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Letters/Compose')
            ->where('selectedTemplateId', $this->template->id)
            ->where('caseNumber', 'CIVI/00009/26')
            ->where('nextReference', 'CIVI/00009/26/01')
            ->where('templates.0.excerpt', 'Dear {applicant_name}, case {case_number}.'));

    $this->post(route('letters.store'), [
        'template_id' => $this->template->id,
        'case_number' => 'CIVI/00009/26',
        'send_to_applicant' => true,
        'send_to_respondent' => false,
        'body' => '<p>Dear {applicant_name}, case {case_number}.</p><script>x</script>',
    ])->assertRedirect();

    $letter = Letter::latest('id')->firstOrFail();
    expect($letter->reference_number)->toBe('CIVI/00009/26/01')
        ->and($letter->body)->toContain('case CIVI/00009/26')->not->toContain('<script')
        ->and($letter->send_to_respondent)->toBeFalse();

    $this->post(route('letters.store'), [
        'template_id' => $this->template->id,
        'send_to_applicant' => false,
        'send_to_respondent' => false,
        'body' => '<p>x</p>',
    ])->assertSessionHasErrors('send_to_applicant');
});

test('approved letters cannot be edited, re-decided or deleted', function (): void {
    Mail::fake();
    $pending = ($this->makeLetter)();
    $approved = ($this->makeLetter)(['approval_status' => 'approved']);

    $this->actingAs(inertiaStaff(['letters.view', 'letters.update', 'letters.approve', 'letters.delete']));

    $this->get(route('letters.edit', $pending))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Letters/Edit')
            ->where('letter.id', $pending->id)
            ->where('letter.templateTitle', 'Hearing notice')
            ->where('letter.send_to_applicant', true));

    $this->get(route('letters.edit', $approved))->assertRedirect(route('letters.index'));

    $this->post(route('letters.approve', $approved), ['status' => 'rejected'])->assertSessionHas('error');
    expect($approved->fresh()->approval_status)->toBe('approved');

    $this->delete(route('letters.destroy', $approved))->assertSessionHas('error');
    expect(Letter::whereKey($approved->id)->exists())->toBeTrue();

    $this->post(route('letters.approve', $pending), ['status' => 'returned'])->assertRedirect(route('letters.index'));
    expect($pending->fresh()->approval_status)->toBe('returned');

    $this->patch(route('letters.update', $pending), ['body' => '<p>Changed</p>', 'send_to_applicant' => true])
        ->assertRedirect(route('letters.show', $pending));
    expect($pending->fresh()->body)->toContain('Changed');
});

test('the letter preview stays a printable page', function (): void {
    $letter = ($this->makeLetter)();

    $this->actingAs(inertiaStaff(['letters.view']))
        ->get(route('letters.show', $letter))
        ->assertOk()
        ->assertSee('a4-sheet', false);
});
