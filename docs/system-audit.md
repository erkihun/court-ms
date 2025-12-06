# System Audit – Court MS

Date: 2025-12-06  
Scope: High-level review of current Laravel application (letters, users, templates, settings, cases). No source code changes were executed as part of this audit.

## Application & Architecture
- Framework: Laravel 12.x, PHP 8.2.x. Verify `config/app.php` env alignment between environments (app key, debug off in prod).
- Modules observed: Letters (composition, approval, preview), Users (signature/stamp), System Settings (seal/logo), Cases.
- Storage usage: public disk for uploaded assets (header/footer images, seals, signatures). Confirm storage symlink and correct permissions in each environment.

## Authentication & Authorization
- Auth: Laravel default; confirm password hashing defaults (bcrypt/argon). Enforce HTTPS and HSTS at the web server.
- Authorization: ensure policies/gates exist for letters/users/cases. Verify controller actions (approve/delete/update) are policy protected.
- Sessions: set `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax` (or strict if possible).

## Input Validation & Templating
- Validation present in letters create/update; add/confirm validation for approval actions and any bulk endpoints.
- Rich text: body is purified via Mews Purifier in preview; ensure Purifier runs on save or before render everywhere user HTML is allowed.
- Blade: escape output by default; audit any `{!! !!}` blocks (letters body, templates) to ensure sanitized content only.

## File Uploads
- Assets: signatures/seals/templates stored under `storage/app/public`. Enforce mime/size validation on all uploads. Strip executable extensions. Consider virus scanning in CI/CD or at upload.
- Public paths: ensure responses use `asset('storage/...')` only for files meant to be public.

## Data Protection
- Secrets: keep `.env` out of VCS; rotate APP_KEY if leaked. Audit database credentials and mail creds.
- Backups: confirm automated DB/file backups, retention, and tested restores.
- PII: identify tables with personal data (users, cases). Enable database encryption at rest and TLS in transit.

## Logging & Monitoring
- Laravel logs: ensure daily rotation and central aggregation in production.
- Audit trail: add activity logs for create/update/delete/approve actions on letters, users, cases (who, when, before/after).
- Error handling: turn off `APP_DEBUG` in prod; send critical exceptions to an APM (Sentry/Bugsnag).

## Email & Notifications
- Ensure `MAIL_FROM_ADDRESS`/`NAME` configured. If sending letters/approvals by email, log delivery status and failures.
- Rate-limit outbound mail to prevent abuse.

## Jobs & Queues
- If using queues for PDF generation/notifications, ensure a supervisor/PM2 monitors workers; set `QUEUE_CONNECTION` to a non-sync driver in prod.

## Approval Workflow
- Approval endpoint now stores status and approver identity; ensure only authorized roles can approve/return/reject.
- Lock updates/deletes after approval (implemented in controllers); verify front-end blocks are consistent with back-end rules.

## Frontend & UX
- Sticky table header and condensed rows implemented. Confirm accessibility: aria labels on buttons, adequate contrast with blue/orange palette.
- Pagination set to 5 rows; ensure pagination controls remain visible on mobile.

## Performance
- Eager loading: letters index uses `with(['template','author'])`; verify other heavy listings do the same to avoid N+1.
- Caching: consider caching system settings (seal/logo) and templates where possible.

## Deployment & CI/CD
- Add automated tests (feature + unit) for letters workflow (compose, approve, lock). Run in CI on each push.
- Run `composer audit` and `npm audit` in CI; track and patch vulnerabilities.
- Use environment-specific config for storage/mail/queue; do not hard-code URLs.

## Recommendations (Next Actions)
1. Add/verify policies for all CRUD/approve actions (letters/users/cases); restrict approvals to proper roles.
2. Centralize upload validation (mime/size) and add optional AV scanning; ensure signatures/seals cannot be script files.
3. Harden prod config: `APP_DEBUG=false`, secure session/cookie flags, HTTPS/HSTS at the edge.
4. Implement activity/audit logging for approvals and destructive actions.
5. Add tests covering approval lockouts (no edit/delete after approval) and PDF/preview rendering with purified body.
6. Set up monitoring/alerting (APM, log aggregation) and scheduled backups with restore drills.
7. Document release/runbooks and incident response (rollback, credential rotation, contact list).

---
Notes: This is a high-level audit summary. For a deeper audit, include dependency scan reports, route/middleware inventory, policy coverage, and a review of server/runtime hardening.
