import { type KeyboardEvent, type ReactNode, useId, useRef } from 'react';
import { cn } from '@/lib/cn';

export interface TabItem<K extends string> {
    key: K;
    label: ReactNode;
    count?: number;
}

interface TabsProps<K extends string> {
    tabs: Array<TabItem<K>>;
    active: K;
    onChange: (key: K) => void;
    /** Renders the panel for the active tab. */
    children: ReactNode;
    label?: string;
    className?: string;
}

/** WAI-ARIA tabs with arrow-key navigation. Panels are rendered by the caller for the active key. */
export function Tabs<K extends string>({ tabs, active, onChange, children, label, className }: TabsProps<K>) {
    const id = useId();
    const list = useRef<HTMLDivElement>(null);

    const onKeyDown = (event: KeyboardEvent<HTMLButtonElement>, index: number) => {
        const step = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
        if (step === 0) return;
        event.preventDefault();
        const next = tabs[(index + step + tabs.length) % tabs.length];
        if (!next) return;
        onChange(next.key);
        list.current?.querySelector<HTMLButtonElement>(`[data-tab="${next.key}"]`)?.focus();
    };

    return (
        <div className={className}>
            <div ref={list} role="tablist" aria-label={label} className="flex gap-1 overflow-x-auto border-b border-(--border)">
                {tabs.map((tab, index) => {
                    const selected = tab.key === active;

                    return (
                        <button
                            key={tab.key}
                            type="button"
                            role="tab"
                            data-tab={tab.key}
                            id={`${id}-tab-${tab.key}`}
                            aria-selected={selected}
                            aria-controls={`${id}-panel`}
                            tabIndex={selected ? 0 : -1}
                            onClick={() => onChange(tab.key)}
                            onKeyDown={(event) => onKeyDown(event, index)}
                            className={cn(
                                '-mb-px inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                                selected ? 'border-blue-600 text-blue-700 dark:text-blue-300' : 'border-transparent text-(--text-muted) hover:text-(--text)',
                            )}
                        >
                            {tab.label}
                            {tab.count !== undefined && (
                                <span className="rounded-full bg-(--surface-soft) px-1.5 text-[11px] text-(--text-subtle)">{tab.count}</span>
                            )}
                        </button>
                    );
                })}
            </div>
            <div role="tabpanel" id={`${id}-panel`} aria-labelledby={`${id}-tab-${active}`} className="pt-4">
                {children}
            </div>
        </div>
    );
}
