# Court MS User Manual

Prepared from the current codebase on 2026-03-27 by reviewing routes, controllers, views, and existing project docs.

## 1. Scope

This manual covers the current behavior of the Court Management System (`court-ms`) for:

- Public visitors
- Applicants
- Respondents
- Admin and court staff users
- API consumers

It reflects the implementation currently wired in the repository, not an idealized workflow.

## 2. System Overview

Court MS is a Laravel-based court case management system with four main access layers:

| User Type | Purpose | Main Access |
| --- | --- | --- |
| Public visitor | View the public landing page, terms, and signage dashboard | `/home`, `/terms`, `/signage` |
| Applicant | Register, submit cases, upload evidence, track status, receive letters, reply to respondent responses | `/applicant/login` |
| Respondent | Search authorized cases, view case details, submit responses, view accepted applicant replies | `/respondent/login` |
| Admin/staff | Review and assign cases, manage hearings, letters, decisions, appeals, inspections, users, and settings | `/login` then `/dashboard` or `/admin/...` |

## 3. Access Map

### Public access

- `/` redirects to `/applicant/login`
- `/home` shows the public landing page with case statistics
- `/terms` shows the currently published terms and conditions
- `/signage` shows the public digital signage dashboard

### Applicant access

- Login: `/applicant/login`
- Register: `/applicant/register`
- Dashboard: `/applicant/dashboard`

### Respondent access

- `/respondent/login` and `/respondent/register` redirect into the applicant login flow in respondent mode
- Dashboard: `/respondent/dashboard`

### Admin access

- Login: `/login`
- Main dashboard: `/dashboard`
- Admin modules: `/admin/...`

## 4. Current Build Notes

These are important because they differ from what users may expect:

- Applicant registration currently marks the email as verified immediately after sign-up.
- Respondents do not have a separate standalone authentication system in practice. The current build uses the applicant login session and switches it into respondent mode.
- A respondent record is auto-created or synchronized from the logged-in applicant account when respondent mode is used.
- Public case list/detail views exist in code (`PublicController` and `resources/views/public/cases/*`), but there are no active routes for them in `routes/web.php` in the current build.
- The existing `README.md` is still the default Laravel README and does not describe this project.

## 5. Core Concepts

### Case number

Each case receives a generated case number when it is created. This number is used throughout the system for:

- applicant receipts
- respondent search
- letters
- decisions
- appeals
- reports

### Case code

The system also generates a case code. In respondent mode, access to a case depends on:

- the case number
- the matching case code

### Status vs review status

Cases use two different progress markers:

- `status`: operational case state such as `pending`, `active`, `adjourned`, `dismissed`, `closed`
- `review_status`: submission review state such as `awaiting_review`, `accepted`, `returned`, `rejected`

The same review pattern is used for:

- applicant case submissions
- respondent responses
- applicant replies to respondent responses

### Notifications

Notifications are role-specific:

- Applicants receive case message, status, hearing, and respondent-view notifications.
- Respondents receive respondent-view, hearing, status, and message notifications for authorized cases.
- Admin users receive applicant message, new unassigned case, hearing, and respondent-view notifications.

### Documents

Private documents are stored on protected disks and downloaded through secure routes. Public preview is only used for approved letters and only when access rules allow it.

## 6. Public Portal

### 6.1 Landing page

The public landing page at `/home` shows:

- total cases
- open cases
- pending cases
- upcoming hearings
- recently registered cases
- quick links to applicant login, respondent access, terms, and signage

### 6.2 Terms and conditions

`/terms` displays the currently published terms record. Applicants may be required to accept the active terms during case submission when a published terms record exists.

### 6.3 Signage

`/signage` is a public dashboard intended for kiosk or screen display. It is rate-limited and shows:

- active case counts
- case distribution by status and category
- today's new submissions
- today's hearings
- active announcements
- active staff list

## 7. Applicant Manual

### 7.1 Account registration

Applicants register at `/applicant/register`.

Required registration data:

- first name
- middle name
- last name
- gender
- position
- organization name
- phone
- email
- address
- national ID number (16 digits)
- lawyer flag
- password and password confirmation

Current behavior:

- the account is created as active
- the applicant is logged in immediately
- `email_verified_at` is set during registration in the current build

