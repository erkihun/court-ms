# EUISIS integration

court-ms and EUISIS (the city HR system, also called Agelgay) connect API to API; neither
reads the other's database. The decision record is EUISIS ADR 0013
(`docs/architecture/adr/0013-court-ms-integration.md` in the EUISIS repository), and its
runbook is `docs/integration/court-ms.md` there.

| Flow | Direction | What court-ms does |
| --- | --- | --- |
| 1 Master data | court-ms reads EUISIS | Mirrors organizations and units, positions of linked people and the employees who are parties, then follows the EUISIS change feed (`euisis:sync`, every 5 minutes) |
| 2 Referral | EUISIS sends to court-ms | `POST /api/v1/integration/referrals` opens a case in intake review (`review_status = awaiting_review`, `source = euisis`) |
| 3 Status | EUISIS reads court-ms | `GET /api/v1/integration/referrals/updates?since=<cursor>` lists changes of referred cases |
| 4 Employee portal | EUISIS reads court-ms | `POST /api/v1/integration/people/court-summary` gives an employee's cases and notices |
| 5 Filing and letters | EUISIS sends to court-ms; EUISIS reads court-ms | `POST /api/v1/integration/people/cases` files an employee's case; `GET /api/v1/integration/letters/updates` lists letters to EUISIS institutions |
| 6 Case workspace | EUISIS asks court-ms, as the employee | `POST /api/v1/integration/people/cases/{case}/…` runs the applicant portal's own actions for the employee's linked account |

Everything is off until `EUISIS_ENABLED=true` (Flow 1) and an integration token is
issued (Flows 2 to 6). Existing screens, forms and the person-facing API are unchanged.

## Set up

```env
EUISIS_ENABLED=true
EUISIS_URL=https://euisis.example.gov.et
EUISIS_TOKEN=<token of the court-ms application registered in EUISIS API Management>
EUISIS_TIMEOUT=15
EUISIS_VERIFY_TLS=true
# EUISIS_PARTIES_ONLY=true      # on with EUISIS_ENABLED; see "Filing from EUISIS (Flow 5)"
# EUISIS_PORTAL_IN_EUISIS=true  # on with EUISIS_PARTIES_ONLY; see "Case workspace (Flow 6)"
EUISIS_PORTAL_URL=https://euisis.example.gov.et/my-portal/court-cases
```

```bash
php artisan migrate
php artisan euisis:sync            # first run bootstraps the organization cache
php artisan euisis:integration-token --ip=<EUISIS server IP>   # prints the EUISIS token once
php artisan euisis:integration-token --refresh-abilities      # an older token gains the Flow 4 to 6 abilities, unchanged
```

The scheduler must run (`* * * * * php artisan schedule:run`); it runs `euisis:sync`
every five minutes. `--bootstrap` pages through the EUISIS directory again.

## Data

All schema changes are additive and reversible:

| Table | Change |
| --- | --- |
| `euisis_organizations`, `euisis_organization_units`, `euisis_positions`, `euisis_employees`, `euisis_sync_state` | New: the EUISIS cache and the change-feed cursor. Positions and employees are cached only for people someone linked. |
| `respondents` | `euisis_organization_uuid`, `euisis_employee_uuid` (nullable) |
| `applicants` | `euisis_employee_uuid` (nullable) |
| `court_cases` | `source`, `euisis_referral_id` (unique), `respondent_organization_uuid` (nullable) |
| `integration_clients` | New: machine tokens for `/api/v1/integration/*`, stored as SHA-256 hashes |
| `euisis_referrals`, `euisis_case_updates` | New: what EUISIS sent, and the change list it polls |

Free-text fields (`respondent_name`, `organization_name`, `position`) are never changed by
the integration.

## Screens

- **Admin case page → Case summary → EUISIS panel**: where a referred case came from
  (grievance reference, EUISIS tribunal number, complainant, referring officer), the
  respondent organization as EUISIS knows it (pick from the cache; a warning when EUISIS
  lists it as archived or merged), and the applicant or complainant as an EUISIS employee
  (find by employee number). Linking needs `cases.edit`.
