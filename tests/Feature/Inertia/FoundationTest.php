<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Support\Translations;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/** @return list<array<string, mixed>> */
function flattenNavigation(array $sections): array
{
    return collect($sections)
        ->flatMap(fn (array $section): array => $section['items'])
        ->flatMap(fn (array $item): array => $item['type'] === 'group' ? $item['children'] : [$item])
        ->values()
        ->all();
}

test('converted pages render through Inertia with the shared props', function (): void {
    $user = inertiaStaff(['cases.types', 'cases.view']);

    $this->actingAs($user)
        ->get(route('case-types.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CaseTypes/Index')
            ->has('types.data')
            ->where('filters.q', '')
            ->where('auth.user.id', $user->id)
            ->where('auth.user.permissions', fn ($permissions) => collect($permissions)->sort()->values()->all() === ['cases.types', 'cases.view'])
            ->where('auth.applicant', null)
            ->has('app.translationsUrl')
            ->has('navigation'));
});

test('administrators are shared a wildcard permission', function (): void {
    $this->actingAs(inertiaStaff([], 'admin'))
        ->get(route('case-types.index'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user.permissions', ['*']));
});

test('the sidebar only offers links the user may open and marks the active one', function (): void {
    $this->actingAs(inertiaStaff(['cases.types', 'cases.view']))
        ->get(route('case-types.index'))
        ->assertInertia(function (Assert $page): void {
            $links = collect(flattenNavigation($page->toArray()['props']['navigation']));
            $keys = $links->pluck('key');

            expect($keys)->toContain('case-types.index', 'cases.index', 'dashboard')
                ->not->toContain('users.index', 'settings.system.edit', 'admin.landing.index', 'decisions.index');
            expect($links->firstWhere('key', 'case-types.index')['active'])->toBeTrue()
                ->and($links->firstWhere('key', 'cases.index')['active'])->toBeFalse();
        });
});

test('flash messages are shared as resolved toast text', function (): void {
    $this->actingAs(inertiaStaff(['cases.types']))
        ->withSession(['success' => ['key' => 'messages.success.created', 'replace' => ['resource' => 'X']]])
        ->get(route('case-types.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('toasts.0.type', 'success')
            ->where('toasts.0.message', __('messages.success.created', ['resource' => 'X'])));
});

test('an Inertia visit to a page that is still Blade becomes a full page load', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request())])
        ->get(route('admin.notifications.index'))
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('admin.notifications.index'));
});

test('an Inertia visit that hits a missing page renders the React error page', function (): void {
    $this->actingAs(inertiaStaff(['cases.types']))
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request())])
        ->get(route('case-types.edit', 999999))
        ->assertNotFound()
        ->assertJsonPath('component', 'Error')
        ->assertJsonPath('props.status', 404);
});

test('translations are served without a session cookie and include the fallback locale', function (): void {
    config(['app.fallback_locale' => 'en']);

    $response = $this->get(route('i18n.messages', ['locale' => 'am']))
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie');

    expect($response->json()['app.Dashboard'] ?? null)->toBe(trans('app.Dashboard', [], 'am'))
        ->and($response->headers->get('Cache-Control'))->toContain('max-age=0');

    // A key only the fallback locale has is still present.
    $onlyInFallback = collect(Translations::messages('en'))->keys()
        ->first(fn (string $key): bool => ! trans()->hasForLocale($key, 'am') && ! str_contains($key, ' '));
    if ($onlyInFallback !== null) {
        expect($response->json())->toHaveKey($onlyInFallback);
    }
});

test('the versioned translations URL is cacheable forever', function (): void {
    $url = route('i18n.messages', ['locale' => 'en', 'v' => Translations::version('en')]);

    expect($this->get($url)->headers->get('Cache-Control'))->toContain('immutable')->toContain('max-age=31536000');
});

test('an unknown locale is not served', function (): void {
    $this->get('/api/i18n/xx.json')->assertNotFound();
});

test('the notification feed needs a signed-in staff member', function (): void {
    $this->getJson(route('admin.notifications.feed'))->assertUnauthorized();
});

test('the notification feed and count agree and stay within the user\'s cases', function (): void {
    $typeId = DB::table('case_types')->insertGetId(['name' => 'Feed type', 'created_at' => now(), 'updated_at' => now()]);
    foreach (range(1, 3) as $i) {
        DB::table('court_cases')->insert([
            'case_number' => 'FEED-'.$i, 'title' => 'Feed case '.$i, 'case_type_id' => $typeId,
            'status' => 'pending', 'filing_date' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $viewer = inertiaStaff(['cases.view']);
    $feed = $this->actingAs($viewer)->getJson(route('admin.notifications.feed'))->assertOk();
    $count = $this->actingAs($viewer)->getJson(route('admin.notifications.count'))->json('count');

    expect($feed->json('count'))->toBe(3)->and($count)->toBe(3)
        ->and(collect($feed->json('groups'))->firstWhere('type', 'case')['items'])->toHaveCount(3);

    // Someone without cases.view sees nothing.
    $this->actingAs(inertiaStaff(['cases.types']))->getJson(route('admin.notifications.feed'))->assertJsonPath('count', 0);
});
