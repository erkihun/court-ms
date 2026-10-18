import { CalendarDaysIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import { useRef, useState } from 'react';
import { useField } from '@/components/ui/Field';
import { useDismiss } from '@/hooks/useDismiss';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import {
    ET_DOW,
    ET_MONTHS,
    etMonthDays,
    etToGreg,
    formatCalendarDate,
    GREG_DOW,
    GREG_MONTHS,
    gregToEt,
    parseIsoDate,
    todayYmd,
    toIsoDate,
    type YMD,
} from '@/lib/ethiopianCalendar';

interface EthDateInputProps {
    /** Gregorian Y-m-d, or '' for none. The server always receives Gregorian. */
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
    invalid?: boolean;
    placeholder?: string;
    align?: 'start' | 'end';
}

interface Cell {
    key: string;
    day: number;
    current: boolean;
    today: boolean;
    selected: boolean;
}

/**
 * Date picker that shows the Ethiopian calendar in Amharic and the
 * Gregorian one in English, while always reading/writing a Gregorian date.
 */
export function EthDateInput({ value, onChange, disabled = false, invalid, placeholder, align = 'start' }: EthDateInputProps) {
    const amharic = useShared().app.locale === 'am';
    const field = useField();
    const root = useRef<HTMLDivElement>(null);
    const [open, setOpen] = useState(false);
    useDismiss(root, open, () => setOpen(false));

    const selected = parseIsoDate(value);
    const today = todayYmd();
    const initial = selected ?? today;
    const [view, setView] = useState<{ y: number; m: number }>(() => {
        const base = amharic ? gregToEt(initial) : initial;
        return { y: base.y, m: base.m };
    });

    const cells = buildCells(view, amharic, selected, today);
    const monthCount = amharic ? 13 : 12;
    const monthLabel = (amharic ? ET_MONTHS : GREG_MONTHS)[view.m - 1] ?? '';

    const step = (delta: 1 | -1) =>
        setView(({ y, m }) => {
            const next = m + delta;
            if (next < 1) return { y: y - 1, m: monthCount };
            if (next > monthCount) return { y: y + 1, m: 1 };
            return { y, m: next };
        });

    const pick = (day: number) => {
        const date = amharic ? etToGreg({ y: view.y, m: view.m, d: day }) : { y: view.y, m: view.m, d: day };
        onChange(toIsoDate(date));
        setOpen(false);
    };

    const goToday = () => {
        onChange(toIsoDate(today));
        const base = amharic ? gregToEt(today) : today;
        setView({ y: base.y, m: base.m });
        setOpen(false);
    };

    return (
        <div ref={root} className="relative">
            <button
                type="button"
                id={field?.id}
                aria-describedby={field?.describedBy}
                aria-invalid={invalid ?? field?.invalid ?? undefined}
                aria-haspopup="dialog"
                aria-expanded={open}
                disabled={disabled}
                onClick={() => setOpen((current) => !current)}
                className="ui-input flex items-center justify-between gap-2 text-left disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
            >
                <span className={selected ? undefined : 'text-slate-400'}>{selected ? formatCalendarDate(selected, amharic) : (placeholder ?? (amharic ? 'ቀን ይምረጡ' : 'Select date'))}</span>
                <CalendarDaysIcon className="h-4 w-4 shrink-0 text-slate-400" />
            </button>

            {open && (
                <div
                    role="dialog"
                    className={cn('absolute z-50 mt-1.5 w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-(--border-strong) bg-(--surface-strong) p-3 shadow-xl', align === 'end' ? 'end-0' : 'start-0')}
                >
                    <div className="mb-2 flex items-center justify-between">
                        <div className="text-sm font-semibold text-(--text)">
                            {monthLabel} <span className="text-(--text-subtle)">{amharic ? `${view.y} ዓ.ም` : view.y}</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <button type="button" onClick={() => step(-1)} className="rounded-lg p-1.5 hover:bg-(--surface-soft)" aria-label="Previous month">
                                <ChevronLeftIcon className="h-4 w-4" />
                            </button>
                            <button type="button" onClick={() => step(1)} className="rounded-lg p-1.5 hover:bg-(--surface-soft)" aria-label="Next month">
                                <ChevronRightIcon className="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div className="mb-1 grid grid-cols-7 gap-0.5">
                        {(amharic ? ET_DOW : GREG_DOW).map((dow) => (
                            <div key={dow} className="py-1 text-center text-[11px] font-medium text-(--text-subtle)">
                                {dow}
                            </div>
                        ))}
                    </div>

                    <div className="grid grid-cols-7 gap-0.5">
                        {cells.map((cell) => (
                            <button
                                key={cell.key}
                                type="button"
                                disabled={!cell.current}
                                onClick={() => pick(cell.day)}
                                aria-pressed={cell.selected}
                                className={cn(
                                    'h-8 rounded-lg text-sm transition-colors',
                                    !cell.current && 'text-(--text-subtle) opacity-40',
                                    cell.current && 'hover:bg-(--surface-soft)',
                                    cell.today && !cell.selected && 'font-semibold ring-1 ring-(--primary)',
                                    cell.selected && 'bg-(--primary) font-semibold text-white',
                                    cell.current && !cell.selected && 'text-(--text)',
                                )}
                            >
                                {cell.day}
                            </button>
                        ))}
                    </div>

                    <div className="mt-2 flex items-center justify-between border-t border-(--border) pt-2">
                        <button type="button" onClick={() => onChange('')} className="text-xs font-medium text-(--text-subtle) hover:text-(--text)">
                            {amharic ? 'አጽዳ' : 'Clear'}
                        </button>
                        <button type="button" onClick={goToday} className="text-xs font-medium text-(--primary) hover:opacity-80">
                            {amharic ? 'ዛሬ' : 'Today'}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}

function buildCells(view: { y: number; m: number }, amharic: boolean, selected: YMD | null, today: YMD): Cell[] {
    const cells: Cell[] = [];
    const mark = (date: YMD | null) => (date ? (amharic ? gregToEt(date) : date) : null);
    const todayView = mark(today);
    const selectedView = mark(selected);

    let days: number;
    let startDow: number;
    let prevDays: number;
    if (amharic) {
        days = etMonthDays(view.y, view.m);
        const first = etToGreg({ y: view.y, m: view.m, d: 1 });
        startDow = new Date(first.y, first.m - 1, first.d).getDay();
        prevDays = view.m === 1 ? etMonthDays(view.y - 1, 13) : etMonthDays(view.y, view.m - 1);
    } else {
        days = new Date(view.y, view.m, 0).getDate();
        startDow = new Date(view.y, view.m - 1, 1).getDay();
        prevDays = new Date(view.y, view.m - 1, 0).getDate();
    }

    for (let i = startDow - 1; i >= 0; i--) {
        cells.push({ key: `p${i}`, day: prevDays - i, current: false, today: false, selected: false });
    }
    const same = (date: YMD | null, day: number) => date !== null && date.y === view.y && date.m === view.m && date.d === day;
    for (let day = 1; day <= days; day++) {
        cells.push({ key: `c${day}`, day, current: true, today: same(todayView, day), selected: same(selectedView, day) });
    }
    const remainder = cells.length % 7;
    for (let i = 1; remainder !== 0 && i <= 7 - remainder; i++) {
        cells.push({ key: `n${i}`, day: i, current: false, today: false, selected: false });
    }

    return cells;
}