- **Applicant case filing form**: an optional "Respondent organization (city directory)"
  list, shown once the cache has organizations.

## Integration API

Bearer token from `euisis:integration-token`. These tokens are not Sanctum tokens: the
person-facing API never accepts them, and a staff token never opens these routes.
Errors are `{message, error_code, request_id}`; `401 unauthenticated`,
`403 ip_not_allowed`, `403 ability_missing`, `422` with `errors`.

| Route | Ability | Notes |
| --- | --- | --- |
| `GET /api/v1/integration/case-types` | `referrals:read` | `{data: [{id, name, prefix}]}` |
| `POST /api/v1/integration/referrals` | `referrals:create` | JSON or multipart. `referral_id` (the EUISIS grievance UUID) is the idempotency key: `201` the first time, `200` with the same case after. Fields: `case_type_id`, `title`, `summary`, `relief_requested?`, `complainant{name, employee_uuid?, phone?, email?}`, `respondent{name, organization_uuid?, address?}`, `referring_officer?`, `grievance_reference?`, `tribunal_case_number?`, `documents[]` (PDF, ≤ 10 MB, ≤ 10) with `document_titles[]` |
| `GET /api/v1/integration/referrals/{referral_id}` | `referrals:read` | The referral and its case number and states |
| `GET /api/v1/integration/referrals/updates?since=&limit=` | `referrals:read` | `{data: [{cursor, referral_id, case_number, review_status, review_reason, status, hearing_at, decision_date, decision_summary, changed, occurred_at}], next_cursor, has_more}` |
| `POST /api/v1/integration/people/court-summary` | `people:read` | Body `{employee: {uuid, national_id?, email?}, locale?}`; `locale` (`en`/`am`) words the notices. `{data: {linked: false}}`, or `{data: {linked: true, applicant, counts{cases, open, awaiting_review, upcoming_hearings, unread_notices}, cases[{id, case_number, title, respondent, case_type, status, review_status, filed_at, next_hearing_at}], notices[{id, title, message, created_at, case_id}]}}`; a notice `id` is `type:source` (`hearing:3`). Reads only |
| `POST /api/v1/integration/people/sign-in-tickets` | `people:sign-in` | `410 portal_in_euisis` while the portal is closed (Flow 6). Otherwise body `{employee: {uuid, national_id, first_name, middle_name?, last_name, gender, email, phone, position?, organization_name?, address?}, target}` (`gender` `male` or `female`, needed when the account is opened); `target` is `dashboard`, `cases`, `new_case`, `notifications`, `profile` or `case:<id>` of the employee's own case. `201 {data: {url, expires_at}}`. `409` with `error_code` `linked_to_another_employee`, `identity_mismatch`, `employee_record_incomplete`, `phone_in_use` or `account_inactive`; `404 unknown_target` |
| `POST /api/v1/integration/people/cases` | `people:file` | Multipart. The employee files a case from the EUISIS employee portal. `employee{…}` as for tickets, `filing_id` (UUID, the idempotency key: `201` the first time, `200` with the same case after), `case_type_id`, `description`, `relief_requested?`, `respondent{organization_uuid, name, address?}`, `grievances` (a JSON string, ≤ 20: `[{uuid, reference?, subject, category?, status?, description?, submitted_at?, closed_at?, responses[{body, at?}], decisions[{number?, type?, text, at?}]}]`), `documents[]` (PDF, ≤ 10 MB, ≤ 20) with `document_titles[]` and `document_sources[]`. `{data: {case_id, case_number, review_status, status}}`; `409` as for tickets |
| `POST /api/v1/integration/people/cases/{case}/workspace` | `people:read` | Body `{employee_uuid}` (every Flow 6 call). The case page: `case`, `docs`, `witnesses`, `hearings`, `messages`, `files`, `respondentResponses`, approved `letters`, downloadable `decisions`, `review{status, note}`, `can{modify}`. `404 not_linked` (no court account), `409 account_inactive`, `404 not_found` (not the employee's case) |
| `POST …/cases/{case}/edit`, `…/responses/{response}`, `…/responses/{response}/replies/{reply}` | `people:read` | The correction form's values, a respondent response with the employee's replies, one reply |
| `POST …/cases/{case}/download` | `people:read` | `{kind, id?, response_id?}`, `kind` one of `evidence`, `file`, `response`, `reply`, `decision`, `letter`, `receipt`, `hearing` (an `.ics`). The file, as the portal serves it |
| `POST …/people/notices`, `…/people/notices/read` | `people:read`, `people:act` | The notice feed (`page`); mark one read (`id`, `type:source`) or all (`all: true`) |
| `POST …/cases/{case}/messages`, `/files`, `/files/{file}/delete`, `/evidences/{evidence}/delete`, `/witnesses/{witness}/delete`, `/correct` | `people:act` | The portal form's own fields (`body`; `label`, `file`; the edit form's fields with `evidence_files[]`, `evidence_titles[]`, `witnesses[]`). `{data: {ok, message}}`; `422` with the portal's `errors`; `409 refused` when the portal would refuse (the case moved on) |
| `POST …/responses/{response}/replies`, `/replies/{reply}/update`, `/replies/{reply}/delete` | `people:act` | A reply (`description`, `pdf`); as above |
| `GET /api/v1/integration/letters/updates?since=&limit=` | `letters:read` | Approved letters for EUISIS institutions: `{data: [{cursor, letter_id, organization_uuid, case_number, case_title, subject, reference_number, recipient, approved_by, approved_at, pdf_url}], next_cursor, has_more}` |
| `GET /api/v1/integration/letters/{letter}/pdf` | `letters:read` | The approved letter as a PDF; `404` unless it is in the delivery queue |

