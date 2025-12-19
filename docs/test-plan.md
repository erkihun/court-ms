# Test Plan — Court MS (Question-Driven)

Date: 2025-12-18  
Scope: Functional and non-functional validation of the Court Management System across Applicant, Respondent, and Admin portals. Web UI, backend workflows, and document handling are in scope; mobile apps and external integrations are not.

## Objectives (ask and answer)
- Do the critical case, appeal, letter, and notification flows work end-to-end for every role?
- Are authorization, file handling, localization, and PDF/ICS generation protected against regressions?
- Do we have a fast smoke set for deployments and a deeper regression map for scheduled runs?

## Environments & Test Data
- Have you run `composer install && npm install`, `php artisan migrate --seed`, `php artisan storage:link`, and started the app (`php artisan serve` or web server) plus assets (`npm run dev`/`build`)?
- Are the sample accounts from `SampleUsersSeeder` available (`admin@example.com`, `staff1@example.com`, `viewer1@example.com`, password `password`) and do you have fresh applicant/respondent test users created via UI?
- Is mail trapping (MailHog/Mailpit) enabled to capture password reset, verification, and receipt emails, and is a real queue driver used when validating async jobs?
- Will you refresh the DB between runs (`php artisan migrate:fresh --seed`) to keep IDs predictable and prevent leaked files in `storage/app/public`?

## Test Types & Owners (prompt per type)
| Type | Question to answer | Tool/Notes |
| --- | --- | --- |
| Unit | Do helpers/policies/formatters and permission checks behave as specified? | Pest under `tests/Unit` |
| Feature/API | Do HTTP flows, controllers, and middleware (auth, throttling, signed URLs) respond as expected? | Pest under `tests/Feature`, run with `php artisan test` |
| Browser/E2E | Can users navigate happy paths (UI, uploads, rich text, locale switch) without blocking defects? | Manual now; plan for Laravel Dusk/Playwright |
| Accessibility/UX | Is keyboard nav/focus order/contrast/ARIA acceptable on main dashboards and forms? | Axe/Lighthouse spot checks |
| Non-functional | Are pagination limits, file-size limits, cache/storage linking, and dashboard response times within tolerance? | Spot checks per release |

## Smoke Suite (pre-deploy, 15–20 min)
- Applicant: Can a new applicant register, verify email, log in, create a case with file upload, view receipt PDF, email the receipt, and see dashboard counts update?
- Respondent: Can a respondent switch from applicant or log in directly, search a case with throttling respected, submit a response with a file, and download their own response?
- Admin: Can an admin log in, reach dashboard, review a case (accept/reject), assign to user/team, update status, add a hearing, upload/delete a case file, and post an admin message?
- Letters: Can an admin compose from a template, save, approve, and confirm the applicant public preview works?
- Notifications: Do mark-one and mark-all work for applicant/respondent/admin, and do unseen counts drop?
- Localization: When switching locale via `/language/{locale}`, do UI/validation messages change and does the signed `/signage` link reject tampering?

## API Smoke (token)
- Does `/api/health` return ok without auth?
- Can admin/applicant/respondent log in via `/api/v1/auth/login`, receive a Bearer token, and `/api/v1/me` return the right actor type?
- With the token, does `/api/v1/cases` list cases scoped to that actor (staff -> all with perm, applicant -> own, respondent -> invited cases)?
- Does `/api/v1/cases/{id}` return 403 when a case is outside the actor scope?
- Can an applicant create a case via `/api/v1/cases` (title/description/case_type_id/filing_date) and upload PDF evidence + witnesses?
- Can an applicant/staff post and read case messages via `/api/v1/cases/{id}/messages`?
- Can a respondent upload a PDF response via `/api/v1/responses` and fetch their own list/detail?

## Regression Coverage (deeper runs framed as questions)
- **Authentication & Identity**: Can applicants register/login/forgot/reset? Does respondent login redirect correctly? Does force-password-change fire for admins? Do sessions expire? Do email verification links (including legacy `/apply/email/verify`) and invalid tokens behave correctly?
- **Applicant Portal**: Can applicants create/edit/delete cases with required validation and file type/size limits enforced? Are witness/evidence deletions honored? Are file/evidence downloads secure? Do case messages post? Do receipt view/PDF/email, hearing ICS download, dashboard stats, notifications, and switch-to-respondent all work?
- **Respondent Portal**: Is case search throttled? Can a respondent view by case number, list "my cases", create/edit/delete responses, download their response, and manage notification preferences/mark read?
- **Admin Case Management**: Do case index filters/sort/export work with permissions? Does the review endpoint (accept/reject) enforce perms? Can admins assign to user/team, update status, create/update/delete hearings, post messages, upload/delete files, manage witnesses, and use registrar review and bench notes (perm: bench-notes.manage) without leaks?
- **Appeals & Decisions**: Can admins CRUD/submit/decide appeals, manage appeal documents (upload/download/delete), CRUD decisions, and add/edit/delete decision reviews with correct permission enforcement?
- **Letters & Templates**: Can admins CRUD templates with granular permissions, compose letters, edit/update/approve them, prevent improper delete after approval (if enforced), authorize public preview correctly, link reference numbers/cases, and sanitize rich text?
- **Users/Roles/Permissions**: Can admins create/edit/delete users, assign roles, CRUD roles, CRUD permissions, see 403 on unauthorized actions, and keep sidebar links consistent with perms?
- **Case Types, Terms, Settings**: Can admins CRUD case types and terms, update system settings (seal/logo paths), view audit read-only, and persist language switches?
- **Documents & Storage**: Does the signed `/signage` route reject tampering? Do secure file controllers block cross-user access? Is the storage symlink intact? Are orphan files cleaned when records delete?
- **Reporting & Analytics**: Does `/admin/dashboard/stats` return expected data? Are reports view/export guarded by perms? Do charts render without JS errors?
- **Localization & Content**: Are translation keys present for dashboard/cases? Is Ethiopian date formatting applied where expected? Does fallback locale behave?
- **Security & Resilience**: Is throttling active on sensitive routes (login, case search, verification resend)? Is XSS blocked on rich text (letters/templates)? Are CSRF tokens validated? Does logout revoke secure downloads? Is APP_DEBUG off in staging/prod?
- **Accessibility & UI**: Are table headers sticky/visible? Can all controls be focused? Are status chips readable on light/dark backgrounds? Do mobile dashboard tables/cards present correctly?

## Automation Backlog (turn into test questions)
- Feature: Can we automate applicant registration plus email verification?
- Feature: Can we automate applicant case creation with file upload and receipt PDF generation?
- Feature: Can we automate admin case review + status update + hearing add/remove with permission checks?
- Feature: Can we automate letter compose -> approve and ensure public preview requires the right user?
- Feature: Can we automate respondent response CRUD scoped to owner and assert 403 on others?
- Feature: Can we automate notifications mark-all per guard?
- Unit: Can we cover permissions policy matrices for cases/letters/appeals, Ethiopian date helper formatting, and locale middleware setting app locale from route?

## Reporting & Exit Criteria (questions to confirm)
- Did `php artisan test` pass and the smoke suite run on the target build with no open P1/P2?
- Did you capture evidence (UI screenshots, mail trap logs, storage listings for upload/delete)?
- If a test failed, did you log repro steps, data used, screenshots, and block release or obtain a waiver?

## References
- Routes: `routes/web.php`
- Workflow diagram: `docs/workflow.md`
- Security/audit notes: `docs/system-audit.md`
