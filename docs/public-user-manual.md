# Public Side User Manual

This manual covers the public-facing portal for applicants and respondents. It does not cover the admin system.

## Access and language
- Main entry: `/applicant/login` (the root path `/` redirects here).
- Language switcher: use the language toggle in the top navigation (English/Amharic).
- Terms and conditions: `/terms`.

## Applicant portal

### Create an account
1. Open `/applicant/register`.
2. Fill in the registration form and submit.
3. Check your email for a verification link if prompted.

### Sign in and password reset
- Sign in at `/applicant/login`.
- Forgot password: `/applicant/forgot-password`.
- Reset password: use the emailed reset link to reach `/applicant/reset-password/{token}`.

### Dashboard
After login, the dashboard at `/applicant/dashboard` shows your case activity, notifications, and quick links.

### Profile
- View or update profile at `/applicant/profile`.
- You can switch to the respondent view from your profile or top navigation.

### Create a case
1. Go to `/applicant/cases/create`.
2. Fill in the case form and submit.
3. You are redirected to the case detail page after creation.

### View and manage your cases
- List your cases: `/applicant/cases`.
- View a case: `/applicant/cases/{id}`.
- Edit a case: `/applicant/cases/{id}/edit`.
- Delete a case: use the delete action on the case detail page.

### Files, evidence, and witnesses
- Upload case files: POST to `/applicant/cases/{id}/files`.
- Delete a file: `/applicant/cases/{id}/files/{fileId}`.
- Download a file: `/applicant/cases/{id}/files/{fileId}/download`.
- Delete evidence: `/applicant/cases/{id}/evidences/{evidenceId}`.
- Download evidence: `/applicant/cases/{id}/evidences/{evidenceId}/download`.
- Delete a witness: `/applicant/cases/{id}/witnesses/{witnessId}`.

### Messages
- Post a message to court staff from a case: `/applicant/cases/{id}/messages`.
- Messages appear in your case timeline and notifications.

### Receipts and hearing calendar
- Receipt page: `/applicant/cases/{id}/receipt`.
- Receipt PDF: `/applicant/cases/{id}/receipt/pdf`.
- Email receipt: `/applicant/cases/{id}/receipt/email`.
- Hearing calendar file (ICS): `/applicant/cases/{id}/hearings/{hearingId}/ics`.

### Notifications
- Notification list: `/applicant/notifications`.
- Mark one read: `/applicant/notifications/mark-one`.
- Mark all read: `/applicant/notifications/mark-all`.
- Notification settings: `/applicant/notifications/settings`.

### Respondent mode (switch)
If you need to act as a respondent, use the switch option in the navigation:
- Switch to respondent: `/applicant/switch-to-respondent`.
- Switch back to applicant: `/respondent/switch-to-applicant`.

## Respondent portal

### Register and login
Respondent registration and login routes redirect to the applicant login form with a respondent toggle:
- Register: `/respondent/register` (redirects).
- Login: `/respondent/login` (redirects).

### Dashboard and profile
- Dashboard: `/respondent/dashboard`.
- Profile edit: `/respondent/profile`.
- Change password: `/respondent/profile/password`.

### Find and manage cases
- Case search: `/respondent/case-search`.
- My cases: `/respondent/case-search/results`.
- Case detail view: `/respondent/cases/{caseNumber}`.

### Responses
- List responses: `/respondent/responses`.
- Create response: `/respondent/responses/create`.
- View response: `/respondent/responses/{response}`.
- Edit response: `/respondent/responses/{response}/edit`.
- Download response: `/applicant/respondent/responses/{response}/download` (available when acting as respondent).

### Respondent notifications
- Mark one read: `/respondent/notifications/mark-one`.
- Mark all read: `/respondent/notifications/mark-all`.

## Other public pages
- Public signage display (for kiosk/TV): `/signage`.
- Public letter preview: `/case-letters/{letter}` (access-controlled).
- Terms and conditions: `/terms`.

## Tips and troubleshooting
- If you cannot log in, use password reset and confirm your email verification.
- Case search is rate-limited; wait a minute if you see throttling errors.
- If a case number returns "not found," verify the exact case number and try again.
- Public letter previews require an authorized link from court staff.
