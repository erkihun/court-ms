// Copies the EUISIS frontend foundation court-ms is built on, unchanged, into
// this repository: the entry, layouts, shared components, hooks, the @euisis/ui
// kit, styles, translations and type declarations.
//
//   node scripts/sync-from-euisis.mjs [path to the EUISIS checkout, default ../EUISIS]
//
// Same approach as the EUISIS Core platform (core-platform/frontend/scripts/
// sync-from-euisis.mjs): the SEEDS below and the EUISIS entry are followed
// through every import; each file they reach is copied as it is. The only
// differences from EUISIS are the PATCHES, applied by this script.
//
// court-ms's own code (its pages under resources/js/Pages/Court and the rest
// of resources/js/court) is never touched: the files this script copied are
// listed in resources/js/.euisis-sync.json and only those are replaced.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const euisis = path.resolve(process.argv[2] ?? path.join(here, '..', 'EUISIS'));
const sourceJs = path.join(euisis, 'resources', 'js');
const targetJs = path.join(here, 'resources', 'js');
const manifestFile = path.join(targetJs, '.euisis-sync.json');

/** EUISIS files court-ms renders or builds on, beyond what the entry imports. */
const SEEDS = [
    'Layouts/AuthenticatedLayout.tsx',
    'Layouts/GuestLayout.tsx',
    'Components/PageHeader.tsx',
    'Components/StatusBadge.tsx',
    'Components/StatCard.tsx',
    'Components/EmptyState.tsx',
    'Components/FormSection.tsx',
    'Components/Breadcrumbs.tsx',
    'Components/ConfirmationDialog.tsx',
    'Components/ui/index.ts',
    'Components/forms/RichTextEditor.tsx',
    'Components/Calendar/LocalizedDatePicker.tsx',
    'Components/Calendar/LocalizedTimePicker.tsx',
    'Components/Calendar/LocalizedDateDisplay.tsx',
    'Components/forms/RichTextViewer.tsx',
    'Components/dashboard/DashboardSection.tsx',
    'Components/dashboard/ChartCard.tsx',
    'Components/dashboard/KpiCard.tsx',
    'Components/dashboard/StatusDistribution.tsx',
    'Components/dashboard/charts/SimpleCharts.tsx',
    'hooks/useCan.ts',
    'hooks/useLocale.ts',
    'hooks/useSystemSettings.ts',
    'hooks/useBranding.ts',
    'hooks/useDisplayFormat.ts',
    'hooks/useConfirm.ts',
    'Pages/Auth/Login.tsx',
    'Pages/Auth/ForcedPasswordChange.tsx',
    'Pages/Auth/MfaChallenge.tsx',
    'Pages/Auth/MfaSetup.tsx',
    'Pages/Auth/VerifyEmail.tsx',
    'Pages/Auth/ConfirmPassword.tsx',
    'Pages/Profile/Edit.tsx',
];

/** Copied whole: every language file and type declaration. */
const WHOLE = ['i18n', 'types'];

/**
 * court-ms's only changes to the copied files. A route EUISIS has and court-ms
 * does not resolves to "#" instead of throwing (as in the Core); the sidebar's
 * server-sent workspace opens on the court-ms dashboard under the court-ms
 * name; court-ms's own dictionary is merged into each language's bundle.
 */
