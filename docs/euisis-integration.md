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

Everything is off until `EUISIS_ENABLED=true` (Flow 1) and an integration token is
issued (Flows 2 and 3). Existing screens, forms and the person-facing API are unchanged.

## Set up

```env
EUISIS_ENABLED=true
EUISIS_URL=https://euisis.example.gov.et
EUISIS_TOKEN=<token of the court-ms application registered in EUISIS API Management>
EUISIS_TIMEOUT=15
EUISIS_VERIFY_TLS=true
```

```bash
php artisan migrate
php artisan euisis:sync            # first run bootstraps the organization cache
php artisan euisis:integration-token --ip=<EUISIS server IP>   # prints the EUISIS token once
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

How updates are found: the admin screens write cases with query-builder updates, which
fire no model events, so before each poll (and in `euisis:sync`) court-ms compares each
referred case's current state with what it last reported. `hearing_at` is the next
hearing, or the last one when none is ahead; the decision fields come from the latest
published decision; `review_reason` is the review note when a case is returned or
rejected.

## Tests

`tests/Feature/Euisis/IntegrationApiTest.php` and `EuisisSyncTest.php`. They run on the
`court_ms_test` database from `.env.testing`:

```bash
DB_DATABASE=court_ms_test php vendor/bin/pest tests/Feature/Euisis
```
