import { type ClipboardEvent, type KeyboardEvent, useEffect, useRef } from 'react';

interface OtpInputProps {
    /** Used for the first box, so a <label htmlFor> focuses it. */
    id: string;
    value: string;
    onChange: (value: string) => void;
    length?: number;
    autoFocus?: boolean;
    invalid?: boolean;
    describedBy?: string;
}

/**
 * One box per digit. The joined value is what gets submitted; boxes only
 * accept digits, move focus forward on entry, back on Backspace, and spread a
 * pasted code across all boxes.
 */
export function OtpInput({ id, value, onChange, length = 6, autoFocus = false, invalid = false, describedBy }: OtpInputProps) {
    const boxes = useRef<Array<HTMLInputElement | null>>([]);
    const digits = Array.from({ length }, (_, index) => value[index] ?? '');

    useEffect(() => {
        if (autoFocus) boxes.current[0]?.focus();
    }, [autoFocus]);

    const focus = (index: number) => boxes.current[Math.max(0, Math.min(length - 1, index))]?.focus();

    const setDigit = (index: number, digit: string) => {
        const next = [...digits];
        next[index] = digit;
        onChange(next.join('').slice(0, length));
    };

    const onInput = (index: number, raw: string) => {
        const numeric = raw.replace(/\D/g, '');
        const digit = numeric ? numeric[numeric.length - 1]! : '';
        setDigit(index, digit);
        if (digit && index < length - 1) focus(index + 1);
    };

    const onKeyDown = (index: number, event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Backspace' && !digits[index] && index > 0) {
            event.preventDefault();
            setDigit(index - 1, '');
            focus(index - 1);
        } else if (event.key === 'ArrowLeft' && index > 0) {
            event.preventDefault();
            focus(index - 1);
        } else if (event.key === 'ArrowRight' && index < length - 1) {
            event.preventDefault();
            focus(index + 1);
        }
    };

    const onPaste = (event: ClipboardEvent<HTMLDivElement>) => {
        const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, length);
        if (!pasted) return;
        event.preventDefault();
        onChange(pasted);
        focus(Math.min(pasted.length, length - 1));
    };

    return (
        <div className="flex items-center justify-center gap-2 sm:gap-3" onPaste={onPaste} role="group" aria-describedby={describedBy}>
            {digits.map((digit, index) => (
                <input
                    key={index}
                    ref={(element) => {
                        boxes.current[index] = element;
                    }}
                    id={index === 0 ? id : undefined}
                    type="text"
                    inputMode="numeric"
                    autoComplete="one-time-code"
                    maxLength={1}
                    value={digit}
                    onChange={(event) => onInput(index, event.target.value)}
                    onKeyDown={(event) => onKeyDown(index, event)}
                    onFocus={(event) => event.target.select()}
                    aria-label={`${index + 1} / ${length}`}
                    aria-invalid={invalid || undefined}
                    className="auth-otp-box"
                />
            ))}
        </div>
    );
}
