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
- **Frontend:** Blade + Alpine.js v3 + Tailwind CSS v3, with Bootstrap 5 / AdminLTE 4 and jQuery in parts; Vite 7 build
- **Charts:** Chart.js
- **Rich text:** TinyMCE v8
- **PDF:** barryvdh/laravel-dompdf + laravel-snappy/wkhtmltopdf (server-side), html2pdf.js (client-side)
- **QR codes:** simplesoftwareio/simple-qrcode
- **Realtime:** Laravel Echo + Pusher
- **Auth:** Custom roles/permissions system (NOT Spatie); Laravel Sanctum for API
- **Auditing:** owen-it/laravel-auditing (+ custom system audit tables)
- **Monitoring:** Laravel Pulse
- **HTML sanitization:** mews/purifier + ezyang/htmlpurifier (see `app/Support/SafeHtml.php`)
- **Testing:** Pest 3

## Architecture

### Three portals, three guards
The app is split into three role-based areas, each with its own controllers, routes, layout, and (in some cases) auth guard:

- **Admin** — `app/Http/Controllers/Admin/`, routes under `/admin/*`, layout `resources/views/components/admin-layout.blade.php` (large, ~2000 lines). Court staff: cases, hearings, decisions, bench notes, letters, inspections, appeals, performance evaluations, users/roles/permissions, system settings, home/landing CMS.
- **Applicant** — `app/Http/Controllers/Applicant/`, layout `resources/views/layouts/applicant.blade.php`. File cases, view status, messages, notifications. Lawyers can act for applicants.
- **Respondent** — `app/Http/Controllers/Respondent/`. Search/view cases against them and submit responses.

Public-facing pages (home/landing CMS, public case lookup, signage, terms) live in `app/Http/Controllers/PublicController.php`, `PublicSignageController.php`, etc.

### Auth & authorization
- Custom **roles & permissions** via `app/Models/Role.php`, `app/Models/Permission.php`, and helpers in `app/Helpers/permissions.php` (autoloaded via composer `files`).
- Middleware aliases (registered in `app/Http/Kernel.php`):
  - `perm:<permission>` → `RequirePermission`
  - `role:<role>` → `RequireRole`
  - `use.guard:<guard>` → `UseGuard`
  - `act.respondent` → `ActAsRespondent`
  - `audit` → `SystemAuditMiddleware`
  - `force.password.change` → forces password reset flow
- Multiple session guards across portals; `SetSessionCookieForGuard` / `UseGuard` manage guard switching.
- Policies in `app/Policies/` (`CourtCasePolicy`, `UserPolicy`).

### Localization
- `SetLocale` middleware is registered in the **`web` group AFTER `StartSession`** (intentionally NOT global — see comments in `app/Http/Kernel.php`). Don't move it back to the global stack.

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
- `resources/views/{admin,applicant,respondent,public,components,layouts,partials,pdf,mail}/` — Blade views
- `resources/css/app.css` — imports theme.css + motion.css (class-based dark mode, accent palettes via `data-accent` on `<html>`)
- `routes/{web,api,auth,channels,console}.php`
- `database/migrations/` — schema (chronological; many incremental ALTER migrations)
- `tests/{Feature,Unit}/` — Pest tests

## Commands

```bash
# Install / setup
composer install && npm install

# Dev (runs server + queue + vite concurrently)
composer run dev

# Build frontend assets (run after CSS/JS changes)
npm run build

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
- Frontend is currently Blade + Alpine + Tailwind (with some Bootstrap/jQuery legacy). Prefer Tailwind + Alpine for new UI; reuse the `resources/views/components/ui/*` Blade components (card, table, badge, input, select, alert, etc.).
