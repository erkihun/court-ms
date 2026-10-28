# court-ms on the EUISIS stack

court-ms is moving, screen by screen, from Blade + Alpine + Tailwind 3 to the
stack and user interface of EUISIS: Inertia, React 19, TypeScript, Tailwind 4
and the `@euisis/ui` kit, inside the EUISIS application shell (sidebar, header,
command palette, language and theme switches). It stays its own application
with its own database; what it shares with EUISIS is the frontend foundation,
copied verbatim the way the EUISIS Core platform does it.

## The build

One Vite build (`vite.config.js`) into `public/build`: Tailwind 4
(`resources/css/app.css`, from EUISIS), loaded by
`resources/views/inertia.blade.php`. `npm run build` type-checks and builds;
`npm run dev` serves it with hot reload. The Blade/Alpine/Tailwind 3 build
that served the screens during the move (`vite.legacy.config.js`,
`resources/legacy`, `public/build-legacy`) is gone, with its layouts,
components and npm packages.

## What comes from EUISIS, and how

`node scripts/sync-from-euisis.mjs [path to EUISIS]` copies the EUISIS entry
(`resources/js/app.tsx`), every file it imports, the SEEDS listed in the script
(layouts, shared components, hooks, the sign-in and profile pages), the whole
`i18n` and `types` folders, `resources/css` and `packages/euisis-ui`. The copied
files are listed in `resources/js/.euisis-sync.json`; a sync removes and
replaces only those, never court-ms's own files. **Do not edit a copied file**:
change it in EUISIS and re-sync, or add a PATCH to the script. The PATCHES are
the complete list of differences from EUISIS (a dozen one-line changes: route
names, the workspace name, the merged dictionary).

Run the sync whenever EUISIS changes its foundation, then `npm run typecheck`
and `npm run build`.

## court-ms's own frontend code

Everything under `resources/js/court/` and `resources/js/Pages/Court/`:

- `court/i18n/{en,am}.ts` — the dictionary merged over the EUISIS one.
  `cm.*` is court-ms's `lang/{en,am}/*.php`, exported by
  `php artisan court:client-lang` into `court/i18n/lang/` (committed; the
  test suite fails when it is out of date), so a React screen says exactly what
  the Blade screen it replaces said: `__('cases.status.pending')` is
  `t('cm.cases.status.pending')`. `court.*` is text only the React screens need.
- `court/components/` — court-ms-only components the shell shows
  (the active-role switcher in the account menu).
- `court/types/` — the TypeScript shape of each page's props.
- `Pages/Court/*.tsx` — the screens.

## The server side

- `App\Http\Middleware\HandleInertiaRequests` shares the props every EUISIS
  component expects, in the EUISIS shape (`auth`, `settings`, `branding`,
  `workspace`, `flash`, …). court-ms is always in its own workspace: the
  sidebar is `App\Support\Ui\CourtNavigation` (the Blade sidebar's items,
  labels and permissions), filtered per person on the server.
- `App\Support\Ui\SharedSettings` maps court-ms System Settings onto the
  EUISIS settings keys. System Settings > Appearance is the EUISIS section
  (brand, secondary, accent and sidebar colours, theme, density, button style,
  card radius, logo position, shell switches), stored in `system_settings`
  columns; an empty value means the EUISIS default
  (`CourtSettingsSchema::APPEARANCE_DEFAULTS`). EUISIS's page size and
  dashboard settings are left out: no court-ms screen reads them.
- The shared `flash` prop is lazy like the others: `SetLocale` is route
  middleware and runs after `HandleInertiaRequests`.
- `SetLocale` reads the React client's `euisis_locale` cookie (unencrypted,
  as EUISIS does) ahead of court-ms's `app_locale`; the Blade language switch
  writes both, so the language follows the person across both kinds of screen.
- The route list the client uses is `resources/js/ziggy.js`
  (`php artisan ziggy:generate resources/js/ziggy.js`; `config/ziggy.php`
  keeps development routes out). A route EUISIS has and court-ms does not
  resolves to `#`.
- `notifications.feed`, `notifications.read`, `notifications.read-all`
  (`Admin\NotificationFeedController`) feed the staff header bell with the
  same items as the notifications page. In the portals the bell reads
  `applicant.notifications.*` or `respondent.notifications.*`
  (`PortalNotificationFeedController`,
  `App\Services\Notifications\PortalNotificationFeed`), over the same seen
  rows as the portal pages; `PortalNavigation::routes()` hands the bell those
  route names through the shared `portal` prop.

## Where the move stands

Every screen is on the EUISIS stack (React):

- the staff application: sign-in and the account pages (forgot password,
  reset code, reset, registration), the dashboard, notifications, every
  register and its forms and pages (cases and the case page, hearings,
  decisions and decision templates, letters, letter templates and categories,
  appeals, inspections, bench notes, records, applicants, users, teams,
  roles, permissions, case types, announcements, terms, About pages),
  reports, the audit trail, the system settings and the landing-page manager;
- the applicant and respondent portals, in the same shell:
  `App\Support\Ui\PortalNavigation` tells `HandleInertiaRequests` which
  portal a request belongs to, the signed-in person comes from the
  `applicant` guard, and the sidebar and the header's home, profile,
  sign-out and notification bell are the portal's own (shared prop
  `portal`). The applicant's printable filing receipt is a React page too;
- the public site: home, resources, terms, the user manual and the signage
  board, in `court/public/PublicLayout` (header, footer and manual link from
  the landing manager's settings, `App\Support\Ui\PublicSiteChrome`).

Kept as Blade on purpose: documents, not screens. These are the mail
templates (`resources/views/mail`), the PDFs (`resources/views/pdf`,
`admin/recordes/record-pdf`, `applicant/cases/receipt-pdf`), the printable
letter (`admin/letters/preview` and its inline partial), the Inertia root
view and the error pages.

## Adding a screen

1. Make the controller `return Inertia::render('Court/<Name>', [...])` with
   plain arrays (dates as ISO 8601 strings, rich text cleaned on the server).
2. Write `resources/js/Pages/Court/<Name>.tsx` on `AuthenticatedLayout`
   (or `PublicLayout` for a public page), `PageHeader` and the
   `@/Components/ui` kit; text through `t('cm.…')`.
3. Add the props type under `court/types/`, run `npm run typecheck`,
   `php artisan ziggy:generate resources/js/ziggy.js` for new routes,
   `npm run build`, and the feature tests (`DB_DATABASE=court_ms_test`),
   asserting with `assertInertia`.

Laravel is upgraded to 13 last, once the production host runs PHP ≥ 8.3.
