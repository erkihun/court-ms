import { type ReactNode, useCallback, useState } from 'react';
import { Sidebar } from '@/components/layout/admin/Sidebar';
import { Topbar } from '@/components/layout/admin/Topbar';
import { SessionTimeoutWarning } from '@/components/layout/SessionTimeoutWarning';
import { ToastProvider } from '@/components/ui/Toaster';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';

const COMPACT_KEY = 'admin_sidebar_compact';

function readCompact(): boolean {
    try {
        return localStorage.getItem(COMPACT_KEY) === '1';
    } catch {
        return false;
    }
}

/**
 * Staff portal shell. Used as a persistent layout, so the sidebar, open
 * menus and notification polling survive page visits.
 */
export default function AdminLayout({ children }: { children: ReactNode }) {
    const { app } = useShared();
    const [compact, setCompact] = useState(readCompact);
    const [mobileOpen, setMobileOpen] = useState(false);

    const toggleCompact = useCallback(() => {
        setCompact((value) => {
            try {
                localStorage.setItem(COMPACT_KEY, value ? '0' : '1');
            } catch {
                // Not remembered when storage is unavailable.
            }
            return !value;
        });
    }, []);

    return (
        <ToastProvider>
            <div className="ui-shell min-h-screen">
                <Sidebar compact={compact} mobileOpen={mobileOpen} onCloseMobile={() => setMobileOpen(false)} />
                {mobileOpen && (
                    <div className="fixed inset-0 z-30 bg-black/40 md:hidden" onClick={() => setMobileOpen(false)} aria-hidden="true" />
                )}
                <div className={cn('flex min-h-screen flex-1 flex-col transition-[padding] duration-300', compact ? 'md:ml-18' : 'md:ml-64')}>
                    <Topbar compact={compact} onOpenMobile={() => setMobileOpen(true)} onToggleCompact={toggleCompact} />
                    <main className="page-enter flex-1 p-4 md:p-5 xl:p-7">
                        <div className="ui-page-admin">{children}</div>
                    </main>
                    <footer className="border-t border-slate-200/60 bg-white/80 backdrop-blur-xs dark:border-slate-800/60 dark:bg-slate-950/80">
                        <div className="flex flex-wrap items-center justify-between gap-3 px-4 py-3 md:px-6 xl:px-8">
                            <div className="flex items-center gap-2.5">
                                <span className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-600/90 text-[11px] font-bold text-white">
                                    {Array.from(app.shortName).slice(0, 2).join('')}
                                </span>
                                <span className="text-[12px] text-slate-500">
                                    <span className="font-semibold text-slate-700 dark:text-slate-400">{app.name}</span>
                                    <span className="mx-1.5 opacity-30">·</span>
                                    {app.footerText}
                                </span>
                            </div>
                            <div className="flex items-center gap-1.5 text-[11px]">
                                <span className="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
                                    <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500" />
                                    {t('app.court_portal')}
                                </span>
                                <span className="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 font-medium text-slate-400 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-600">
                                    {app.today.short}
                                </span>
                            </div>
                        </div>
                    </footer>
                </div>
                <SessionTimeoutWarning />
            </div>
        </ToastProvider>
    );
}
