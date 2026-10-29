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
- Public display for the screen at the court's door: `/signage`. It is in Amharic throughout, with every hour on the Ethiopian clock (a hearing set for 14:00 reads ከቀኑ 8:00) and dates in the Ethiopian calendar. From top to bottom: the court and the current Ethiopian hour and date; the next sitting (time, case number, bench and room, how long until it); the day's cause list (time, case number, matter, the bench the case is assigned to, in its own colour, and the room); the case numbers whose decision was released to the parties in the last 14 days (published and approved); a QR code that opens My Portal for filing and following a case, with the registry's office hours, phone and address when the landing manager's contact settings have them; and the active notices. Once today's sittings are past (or on a day without any), it shows the next sitting day within two weeks. No party or staff is named. It refreshes every minute. It is laid out for the court's portrait floor screen (1080x1920); on a screen of another shape the portrait board shows framed in the middle. For a panel stood on its side while its computer still drives it as landscape, add `?rotate=90` (or `?rotate=270` if it reads upside down); for a landscape wall screen, add `?orientation=landscape`. Open it full screen on the display; the cursor hides when the mouse is still.
- Public letter preview: `/case-letters/{letter}` (access-controlled).
- Terms and conditions: `/terms`.

## Tips and troubleshooting
- If you cannot log in, use password reset and confirm your email verification.
- Case search is rate-limited; wait a minute if you see throttling errors.
- If a case number returns "not found," verify the exact case number and try again.
- Public letter previews require an authorized link from court staff.