### 7.2 Sign in, password reset, and verification

Applicant authentication routes:

- Login: `/applicant/login`
- Forgot password: `/applicant/forgot-password`
- Reset password: `/applicant/reset-password/{token}`
- Verification notice: `/applicant/email/verify`

Although new applicants are auto-verified now, the verification flow still exists.

### 7.3 Dashboard

The applicant dashboard at `/applicant/dashboard` shows:

- total, pending, active, and closed case counts
- recent cases
- approved case letters
- accepted respondent responses
- applicant response replies
- decisions linked to the applicant's cases

### 7.4 Profile management

Applicants manage their profile at `/applicant/profile`.

They can update:

- name fields
- gender
- position
- organization name
- phone
- email
- address
- national ID number
- password

Password changes require the current password.

### 7.5 Filing a new case

Applicants create a case at `/applicant/cases/create`.

Required data in the current build:

- title
- description
- relief requested
- respondent name
- respondent address
- case type
- filing date
- at least one evidence title
- at least one evidence PDF
- at least one witness
- certification of appeal validity
- certification of evidence truthfulness
- acceptance of terms if a published terms record exists

Important case filing rules:

- evidence files must be PDF
- evidence files are required on first submission
- witness phone numbers must be unique within the submission
- the system generates the case number and case code automatically
- the case is created with `status=pending` and `review_status=awaiting_review`

### 7.6 Viewing a case

The case detail page at `/applicant/cases/{id}` shows:

- case details and status timeline
- approved letters for the case
- accepted respondent responses
- uploaded files
- messages with court staff
- hearings
- evidence documents
- witnesses
- case audit history

Reading the case page also marks relevant applicant notifications as seen for recent:

- staff messages
- status changes
- hearings

### 7.7 Editing and deleting a case

Applicants can edit or delete a case only while it remains pending and not already accepted by review.

When a case is edited:

- the review status resets to `awaiting_review`
- reviewer identity and review timestamp are cleared
- additional evidence PDFs can be added
- additional witnesses can be added
- if the case type changes, the system can generate a new case number

Delete route:

- `/applicant/cases/{id}` with `DELETE`

### 7.8 Files, evidence, and witnesses

Applicants can:

- upload additional case files
- delete files they uploaded
- download their files
- delete evidence records
- download evidence files
- delete witnesses

Case file uploads support:

- PDF
- DOC
- DOCX
- JPG
- JPEG
- PNG
- WEBP

### 7.9 Messages with court staff

Applicants can send case-related messages to staff from the case page.

Behavior:

- the message is stored in the case thread
- the assigned staff member is notified by email when possible
- the action is logged in the case audit trail

### 7.10 Receipts and hearing calendar files

Applicants can access:

- receipt page: `/applicant/cases/{id}/receipt`
- receipt PDF: `/applicant/cases/{id}/receipt/pdf`
- email receipt action: `/applicant/cases/{id}/receipt/email`
- hearing calendar file: `/applicant/cases/{id}/hearings/{hearingId}/ics`

The ICS download also marks that hearing notification as seen.

### 7.11 Notifications

Applicant notification routes:

- list: `/applicant/notifications`
- mark one: `/applicant/notifications/mark-one`
- mark all: `/applicant/notifications/mark-all`
- settings: `/applicant/notifications/settings`

Email preference settings currently cover:

- hearing emails
- message emails
- status emails

### 7.12 Respondent responses and applicant replies

Applicants only see respondent responses after staff review has accepted them.

Routes:

- response detail: `/applicant/cases/{case}/respondent-responses/{response}`
- create reply: `/applicant/cases/{case}/respondent-responses/{response}/replies/create`

Applicant reply rules:

- replies require a description and a PDF attachment
- replies are created with `review_status=awaiting_review`
- a reply can be edited or deleted until it becomes accepted
- updating a reply resets it back to `awaiting_review`

### 7.13 Switching to respondent mode

Applicants can switch into respondent mode using:

- `/applicant/switch-to-respondent`

This reuses the same account session and sets a session flag indicating the user is acting as a respondent.

## 8. Respondent Manual

### 8.1 Authentication model

The current implementation does not use a separate respondent login backend. Instead:

- `/respondent/login` redirects to the applicant login page in respondent mode
- a respondent record is created or updated from the same email-based account
- the same browser session is then used for respondent routes

### 8.2 Respondent dashboard

The dashboard at `/respondent/dashboard` shows:

- viewed case count
- submitted response count
- accepted applicant reply count
- notification count
- recently viewed cases
- approved letters tied to viewed cases
- accepted applicant replies to the respondent's responses

### 8.3 Respondent profile

Profile route:

- `/respondent/profile`

Respondents can update:

- name fields
- gender
- position
- organization
- address
- national ID
- phone
- email

Password route:

- `/respondent/profile/password`

### 8.4 Searching for a case

Search route:

- `/respondent/case-search`

Search requires:

- case number
- matching case code

Important rules:

- a respondent cannot search or open their own applicant-submitted case while in respondent mode
- successful search grants access to the case in the session
- successful search records the case in respondent history
- if the respondent is signed in, the view is written to `respondent_case_views`

### 8.5 My Cases

Route:

- `/respondent/case-search/results`

This page lists cases the respondent has already viewed and been associated with through `respondent_case_views`.

### 8.6 Case detail access

Route:

- `/respondent/cases/{caseNumber}`

If the case has not yet been granted into the current session, the user must still supply the correct case code. Once access is granted, the respondent can view the case details without repeating the code during the same session.

### 8.7 Submitting a respondent response

Routes:

- list: `/respondent/responses`
- create: `/respondent/responses/create`
- show: `/respondent/responses/{response}`
- edit: `/respondent/responses/{response}/edit`

Response rules:

- title is required
- PDF attachment is required on create
- case number may be left empty, but if used it must refer to an authorized case
- the system blocks responses to closed cases
- the system blocks users from responding to their own cases
- response numbers are auto-generated per case

### 8.8 Editing and deleting a respondent response

Respondents may edit or delete only their own responses.

When the case number is changed during update:

- authorization is rechecked
- a new response number may be generated for the new case

### 8.9 Applicant reply visibility

Respondents can view applicant replies to their responses through:

- `/respondent/response-replies`
- `/respondent/response-replies/{reply}`

Only accepted applicant replies are visible to respondents in the current build.

### 8.10 Respondent notifications

Routes:

- mark one: `/respondent/notifications/mark-one`
- mark all: `/respondent/notifications/mark-all`

### 8.11 Returning to applicant mode

Route:

- `/respondent/switch-to-applicant`

## 9. Admin and Staff Manual

### 9.1 Admin authentication

Admin users use the default web guard routes:

- `/login`
- `/forgot-password`
- `/reset-password/{token}`
- `/verify-email`

The admin area is protected by:

- authentication
- verified email
- forced password change middleware
- system audit middleware

If a user is marked `must_change_password`, they are redirected until the password is updated.

### 9.2 Dashboard

Routes:

- `/dashboard`
- `/admin/dashboard`
- `/admin/dashboard/stats`

The dashboard shows:

- total, pending, active, and resolved cases
- recent cases
- recent users
- case volume charts
- applicant gender counts
- case type distribution
- quick actions

### 9.3 Reports

Route:

- `/admin/reports`

The report module provides:

- summary cards for major entities
- case status and case type breakdowns
- appeal, decision, letter, applicant, and user status breakdowns
- recent cases and recent appeals
- filterable case counts by date range, status, and judge

### 9.4 Case management

Primary routes:

- `/admin/cases`
- `/admin/cases/{id}`

Admin case operations include:

- browse and filter cases
- export case data
- review applicant case submissions
- assign or unassign cases to staff or teams
- update case status
- post messages to applicants
- manage hearings
- manage case files
- manage witnesses
- review respondent responses
- review applicant response replies

#### Applicant case review

Review decisions:

- `accept`
- `return`
- `reject`

Effects:

- accepting sets review status to `accepted` and activates the case
- returning or rejecting requires a review note
- review actions are logged and the applicant is notified

#### Case assignment

Admins with assignment permission can:

- assign to an active user
- unassign a case
- assign through team-based scope rules

#### Case status updates

Allowed case statuses:

- `pending`
- `active`
- `adjourned`
- `dismissed`
- `closed`

Status changes:

