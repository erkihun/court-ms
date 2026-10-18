# CLAUDE.md

Guidance for Claude Code when working in this repository.

## ⚠️ Production system

This is a **live tribunal court management system in production with real data**. Treat every change accordingly:

- **Never** run destructive DB operations (`migrate:fresh`, `migrate:rollback`, `db:wipe`, raw `DELETE`/`TRUNCATE`) against any environment without explicit confirmation.
- Flag production risk **before** suggesting any migration, seeder, or data-modifying change.
- Prefer additive, reversible migrations. Always implement `down()`.
- Run `npm run build` after CSS/JS changes to confirm assets compile cleanly.

## Project overview

Court/tribunal case management portal. Applicants file cases, respondents respond, and court staff (admins, judges, registrars) manage cases, hearings, decisions, letters, and inspections through a permissioned admin panel. Ethiopian calendar / Amharic (`am`) localization is supported.

## Tech stack

- **Backend:** Laravel 12, PHP 8.2, MySQL
- **Frontend:** Inertia.js v2 + React 19 + TypeScript (strict) + Tailwind CSS v4; Vite 7 build; Ziggy for typed routes
- **Charts:** Chart.js via react-chartjs-2
- **Rich text:** TinyMCE v8 (self-hosted under `public/vendor/tinymce`, wrapped by `components/ui/RichTextEditor.tsx`)
- **PDF:** barryvdh/laravel-dompdf + laravel-snappy/wkhtmltopdf (server-side), html2pdf.js (client-side)
- **QR codes:** simplesoftwareio/simple-qrcode
- **Auth:** Custom roles/permissions system (NOT Spatie); Laravel Sanctum for API
- **Auditing:** owen-it/laravel-auditing (+ custom system audit tables)
- **Monitoring:** Laravel Pulse
- **HTML sanitization:** mews/purifier + ezyang/htmlpurifier (see `app/Support/SafeHtml.php`)
- **Testing:** Pest 3

## Architecture

### Three portals, three guards
The app is split into three role-based areas, each with its own controllers, routes, layout, and (in some cases) auth guard:

- **Admin** — `app/Http/Controllers/Admin/`, routes under `/admin/*`, pages `resources/js/Pages/Admin/*` (layout `layouts/AdminLayout.tsx`, sidebar built server-side as the shared `navigation` prop). Court staff: cases, hearings, decisions, bench notes, letters, inspections, appeals, performance evaluations, users/roles/permissions, system settings, home/landing CMS.
- **Applicant** — `app/Http/Controllers/Applicant/`, pages `Pages/Applicant/*` (layout `layouts/PartyLayout.tsx`). File cases, view status, messages, notifications. Lawyers can act for applicants.
- **Respondent** — `app/Http/Controllers/Respondent/`, pages `Pages/Respondent/*` (same PartyLayout). Search/view cases against them and submit responses.

Public pages (landing page, resources, user manual, terms, hall signage) are `Pages/Public/*` (layout `layouts/PublicLayout.tsx`), served by `PublicLandingController`, `UserManualController`, `TermsDisplayController` and `PublicSignageController`. Landing content rules live in `app/Support/Public/LandingPage.php`.

### Frontend (Inertia + React)
- Entry `resources/js/app.tsx`; the root view is `resources/views/app.blade.php`. The default layout is chosen by page prefix (`Admin/*`, `Applicant/*`/`Respondent/*`, `Public/*`; `Auth/*` has none).
- Shared props come from `App\Http\Middleware\HandleInertiaRequests` (app info, `auth`, `navigation`, `toasts`); keep `resources/js/types/index.ts` in sync with it.
- Controllers pass explicit camelCase arrays (never raw models) and format dates on the server (`App\Support\EthiopianDate`). Larger pages use a presenter in `app/Support/` (e.g. `Support/Admin/CaseShowPage.php`).
- Translations: Laravel lang files are served to the client at `/api/i18n/{locale}.json`; use `t('group.key')` from `lib/i18n.ts`. Add new keys to both `lang/en` and `lang/am`.
- Routes: `route()` from `lib/route.ts`; run `npm run routes:types` after changing routes to regenerate `resources/js/types/ziggy.d.ts`.
- UI kit: `resources/js/components/ui/*` (Button, Card, Field, Input/Select/Textarea/Checkbox, Table, Pagination, Modal, ConfirmDialog, ConfirmButton, Tabs, DescriptionList, EthDateInput/EthCalendar, RichText, RichTextEditor…). Hooks: `useCan`, `useFilters`, `useShared`. Feature components go in `components/admin/<area>/`, `components/party/`, `components/public/`.
- `useCan()` only hides UI; every action is still authorised on the server.
- What stays Blade on purpose: printable/PDF documents (`admin/letters/preview`, `admin/recordes/record-pdf`, `applicant/cases/receipt*`, `pdf/*`), mail templates (`mail/*`) and error pages (`errors/*`).

