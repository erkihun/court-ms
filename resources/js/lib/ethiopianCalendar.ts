/**
 * Ethiopian <-> Gregorian conversion. Mirrors app/Support/EthiopianDate.php
 * (and the legacy eth-date-picker.js) so server and client agree.
 */

export interface YMD {
    y: number;
    m: number;
    d: number;
}

const ET_EPOCH = 1724220.5; // Julian day of Meskerem 1, year 1

function gregToJD(year: number, month: number, day: number): number {
    let y = year;
    let m = month;
    if (m < 3) {
        m += 12;
        y--;
    }
    const a = Math.floor(y / 100);
    const b = 2 - a + Math.floor(a / 4);

    return Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + day + b - 1524.5;
}

function etToJD(ey: number, em: number, ed: number): number {
    const yr = ey < 0 ? ey + 1 : ey;

    return ed + (em - 1) * 30 + (yr - 1) * 365 + Math.floor(yr / 4) + ET_EPOCH - 1;
}

function jdToGreg(jd: number): YMD {
    const z = Math.floor(jd + 0.5);
    const alpha = Math.floor((z - 1867216.25) / 36524.25);
    const A = z + 1 + alpha - Math.floor(alpha / 4);
    const B = A + 1524;
    const C = Math.floor((B - 122.1) / 365.25);
    const D = Math.floor(365.25 * C);
    const E = Math.floor((B - D) / 30.6001);
    const d = B - D - Math.floor(30.6001 * E);
    const m = E < 14 ? E - 1 : E - 13;
    const y = m > 2 ? C - 4716 : C - 4715;

    return { y, m, d };
}

export function gregToEt({ y, m, d }: YMD): YMD {
    const jd = gregToJD(y, m, d);
    const c = Math.floor(jd) + 0.5 - ET_EPOCH;
    let ey = Math.floor((c - Math.floor((c + 366) / 1461)) / 365) + 1;
    if (ey <= 0) ey--;
    const doy = Math.floor(jd) + 0.5 - etToJD(ey, 1, 1) + 1;
    const em = Math.floor((doy - 1) / 30) + 1;

    return { y: ey, m: em, d: Math.round(doy - (em - 1) * 30) };
}

export function etToGreg({ y, m, d }: YMD): YMD {
    return jdToGreg(etToJD(y, m, d));
}

/** 30 days for months 1-12; 5 or 6 for Pagumē. */
export function etMonthDays(year: number, month: number): number {
    if (month <= 12) return 30;
    return year % 4 === 3 ? 6 : 5;
}

export const GREG_MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
export const ET_MONTHS = ['መስከረም', 'ጥቅምት', 'ኅዳር', 'ታህሳስ', 'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜን'];
export const GREG_DOW = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
export const ET_DOW = ['እሑ', 'ሰኞ', 'ማክ', 'ረቡ', 'ሐሙ', 'አርብ', 'ቅዳ'];

const pad2 = (n: number) => String(n).padStart(2, '0');

export function toIsoDate({ y, m, d }: YMD): string {
    return `${y}-${pad2(m)}-${pad2(d)}`;
}

export function parseIsoDate(value: string | null | undefined): YMD | null {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(value ?? '');
    return match ? { y: Number(match[1]), m: Number(match[2]), d: Number(match[3]) } : null;
}

export function todayYmd(): YMD {
    const now = new Date();
    return { y: now.getFullYear(), m: now.getMonth() + 1, d: now.getDate() };
}

/** "ጥቅምት 8፣ 2019 ዓ.ም" in Amharic, "18 October 2026" otherwise. */
export function formatCalendarDate(date: YMD, amharic: boolean): string {
    if (amharic) {
        const et = gregToEt(date);
        return `${ET_MONTHS[et.m - 1]} ${et.d}፣ ${et.y} ዓ.ም`;
    }
    return `${date.d} ${GREG_MONTHS[date.m - 1]} ${date.y}`;
}
