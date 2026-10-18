# Release runbook: React frontend and security fixes (October 2026)

This release replaces the Blade/Alpine frontend with Inertia + React + TypeScript across all
portals and fixes the security issues found in the end-to-end review. It is large (about 650 files),
so roll it out through staging, check access before go-live, and keep the previous release ready
to redeploy.

**Database change:** one additive migration, `2026_10_18_000001_add_title_to_bench_notes_table`
(nullable `bench_notes.title`; skipped if the column already exists).

---

## 1. Before deploying

1. **Back up** the database and `storage/app` (uploaded case files) and confirm the backup restores.
2. **Tag the current production release** (for example `git tag pre-react-2026-10`) and keep its
   built `public/build` directory, so rolling back is a redeploy and not a rebuild.
3. **Check who loses access.** Several permission checks moved from "hide the button" to
   "enforced on the server". On staging, pointed at a fresh copy of the production database, run:

   ```bash
   php artisan access:upgrade-report          # table per check
   php artisan access:upgrade-report --json   # same, machine-readable
   ```

   The command only reads. For each role it lists the missing permissions and whether staff will
   notice. Where the old behaviour was intended, grant the listed permissions under
   **Users & roles → Roles** on production before go-live. Items marked "no visible change"
   only refuse requests the old pages never offered.
4. **Queue worker.** Notification mail (hearings, messages, status changes, approved letters,
   respondent views) is now sent by the queue worker. Production already uses
   `QUEUE_CONNECTION=database`; make sure a supervised `php artisan queue:work` is running
   and that `APP_URL` is the public address (queued mail builds its links from it).

## 2. Staging test (with court staff, in Amharic and English)

Use a production-data copy, the real roles, and phones as well as desktops. Sign off each line.

| Area | Who | Check |
|---|---|---|
| Sign-in | Staff, applicant, respondent | Login, password reset by code, MFA, forced password change |
| Case list | Registrar | Filters, Ethiopian date range, CSV export |
| Case page | Registrar, team leader, member | Accept / return / reject; status change; assignment; team members see only their cases |
| Hearings | Registrar | Add on a free day (08:00–16:00 slots), edit/delete a future hearing, past hearings locked |
| Messages and files | Registrar | Send a message (applicant gets the email shortly after), upload and delete a file |
| Letters | Clerk, approver | Compose from a case ("Write letter"), edit, approve once, print preview |
| Decisions | Judge | Create with the panel, publish, download the PDF |
| Bench notes | Judges | Title is saved; a colleague cannot edit or delete your note |
| Applicant portal | Applicant, lawyer | File a case with documents, edit while pending, reply to a response, receipt PDF |
| Respondent portal | Respondent | Search with access code, respond, view a returned response |
| Settings and CMS | Administrator | System settings tabs, landing page editor, user manual, terms |
| Public | Anyone | Landing page, resources, signage screen (initials only) |
| Dashboard | Each role | Figures appear for roles with `cases.view` |

## 3. Deploy

```bash
php artisan down                            # optional, for a short window
git fetch && git checkout <release>
composer install --no-dev --optimize-autoloader
npm ci && npm run build                     # also copies TinyMCE into public/build/vendor/tinymce
php artisan migrate --force                 # the one additive migration
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart                   # workers pick up the new mail classes
php artisan up
```

`public/vendor/tinymce` is no longer used; the editor loads from `public/build`. Deleting the old
folder from the server is optional.

## 4. Rollback

1. Let the queue drain first (`php artisan queue:work --stop-when-empty`, or wait until the
   `jobs` table is empty) so no mail queued by the new release is left for the old code.
2. `git checkout pre-react-2026-10`, restore that release's `public/build`, then
   `composer install --no-dev`, `php artisan optimize:clear`, rebuild the caches and
   `php artisan queue:restart`.
3. Leave the database as it is. The new `bench_notes.title` column is nullable, and the old code
   already writes to it when it exists, so no `migrate:rollback` is needed. Rolling it back would delete the titles entered
   since go-live.

## 5. First week: what to watch

- **403s by role** (Pulse slow/failed requests, `storage/logs`): a burst from one role means a
  permission is missing. Grant it rather than rolling back.
- **Failed jobs:** `php artisan queue:failed`. Mail retries three times (after 1 and 5 minutes)
  before landing there; `php artisan queue:retry all` after fixing SMTP.
- **500s** in `storage/logs/laravel-*.log` and the system audit log.
- **Feedback channel:** one person collects staff reports daily for the first week.

## 6. Notice to staff

### English

> **New look for the court system.** From **[date]** the court system has a new, faster
> interface. Your accounts, cases and documents are unchanged. What is different:
>
> - "Write letter" on a case now opens the letter page with the case already filled in.
> - Hearing times are chosen from court hours (2:00 morning to 10:00 afternoon, Ethiopian time).
> - Bench notes can be edited or deleted only by the judge who wrote them, or an administrator.
> - Roles that are in use, and the administrator role, cannot be deleted.
> - The public screen shows applicants' initials instead of their names.
> - The dashboard shows case figures to staff who have access to cases.
>
> If something you used to do is no longer possible, tell **[contact]** and include the page
> and your role.

### Amharic

> **የፍርድ ቤቱ ሥርዓት አዲስ ገጽታ።** ከ **[ቀን]** ጀምሮ የፍርድ ቤቱ ሥርዓት አዲስና ፈጣን ገጽታ ይኖረዋል።
> መለያዎ፣ መዝገቦችና ሰነዶች አልተቀየሩም። የተቀየሩት፦
>
> - በመዝገብ ገጽ ላይ "ደብዳቤ ጻፍ" የመዝገቡ ቁጥር አስቀድሞ የተሞላበትን የደብዳቤ ገጽ ይከፍታል።
> - የችሎት ሰዓት የሚመረጠው ከሥራ ሰዓት ውስጥ ነው (ከጠዋቱ 2:00 እስከ ከሰዓት 10:00)።
> - የችሎት ማስታወሻን ማስተካከል ወይም መሰረዝ የሚችሉት የጻፉት ዳኛ ወይም አስተዳዳሪ ብቻ ናቸው።
> - በተጠቃሚዎች የተያዘ ኃላፊነትና የአስተዳዳሪ ኃላፊነት ሊሰረዙ አይችሉም።
> - የሕዝብ ማሳያ ስክሪኑ የአመልካቾችን ሙሉ ስም ሳይሆን የስማቸውን የመጀመሪያ ፊደላት ብቻ ያሳያል።
> - ዳሽቦርዱ የመዝገብ መረጃዎችን የሚያሳየው መዝገቦችን የማየት ፈቃድ ላላቸው ሠራተኞች ነው።
>
> ቀድሞ ይሠሩት የነበረ ተግባር አሁን የማይቻል ከሆነ ገጹንና ኃላፊነትዎን ጠቅሰው ለ **[ተጠሪ]** ያሳውቁ።
