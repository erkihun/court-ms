/* ──────────────────────────────────────────────────────────────────────────
   Ethiopian / Gregorian date-picker Alpine component.

   Drives <x-eth-date-input>. Renders a calendar popover and writes a hidden
   <input> whose value is ALWAYS a Gregorian `Y-m-d` string, so server-side
   code is unaffected by the locale. The visible text shows:
     locale 'am' → Ethiopian calendar + Amharic labels
     locale 'en' → Gregorian calendar + English labels

   Conversion math mirrors app/Support/EthiopianDate.php and the dashboard
   mini-calendar so all three stay in lock-step.
   ────────────────────────────────────────────────────────────────────────── */

const ET_EPOCH = 1724220.5; // JD of Meskerem 1, Year 1

function gregToJD(y, m, d) {
    if (m < 3) { m += 12; y--; }
    const a = Math.floor(y / 100);
    const b = 2 - a + Math.floor(a / 4);
    return Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + d + b - 1524.5;
}

function etToJD(ey, em, ed) {
    const yr = ey < 0 ? ey + 1 : ey;
    return ed + (em - 1) * 30 + (yr - 1) * 365 + Math.floor(yr / 4) + ET_EPOCH - 1;
}

function jdToGreg(jd) {
    const z = Math.floor(jd + 0.5);
    const a = Math.floor((z - 1867216.25) / 36524.25);
    const A = z + 1 + a - Math.floor(a / 4);
    const B = A + 1524;
    const C = Math.floor((B - 122.1) / 365.25);
    const D = Math.floor(365.25 * C);
    const E = Math.floor((B - D) / 30.6001);
    const day = B - D - Math.floor(30.6001 * E);
    const month = E < 14 ? E - 1 : E - 13;
    const year = month > 2 ? C - 4716 : C - 4715;
    return { y: year, m: month, d: day };
}

function gregToEt(y, m, d) {
    const jd = gregToJD(y, m, d);
    const c = Math.floor(jd) + 0.5 - ET_EPOCH;
    let ey = Math.floor((c - Math.floor((c + 366) / 1461)) / 365) + 1;
    if (ey <= 0) ey--;
    const yearStart = etToJD(ey, 1, 1);
    const doy = Math.floor(jd) + 0.5 - yearStart + 1;
    const em = Math.floor((doy - 1) / 30) + 1;
    const ed = Math.round(doy - (em - 1) * 30);
    return { y: ey, m: em, d: ed };
}

// Convert an Ethiopian Y/M/D to Gregorian {y,m,d}
function etToGreg(ey, em, ed) {
    return jdToGreg(etToJD(ey, em, ed));
}

// First Gregorian day of an Ethiopian month (for day-of-week padding)
function etMonthStart(ey, em) {
    return jdToGreg(etToJD(ey, em, 1));
}

// Days in an Ethiopian month (30 for months 1-12; 5 or 6 for Pagumē)
function etMonthDays(ey, em) {
    if (em <= 12) return 30;
    return (ey % 4 === 3) ? 6 : 5;
}

function gregDow(y, m, d) {
    return new Date(y, m - 1, d).getDay();
}

function pad2(n) {
    return String(n).padStart(2, '0');
}

const GREG_MONTHS = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];
const ET_MONTHS = ['መስከረም', 'ጥቅምት', 'ኅዳር', 'ታህሳስ', 'ጥር', 'የካቲት',
    'መጋቢት', 'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜን'];
const GREG_DOW = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
const ET_DOW = ['እሑ', 'ሰኞ', 'ማክ', 'ረቡ', 'ሐሙ', 'አርብ', 'ቅዳ'];

/**
 * Alpine factory for a single date input.
 * @param {object} cfg
 * @param {string} cfg.locale  'am' | 'en'
 * @param {string} cfg.value   initial Gregorian Y-m-d (may be empty)
 * @param {object} cfg.today   {y,m,d} Gregorian "today" from the server
 */
