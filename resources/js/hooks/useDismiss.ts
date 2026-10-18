import { type RefObject, useEffect } from 'react';

/** Calls `onDismiss` on a click outside `ref` or on Escape, while `active`. */
export function useDismiss(ref: RefObject<HTMLElement | null>, active: boolean, onDismiss: () => void): void {
    useEffect(() => {
        if (!active) {
            return;
        }

        const onPointer = (event: MouseEvent | TouchEvent) => {
            if (ref.current && !ref.current.contains(event.target as Node)) {
                onDismiss();
            }
        };
        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onDismiss();
            }
        };

        document.addEventListener('mousedown', onPointer);
        document.addEventListener('touchstart', onPointer);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('mousedown', onPointer);
            document.removeEventListener('touchstart', onPointer);
            document.removeEventListener('keydown', onKey);
        };
    }, [ref, active, onDismiss]);
}
