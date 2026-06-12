/* ──────────────────────────────────────────────────────────────────────────
   Ethiopian time picker (Amharic locale only).

   The native <input type="time"> shows the browser's English AM/PM 12-hour
   wheel, which can't be relabeled. This Alpine component replaces it with a
   dropdown of allowed Ethiopian-clock slots and writes the **Gregorian** 24h
   "HH:MM" value straight into the hidden field — exactly what hearing_at needs.

   The slot label is computed with the SAME formula EthiopianDate uses to
   display time, so what the admin picks is exactly what's later shown:
       ethiopianHour = (gregorianHour + 6) % 12   (0 → 12)
       meridiem      = gregorian AM → ከሰአት በፊት,  PM → ከሰአት በኋላ

   Allowed range (court hours), 30-minute steps, inclusive:
       2:00 ከሰአት በፊት  …  10:00 ከሰአት በኋላ
   which is Gregorian 08:00 → 16:00 (all same-day, no date rollover).
   ────────────────────────────────────────────────────────────────────────── */

const AM = 'ከሰአት በፊት';
const PM = 'ከሰአት በኋላ';

// Inclusive Gregorian stored range (minutes from midnight) and step.
const START_MIN = 8 * 60;    // 08:00 → 2:00 ከሰአት በፊት
const END_MIN = 16 * 60;     // 16:00 → 10:00 ከሰአት በኋላ
const STEP_MIN = 30;

function pad2(n) {
    return String(n).padStart(2, '0');
}

// Ethiopian label for a Gregorian time (mirrors EthiopianDate::formatEthiopianTime).
function labelFor(gregHour, minute) {
    let eth = (gregHour + 6) % 12;
    if (eth === 0) eth = 12;
    const meridiem = gregHour < 12 ? AM : PM;
    return `${pad2(eth)}:${pad2(minute)} ${meridiem}`;
}

function slotFor(totalMin) {
    const h = Math.floor(totalMin / 60);
    const m = totalMin % 60;
    return { value: `${pad2(h)}:${pad2(m)}`, label: labelFor(h, m) };
}

function buildSlots() {
    const out = [];
    for (let t = START_MIN; t <= END_MIN; t += STEP_MIN) {
        out.push(slotFor(t));
    }
    return out;
}

/**
 * @param {object} cfg
 * @param {string} cfg.value  initial Gregorian "HH:MM"
 */
export function ethTimePicker(cfg = {}) {
    return {
        open: false,
        slots: buildSlots(),
        selected: '', // stored Gregorian "HH:MM"

        init() {
            this.setValue(cfg.value || '08:00');
            this.$nextTick(() => {
                const hidden = this.$refs.hidden;
                if (hidden) {
                    if (hidden.value) this.setValue(hidden.value);
                    hidden.addEventListener('eth-time:set', () => this.setValue(hidden.value));
                }
            });
        },

        // Clamp/snap an incoming Gregorian value to the nearest allowed slot.
        setValue(val) {
            const m = /^(\d{1,2}):(\d{2})$/.exec(String(val || '').trim());
            let total = START_MIN;
            if (m) {
                total = parseInt(m[1], 10) * 60 + parseInt(m[2], 10);
            }
            if (total < START_MIN) total = START_MIN;
            if (total > END_MIN) total = END_MIN;
            total = START_MIN + Math.round((total - START_MIN) / STEP_MIN) * STEP_MIN;
            this.selected = slotFor(total).value;
            this.commit();
        },

        get display() {
            const slot = this.slots.find((s) => s.value === this.selected);
            return slot ? slot.label : '';
        },

        choose(value) {
            this.selected = value;
            this.commit();
            this.open = false;
        },

        commit() {
            const hidden = this.$refs.hidden;
            if (hidden && hidden.value !== this.selected) {
                hidden.value = this.selected;
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
    };
}