How updates are found: the admin screens write cases with query-builder updates, which
fire no model events, so before each poll (and in `euisis:sync`) court-ms compares each
referred case's current state with what it last reported. `hearing_at` is the next
hearing, or the last one when none is ahead; the decision fields come from the latest
published decision; `review_reason` is the review note when a case is returned or
rejected.

## Employee portal (Flow 4)

Applicants who work for the city are EUISIS employees, so the EUISIS employee portal has a
Court cases section. EUISIS reads the summary for the signed-in employee; filing (Flow 5)
and every step on a case (Flow 6) happen there too.

- **Who the employee is here.** The applicant linked to their EUISIS employee UUID
  (`applicants.euisis_employee_uuid`). Before the first link, the applicant with their
  national ID, then their email; an email match with a different national ID is
  refused. A match linked to another employee is never taken over. With no match, the
  first filing opens a court account from the EUISIS record (national ID,
  phone, email and gender, male or female, are required: gender is never guessed; the
  address may be empty; the password is random and never used; the email counts as
  verified).
- **Tickets** (`applicant_sign_in_tickets`), only while the portal is open
  (`EUISIS_PORTAL_IN_EUISIS=false`): 64 random characters, stored as a SHA-256 hash, valid
  two minutes, used once (claimed atomically). `GET /applicant/euisis/sign-in/{ticket}`
  signs the person in as an applicant and opens the target.
- **Reading never writes**: the summary links nothing and opens no account. Notices carry
  the case they concern (`case_id`), never a link.

## Filing from EUISIS (Flow 5)

Employees file from their EUISIS employee portal, not from court-ms; a grievance officer
still refers cases (Flow 2). Only employees and institutions registered in EUISIS may sue
or be sued.

- **Parties only** (`EUISIS_PARTIES_ONLY`, on with `EUISIS_ENABLED`): the applicant
  portal hides "New case" and sends `applicant.cases.create`/`store` back to the case list
  with a notice; a referral needs `complainant.employee_uuid` and
  `respondent.organization_uuid`. The portal still follows, answers and messages cases.