- create a status log entry
- can send an email to the applicant
- can add a visible note into the case message thread

#### Hearings

Hearing operations include create, update, and delete.

Current rules:

- duplicate hearing dates for the same case are blocked
- hearings scheduled for today or the past cannot be edited
- hearings scheduled for today or the past cannot be deleted
- hearing creation can send applicant email notification

#### Case files and witnesses

Admin file uploads support:

- PDF
- DOC
- DOCX
- JPG
- JPEG
- PNG
- WEBP

Witnesses can be created, updated, and deleted from the admin case view.

### 9.5 Hearings index

Route:

- `/admin/hearings`

This provides a dedicated hearing listing in addition to hearing management within each case.

### 9.6 Bench notes

Routes under:

- `/admin/bench-notes`

Bench notes are permission-gated and support full CRUD.

### 9.7 Applicants

Routes:

- `/admin/applicants`
- `/admin/applicants/{applicant}/status`

Admin staff can:

- search applicants
- filter by status
- activate or deactivate applicant accounts

Deactivated applicants cannot log in.

### 9.8 Appeals

Routes under:

- `/admin/appeals`

Appeal workflow:

1. Create a draft appeal linked to a case.
2. The system generates an appeal number in the format `APL-YYYY-####`.
3. Edit while in `draft` or `submitted`.
4. Submit the appeal from draft state.
5. Decide the appeal when it is in `submitted` or `under_review`.

Decision outcomes:

- `approved`
- `rejected`
- `closed`

Appeal documents can be uploaded, downloaded, and deleted.

### 9.9 Decisions

Routes under:

- `/admin/decisions`

Decision records include:

- linked case
- file/case number information
- decision content
- decision date
- status
- reviewing admin names
- judge panel
- judges' comments

Decision reviews support:

- `approve`
- `reject`
- `improve`

Each review is tied to the logged-in reviewer, and users can edit or delete only their own reviews.

### 9.10 Record compilation

Routes:

- `/admin/recordes`
- `/admin/recordes/{case}`
- `/admin/recordes/{case}/pdf`

This module compiles a case record view and PDF containing the major case materials.

### 9.11 Letters

#### Letter templates

Routes:

- `/admin/letter-templates`
- `/admin/letter-categories`

Template features:

- title
- category
- placeholder list
- rich text body
- optional header image
- optional footer image

#### Letter composition

Routes:

- `/admin/letters/compose`
- `/admin/letters`

Letter composition supports:

- template selection
- recipient details
- subject
- case number linkage
- body editing
- CC
- delivery flags for applicant and respondent
- approver display name and title

Important rules:

- at least one delivery target must be selected
- letters cannot be created for closed cases
- if a case number is supplied, the system generates a case-based reference number
- recipient name can be auto-built from applicant and respondent parties when left blank

#### Letter approval

Approval outcomes:

- `approved`
- `returned`
- `rejected`

If approved:

- the system records approver identity
- the system notifies relevant parties
- the system posts a system case message

#### Public letter preview

Route:

- `/case-letters/{letter}`

A letter preview is available to:

- admin users
- the case applicant when the letter is marked for applicant delivery
- respondents who have viewed the case when the letter is marked for respondent delivery
- anyone holding a valid signed preview URL

### 9.12 Notifications

Route:

- `/admin/notifications`

Admin notifications currently track:

- applicant messages from the last 14 days
- new pending and unassigned cases from the last 14 days
- upcoming hearings on the user's assigned cases
- recent respondent views on cases assigned to the user or unassigned cases

### 9.13 Users

Routes:

- `/users`
- `/users/create`
- `/users/{user}`

Admin users can manage staff accounts with:

- name fields
- email
- active or inactive status
- gender
- date of birth
- national ID
- position
- phone
- address
- roles
- avatar
- signature
- stamp

Current creation behavior:

- a random password is generated
- `must_change_password` is set
- the system attempts to send a password reset link automatically

### 9.14 Roles and permissions

Routes:

- `/roles`
- `/admin/permissions`

The system uses permission-gated admin modules. Access to most admin routes depends on named permissions such as:

- `cases.view`
- `cases.edit`
- `cases.review`
- `cases.assign`
- `appeals.view`
- `decision.view`
- `letters.create`
- `letters.approve`
- `reports.view`

