import { type ReactNode, useCallback, useRef, useState } from 'react';
import { useDismiss } from '@/hooks/useDismiss';
import { cn } from '@/lib/cn';

interface DropdownProps {
    /** Render prop so the trigger can reflect the open state (aria-expanded, chevron). */
    trigger: (props: { open: boolean; toggle: () => void }) => ReactNode;
    children: ReactNode | ((close: () => void) => ReactNode);
    align?: 'start' | 'end';
    panelClassName?: string;
    className?: string;
}

/** Anchored popover closed by outside click or Escape. */
export function Dropdown({ trigger, children, align = 'end', panelClassName, className }: DropdownProps) {
    const [open, setOpen] = useState(false);
    const root = useRef<HTMLDivElement>(null);
    const close = useCallback(() => setOpen(false), []);
    useDismiss(root, open, close);

    return (
        <div ref={root} className={cn('relative', className)}>
            {trigger({ open, toggle: () => setOpen((value) => !value) })}
            {open && (
                <div className={cn('topnav-dropdown motion-safe:animate-[dropdown-in_150ms_ease-out]', align === 'end' ? 'end-0' : 'start-0', panelClassName)}>
                    {typeof children === 'function' ? children(close) : children}
                </div>
            )}
        </div>
    );
}