const PATCHES = [
    {
        file: 'app.tsx',
        from: 'globalThis.route = ziggyRoute;',
        to: [
            '// court-ms: a route that only EUISIS has is not in this route list; link to nothing instead of throwing.',
            "globalThis.route = ((name?: string, ...rest: unknown[]) => (name !== undefined && !(name in (globalThis.Ziggy as ZiggyConfig).routes) ? '#' : (ziggyRoute as (...args: unknown[]) => unknown)(name, ...rest))) as typeof ziggyRoute;",
        ].join('\n'),
    },
    {
        file: 'Components/AppSidebar.tsx',
        from: "const coreHomeNav: NavItem = { routeName: 'core.dashboard', labelKey: 'core.nav.dashboard', icon: LayoutDashboard };",
        to: "const coreHomeNav: NavItem = { routeName: 'admin.dashboard', labelKey: 'nav.dashboard', icon: LayoutDashboard };",
    },
    {
        file: 'Components/AppSidebar.tsx',
        from: "t('core.workspaceName')",
        to: "t('court.workspaceName')",
        all: true,
    },
    {
        file: 'Components/AppSidebar.tsx',
        from: "t(coreSections ? 'core.workspaceName'",
        to: "t(coreSections ? 'court.workspaceName'",
    },
    {
        // The account menu offers court-ms's active-role switch (resources/js/court/components).
        file: 'Components/AppHeader.tsx',
        from: "import NotificationBell from '@/Components/NotificationBell';",
        to: "import NotificationBell from '@/Components/NotificationBell';\nimport ActiveRoleSwitcher from '@/court/components/ActiveRoleSwitcher';",
    },
    {
        file: 'Components/AppHeader.tsx',
        from: "                                    {roles.map((role) => <span key={role} className=\"rounded-md border border-(--app-border) bg-(--app-surface) px-2 py-0.5 text-xs text-(--app-muted-foreground)\">{role}</span>)}\n                                </div>}",
        to: "                                    {roles.map((role) => <span key={role} className=\"rounded-md border border-(--app-border) bg-(--app-surface) px-2 py-0.5 text-xs text-(--app-muted-foreground)\">{role}</span>)}\n                                </div>}\n                                <ActiveRoleSwitcher />",
    },
    {
        // The trail starts at the court-ms dashboard, and that page shows none.
        file: 'Components/Breadcrumbs.tsx',
        from: "{ label: t('core.nav.dashboard'), href: route('core.dashboard') }",
        to: "{ label: t('cm.app.Dashboard'), href: route('admin.dashboard') }",
    },
    {
        file: 'Components/Breadcrumbs.tsx',
        from: "if (current === 'core.dashboard') return null;",
        to: "if (current === 'core.dashboard' || current === 'admin.dashboard' || current === 'dashboard') return null;",
    },
    {
        // The bell's "view all" opens court-ms's notifications page.
        file: 'Components/NotificationBell.tsx',
        from: "route('employee.notifications')",
        to: "route('admin.notifications.index')",
    },
    {
        // court-ms is only its own workspace: there is no "Leave Core" to go back to.
        file: 'Components/AppSidebar.tsx',
        from: "{coreSections && (\n                    <Link href={route('core.exit')}",
        to: "{coreSections && route().has('core.exit') && (\n                    <Link href={route('core.exit')}",
    },
    {
        file: 'i18n/registry.ts',
        from: "    en: () => import('./bundles/en'),\n    am: () => import('./bundles/am'),",
        to: [
            '    // court-ms: its own dictionary (resources/js/court/i18n) over the EUISIS one, namespace by namespace.',
            "    en: () => Promise.all([import('./bundles/en'), import('@/court/i18n/en')]).then(([base, court]) => ({ default: mergeDictionaries(base.default, court.default) })),",
            "    am: () => Promise.all([import('./bundles/am'), import('@/court/i18n/am')]).then(([base, court]) => ({ default: mergeDictionaries(base.default, court.default) })),",
        ].join('\n'),
    },
    {
        file: 'i18n/registry.ts',
        from: 'const loaders: Record<Locale, () => Promise<{ default: TranslationTree }>> = {',
        to: [
            '/** court-ms keys replace EUISIS keys one namespace deep (`auth.stage` as a whole, say), the rest stays. */',
            'function mergeDictionaries(base: TranslationTree, over: TranslationTree): TranslationTree {',
            '    const merged: TranslationTree = { ...base };',
            '    for (const [key, value] of Object.entries(over)) {',
            '        const current = merged[key];',
            '        merged[key] = current && typeof current === \'object\' && value && typeof value === \'object\' && !Array.isArray(value)',
            '            ? { ...(current as TranslationTree), ...(value as TranslationTree) }',
            '            : value;',
            '    }',
            '    return merged;',
            '}',
            '',
            'const loaders: Record<Locale, () => Promise<{ default: TranslationTree }>> = {',
        ].join('\n'),
    },
    {
        // court-ms route names for the second factor and the Devices tab.
        file: 'Pages/Auth/MfaChallenge.tsx',
        from: "route('mfa.challenge.verify')",
        to: "route('mfa.challenge.store')",
    },
    {
        file: 'Pages/Profile/Partials/TwoFactorCard.tsx',
        from: "form.post(route('mfa.disable')",
        to: "form.delete(route('mfa.setup.destroy')",
    },
    {
        file: 'Pages/Profile/Partials/TwoFactorCard.tsx',
        from: "route('mfa.setup')",
        to: "route('mfa.setup.show')",
    },
    {
        file: 'Pages/Profile/Partials/DevicesSection.tsx',
        from: 'route("profile.devices.destroy", session.id)',
        to: 'route("profile.sessions.destroy", session.id)',
    },
    {
        file: 'Pages/Profile/Partials/DevicesSection.tsx',
        from: 'route("profile.devices.destroy-others")',
        to: 'route("profile.sessions.destroyOthers")',
    },
];