- **The filing** (`POST /people/cases`): the employee's court account is found or opened
  as in Flow 4; the respondent must be in the mirrored EUISIS directory once court-ms has
  one. The case title is the applicant's name, as on the applicant form. The case starts
  `pending`, `awaiting_review`, with `source = euisis_employee`,
  `euisis_filing_id` and `respondent_organization_uuid`.
- **The grievance record**: each attached grievance, with its responses and decisions as
  plain text, is a row of `case_euisis_grievances`; staff see it on the case summary
  ("EUISIS grievance record"). The PDFs (complaint evidence, decision letters, the
  employee's documents) are case documents.
- **Letters to the institution**: when a letter on a case against an EUISIS institution
  is approved with "send to respondent", it joins `euisis_letter_deliveries`; EUISIS reads
  the queue after its cursor with each PDF and delivers it to that institution.

## Case workspace (Flow 6)

Employees follow and answer their cases in the EUISIS employee portal, so court-ms's own
applicant portal is not used (owner decision, EUISIS ADR 0013 #12).

- **As the employee, call by call** (`EuisisCaseWorkspaceController`): every call names
  the employee by `employee_uuid` in the body. The linked applicant account is signed in
  on the `applicant` guard for that request only, with an in-memory session, and the
  applicant portal's own controller action runs unchanged: its validation, ownership
  checks, review rules, audit rows and staff notifications. A page comes back as its
  props, a redirect as `{ok, message}` (its error flash as `409 refused`, its validation
  errors as `422`), a file as the file. Opening the case page marks its messages, status
  changes and hearings as seen, as in the portal.
- **Portal gone** (`EUISIS_PORTAL_IN_EUISIS`, on with `EUISIS_PARTIES_ONLY`): every
  `applicant`, `respondent` and `apply` URL, including sign-in, registration and password
  reset, is not found (`404`), before any sign-in redirect, and a court account still
  signed in is signed out. The route names stay registered for the portal controllers the
  workspace API runs. `/` opens the public site, which links parties to
  `EUISIS_PORTAL_URL` (My Portal). Respondents can no longer answer in court-ms either.
  Staff screens and the integration API are untouched.
- **Staff sign-in** (`/login`) has the court's own look: the case path, the official name
  and, while the portal is closed, a note sending parties to My Portal. The public site
  does not link to it.

## Public site

The public site (`/home`, `/news`, `/resources`, `/terms`, `/user-manual`) is laid out as
an administrative court's site, after the ILO and World Bank administrative tribunals
and Germany's Federal Administrative Court: the court's statement with today's sittings,
its services, about the court and its members, the hearing schedule (case number and
type only, never a party's name), figures, how a case proceeds, news (the active
Announcements), publications, questions and the registry's contact details.

Everything on it is managed under System Settings > Landing Page, in English and
Amharic: hero slides, About the Court (mandate and key facts), Members (composition, with
photos), service cards (each may link somewhere), statistics labels, timeline, resources,
user manual, FAQs, footer and contact (parent authority, address, office hours, map link,
social links) and each section's visibility and headings. A row keeps its Amharic text in
`translations` (`{"am": {field: text}}`, migration `2026_10_28_000005`); Amharic visitors
see it, or the English where a field is empty, and a list with no Amharic at all keeps the
reviewed built-in Amharic of `lang/am/home.php`.

## Tests

`tests/Feature/Euisis/IntegrationApiTest.php`, `EuisisSyncTest.php`, `EmployeeCourtAccessTest.php`, `EmployeeFilingTest.php`, `CaseWorkspaceTest.php` and `PortalInEuisisTest.php`. They run on the
`court_ms_test` database from `.env.testing`:

```bash
DB_DATABASE=court_ms_test php vendor/bin/pest tests/Feature/Euisis
```