### Auth & authorization
- Custom **roles & permissions** via `app/Models/Role.php`, `app/Models/Permission.php`, and helpers in `app/Helpers/permissions.php` (autoloaded via composer `files`).
- Middleware aliases and web-group middleware are registered in `bootstrap/app.php` (there is no `app/Http/Kernel.php` in Laravel 12):
  - `perm:<permission>` → `RequirePermission`
  - `role:<role>` → `RequireRole`
  - `use.guard:<guard>` → `UseGuard`
  - `act.respondent` → `ActAsRespondent`
  - `audit` → `SystemAuditMiddleware`
  - `force.password.change` → forces password reset flow
- Multiple session guards across portals; `SetSessionCookieForGuard` / `UseGuard` manage guard switching.
- Policies in `app/Policies/` (`CourtCasePolicy`, `UserPolicy`).

### Localization
- `SetLocale` runs on the route group in `routes/web.php` (after the session has started), intentionally NOT as global middleware. Don't move it to the global stack.

### API
- `app/Http/Controllers/Api/` with Sanctum tokens. Resources in `app/Http/Resources/`. Routes in `routes/api.php`.

### Key domain models (`app/Models/`)
`CourtCase`, `CaseType`, `CaseHearing`, `Decision`, `DecisionReview`, `BenchNote`, `Letter` / `LetterTemplate` / `LetterCategory`, `Applicant`, `Respondent`, `RespondentResponse`, `ApplicantResponseReply`, `CaseInspectionRequest` / `CaseInspectionFinding`, `Team`, `User`, `Role`, `Permission`, `PerformanceEvaluation*`, `Announcement`, and `Home*` CMS models.

## Directory map

- `app/Http/Controllers/{Admin,Applicant,Respondent,Api,Auth}/` — controllers by portal
- `app/Http/Middleware/` — custom middleware (guards, permissions, locale, audit)
- `app/Models/` — Eloquent models
- `app/Policies/`, `app/Providers/` — authorization
- `app/Support/SafeHtml.php` — HTML sanitization helper
- `app/Helpers/permissions.php` — permission helper functions (globally autoloaded)
- `resources/js/{Pages,layouts,components,hooks,lib,types}/` — Inertia/React frontend
- `resources/views/` — the Inertia root view plus the Blade print, PDF, mail and error templates
- `resources/css/app.css` — imports theme.css + motion.css (class-based dark mode, accent palettes via `data-accent` on `<html>`)
- `app/Support/` — presenters and helpers (`SafeHtml`, `EthiopianDate`, page presenters)
- `routes/{web,api,auth,channels,console}.php`
- `database/migrations/` — schema (chronological; many incremental ALTER migrations)
- `tests/{Feature,Unit}/` — Pest tests

## Commands

```bash
# Install / setup
composer install && npm install

# Dev (runs server + queue + vite concurrently)
composer run dev

# Build frontend assets (tsc + vite; run after CSS/JS changes and before tests, which need the Vite manifest)
npm run build
npm run typecheck
npm run routes:types   # after route changes

# Tests (Pest)
composer test            # clears config then runs php artisan test
php artisan test --filter=SomeTest

# Lint / format (PHP)
./vendor/bin/pint

# Migrations — additive/reversible only; NEVER fresh/wipe on real data
php artisan migrate
```

> Testing note: generate a testing key before running the suite — `php artisan key:generate --env=testing` (see README).

## Conventions

- Match existing code style; run **Pint** for PHP formatting before finishing.
- New migrations must be additive and implement `down()`.
- Sanitize any user-supplied HTML through `SafeHtml` / mews-purifier — do not trust raw input into TinyMCE-backed fields.
- Gate new admin actions behind `perm:`/`role:` middleware and matching policies.
- Keep portal separation: put controllers/views/routes in the correct portal namespace.
- New UI is an Inertia page in the right portal folder, built from `components/ui/*`; no `any`, typed props. Format TS/TSX with Prettier (`--single-quote --print-width 160 --tab-width 4`).
- Rich text in pages goes through `RichText` (server output already purified with `clean($html, 'cases')`).
- Tests: Pest feature tests with `assertInertia` (component + key props) and authorization cases; `inertiaStaff([...permissions])`, `partyApplicant()` and `partyCase()` helpers are in `tests/Pest.php`.