function resolveImport(from, spec) {
    let base;
    if (spec.startsWith('@/')) base = path.join(sourceJs, spec.slice(2));
    else if (spec.startsWith('.')) base = path.resolve(path.dirname(from), spec);
    else return null;
    for (const ext of ['', '.tsx', '.ts', '.js', '.jsx', '/index.tsx', '/index.ts', '/index.js']) {
        const file = base + ext;
        if (fs.existsSync(file) && fs.statSync(file).isFile()) return file;
    }
    return null;
}

const reached = new Set();
function follow(file) {
    if (reached.has(file)) return;
    reached.add(file);
    if (!/\.(tsx?|jsx?)$/.test(file)) return;
    const source = fs.readFileSync(file, 'utf8');
    for (const match of source.matchAll(/(?:import|export)\s[^'"]*?from\s*['"]([^'"]+)['"]|import\(\s*['"]([^'"]+)['"]\s*\)|import\s+['"]([^'"]+)['"]/g)) {
        const target = resolveImport(file, match[1] ?? match[2] ?? match[3]);
        if (target) follow(target);
    }
}

function copyFile(from, to, copied) {
    fs.mkdirSync(path.dirname(to), { recursive: true });
    fs.copyFileSync(from, to);
    copied.push(path.relative(here, to).replace(/\\/g, '/'));
}

function copyTree(from, to, copied) {
    for (const entry of fs.readdirSync(from, { withFileTypes: true })) {
        const source = path.join(from, entry.name);
        const target = path.join(to, entry.name);
        if (entry.isDirectory()) copyTree(source, target, copied);
        else copyFile(source, target, copied);
    }
}

if (!fs.existsSync(path.join(sourceJs, 'app.tsx'))) {
    console.error(`No EUISIS checkout at ${euisis}`);
    process.exit(1);
}

follow(path.join(sourceJs, 'app.tsx'));
for (const seed of SEEDS) {
    const file = path.join(sourceJs, seed);
    if (!fs.existsSync(file)) {
        console.error(`EUISIS has no ${seed} any more; update SEEDS.`);
        process.exit(1);
    }
    follow(file);
}

// Remove what the last sync copied (court-ms's own files are not in the list).
if (fs.existsSync(manifestFile)) {
    for (const relative of JSON.parse(fs.readFileSync(manifestFile, 'utf8'))) {
        fs.rmSync(path.join(here, relative), { force: true });
    }
}
fs.rmSync(path.join(here, 'packages', 'euisis-ui'), { recursive: true, force: true });

const copied = [];
for (const file of reached) {
    const relative = path.relative(sourceJs, file);
    if (relative.startsWith('..')) {
        // Outside resources/js (the stylesheet the entry imports).
        copyFile(file, path.join(here, 'resources', path.relative(path.join(euisis, 'resources'), file)), copied);
    } else if (path.basename(file) !== 'ziggy.js') {
        copyFile(file, path.join(targetJs, relative), copied);
    }
}
for (const folder of WHOLE) copyTree(path.join(sourceJs, folder), path.join(targetJs, folder), copied);
copyTree(path.join(euisis, 'resources', 'css'), path.join(here, 'resources', 'css'), copied);
copyTree(path.join(euisis, 'packages', 'euisis-ui', 'src'), path.join(here, 'packages', 'euisis-ui', 'src'), copied);
copyFile(path.join(euisis, 'packages', 'euisis-ui', 'package.json'), path.join(here, 'packages', 'euisis-ui', 'package.json'), copied);

for (const patch of PATCHES) {
    const file = path.join(targetJs, patch.file);
    const source = fs.readFileSync(file, 'utf8');
    if (!source.includes(patch.from)) {
        console.error(`Patch for ${patch.file} no longer applies: EUISIS changed "${patch.from.trim().slice(0, 80)}"`);
        process.exit(1);
    }
    fs.writeFileSync(file, patch.all ? source.split(patch.from).join(patch.to) : source.replace(patch.from, patch.to));
}

const unique = [...new Set(copied)].sort();
fs.writeFileSync(manifestFile, JSON.stringify(unique, null, 1) + '\n');
console.log(`Copied ${unique.length} files from EUISIS (entry, ${SEEDS.length} seeds, ${WHOLE.join(', ')}, styles, @euisis/ui).`);