export function ethDatePicker(cfg = {}) {
    const isAmharic = cfg.locale === 'am';
    const gt = cfg.today; // Gregorian today {y,m,d}

    return {
        isAmharic,
        open: false,
        // hidden field value — always Gregorian Y-m-d
        gregValue: cfg.value || '',
        // selected date parts (Gregorian) or null
        sel: null,
        // currently displayed month
        year: 0,
        month: 0, // 1-based
        cells: [],
        dowHeaders: isAmharic ? ET_DOW : GREG_DOW,

        init() {
            this.parseInitial();
            const base = this.sel || gt;
            if (isAmharic) {
                const e = gregToEt(base.y, base.m, base.d);
                this.year = e.y; this.month = e.m;
            } else {
                this.year = base.y; this.month = base.m;
            }
            this.build();
            // keep the calendar grid in sync if the value is changed externally
            this.$watch('gregValue', () => this.parseInitial());
        },

        parseInitial() {
            if (this.gregValue && /^\d{4}-\d{2}-\d{2}$/.test(this.gregValue)) {
                const [y, m, d] = this.gregValue.split('-').map(Number);
                this.sel = { y, m, d };
            } else {
                this.sel = null;
            }
        },

        // ── Display string for the text field ──
        get display() {
            if (!this.sel) return '';
            if (isAmharic) {
                const e = gregToEt(this.sel.y, this.sel.m, this.sel.d);
                return `${ET_MONTHS[e.m - 1]} ${e.d}፣ ${e.y} ዓ.ም`;
            }
            return `${this.sel.d} ${GREG_MONTHS[this.sel.m - 1]} ${this.sel.y}`;
        },

        get monthLabel() {
            return isAmharic ? (ET_MONTHS[this.month - 1] ?? '') : (GREG_MONTHS[this.month - 1] ?? '');
        },
        get yearLabel() {
            return isAmharic ? `${this.year} ዓ.ም` : String(this.year);
        },

        build() {
            const cells = [];
            const et = gregToEt(gt.y, gt.m, gt.d); // today in ET
            const selEt = this.sel ? gregToEt(this.sel.y, this.sel.m, this.sel.d) : null;

            if (isAmharic) {
                const days = etMonthDays(this.year, this.month);
                const firstGreg = etMonthStart(this.year, this.month);
                const startDow = gregDow(firstGreg.y, firstGreg.m, firstGreg.d);

                const prevMonth = this.month === 1 ? 13 : this.month - 1;
                const prevYear = this.month === 1 ? this.year - 1 : this.year;
                const prevDays = etMonthDays(prevYear, prevMonth);
                for (let i = startDow - 1; i >= 0; i--) {
                    cells.push({ d: prevDays - i, cur: false, today: false, selected: false, key: 'p' + i });
                }
                for (let d = 1; d <= days; d++) {
                    const isToday = d === et.d && this.month === et.m && this.year === et.y;
                    const isSel = selEt && d === selEt.d && this.month === selEt.m && this.year === selEt.y;
                    cells.push({ d, cur: true, today: isToday, selected: !!isSel, key: 'c' + d });
                }
            } else {
                const startDow = new Date(this.year, this.month - 1, 1).getDay();
                const days = new Date(this.year, this.month, 0).getDate();
                const prevDays = new Date(this.year, this.month - 1, 0).getDate();

                for (let i = startDow - 1; i >= 0; i--) {
                    cells.push({ d: prevDays - i, cur: false, today: false, selected: false, key: 'p' + i });
                }
                for (let d = 1; d <= days; d++) {
                    const isToday = d === gt.d && this.month === gt.m && this.year === gt.y;
                    const isSel = this.sel && d === this.sel.d && this.month === this.sel.m && this.year === this.sel.y;
                    cells.push({ d, cur: true, today: isToday, selected: !!isSel, key: 'c' + d });
                }
            }

            const rem = cells.length % 7;
            if (rem !== 0) {
                for (let i = 1; i <= 7 - rem; i++) {
                    cells.push({ d: i, cur: false, today: false, selected: false, key: 'n' + i });
                }
            }
            this.cells = cells;
        },

        prev() {
            if (this.month === 1) { this.month = isAmharic ? 13 : 12; this.year--; }
            else { this.month--; }
            this.build();
        },
        next() {
            const max = isAmharic ? 13 : 12;
            if (this.month === max) { this.month = 1; this.year++; }
            else { this.month++; }
            this.build();
        },

        // Pick a day from the current month grid
        pick(cell) {
            if (!cell.cur) return;
            let g;
            if (isAmharic) {
                g = etToGreg(this.year, this.month, cell.d);
            } else {
                g = { y: this.year, m: this.month, d: cell.d };
            }
            this.gregValue = `${g.y}-${pad2(g.m)}-${pad2(g.d)}`;
            this.sel = g;
            this.build();
            this.open = false;
        },

        clear() {
            this.gregValue = '';
            this.sel = null;
            this.build();
        },

        goToday() {
            this.gregValue = `${gt.y}-${pad2(gt.m)}-${pad2(gt.d)}`;
            this.sel = { ...gt };
            if (isAmharic) {
                const e = gregToEt(gt.y, gt.m, gt.d);
                this.year = e.y; this.month = e.m;
            } else {
                this.year = gt.y; this.month = gt.m;
            }
            this.build();
            this.open = false;
        },
    };
}