### 9.15 Teams

Routes:

- `/admin/teams`

Teams support:

- team name
- optional parent team
- description
- team leader
- team membership

Important rules:

- a leader is kept on the team roster
- users are moved out of other teams when assigned to a new one
- teams with members cannot be deleted

### 9.16 Terms, About pages, Announcements, and System settings

#### Terms

- CRUD under `/admin/terms`
- one or more terms records can exist
- records can be published or unpublished

#### About pages

- CRUD under `/admin/about`
- title, slug, body, publication flag

#### Announcements

- CRUD under `/admin/announcements`
- title, rich content, active or inactive status
- active announcements are used by the public signage dashboard

#### System settings

- `/admin/settings/system`

Settings include:

- app name
- short name
- about text
- contact email
- contact phone
- maintenance mode
- logo
- favicon
- seal
- banner

### 9.17 Case inspections

Routes under:

- `/admin/case-inspections/requests`
- `/admin/case-inspections/findings`

#### Inspection requests

Request fields:

- linked case
- request date
- subject
- request note
- status
- assigned inspector when the user has assignment permission

Rules:

- completed requests are locked against edit and delete

#### Inspection findings

Finding fields:

- inspection request
- finding date
- title
- details
- severity
- optional PDF attachment

Rules:

- findings can be restricted to the assigned inspector
- creating a finding automatically marks the related request as completed
- accepted findings are locked against further edit or delete

## 10. API Manual

The repository includes an OpenAPI file at `docs/api/openapi.yaml`.

### Base endpoints

- Health: `/api/health`
- Versioned API: `/api/v1/...`

### Authentication

- Login: `POST /api/v1/auth/login`
- Logout: `POST /api/v1/auth/logout`
- Current user: `GET /api/v1/me`

Authentication uses Bearer tokens via Laravel Sanctum.

### API resources currently exposed

- case types
- case list and case detail
- case messages list and create
- respondent responses list, detail, and create

### API user types

The OpenAPI file indicates login is intended for:

- admin
- applicant
- respondent

## 11. File Upload Rules

| Area | Allowed Types | Max Size |
| --- | --- | --- |
| Applicant initial evidence | PDF | 5 MB each |
| Applicant extra case file | PDF, DOC, DOCX, JPG, JPEG, PNG, WEBP | 4 MB |
| Respondent response | PDF | 5 MB |
| Applicant response reply | PDF | 2 MB |
| Admin case file | PDF, DOC, DOCX, JPG, JPEG, PNG, WEBP | 10 MB |
| Appeal document | PDF, DOC, DOCX, JPG, JPEG, PNG, WEBP | 5 MB |
| Inspection finding attachment | PDF | 2 MB |
| System logo | PNG, JPG, JPEG, SVG, WEBP | 2 MB |
| System favicon | PNG, ICO | 512 KB |
| System seal | PNG | 1 MB |
| System banner | PNG, JPG, JPEG, WEBP | 3 MB |
| User avatar, signature, stamp | image files as validated by controller | 2 MB |

## 12. Audit Notes and Gaps

The following observations came from the repository audit and should be considered when maintaining or extending the project:

- `docs/public-user-manual.md` covers only part of the system and does not document the admin area.
- `README.md` is still the default Laravel README and should be replaced or updated.
- `PublicController` and public case list/detail views exist, but they are not currently reachable through active routes.
- Applicant registration auto-verifies email in code, so the email verification notice is not normally part of first-time sign-up.
- Respondent mode depends on the applicant session and email-based respondent synchronization rather than a fully separate guard-driven login journey.
- Respondent profile update and password update routes should be rechecked before production use because the route group authenticates with the applicant guard while `Respondent\\ProfileController` reads the respondent guard for update actions.

## 13. Recommended Documentation Next Steps

To improve maintainability, the next documentation updates should be:

1. Replace the default `README.md` with project-specific setup and module documentation.
2. Keep this manual as the master operational manual and retire or clearly scope `docs/public-user-manual.md`.
3. Add role-based screenshots for applicant, respondent, and admin workflows.
4. Add a short admin quick-start for first login, password reset, and permission assignment.
5. Keep `docs/api/openapi.yaml` aligned with any future route changes.
