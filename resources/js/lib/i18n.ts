/**
 * Laravel-compatible translations for React.
 *
 * Messages come from GET /api/i18n/{locale}.json (see App\Support\Translations):
 * PHP group files flattened to "group.key" plus the JSON string table, with the
 * fallback locale already merged underneath. They are loaded once, before the
 * first render, so t() can stay synchronous.
 */

type Replacements = Record<string, string | number | null | undefined>;

let messages: Record<string, string> = {};
let loadedFrom: string | null = null;

export async function loadTranslations(url: string): Promise<void> {
    if (!url || url === loadedFrom) {
        return;
    }

    const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    if (!response.ok) {
        throw new Error(`Could not load translations (${response.status})`);
    }

    messages = (await response.json()) as Record<string, string>;
    loadedFrom = url;
}

export function translationsLoadedFrom(): string | null {
    return loadedFrom;
}

export function hasTranslation(key: string): boolean {
    return Object.prototype.hasOwnProperty.call(messages, key);
}

/**
 * Same contract as Laravel's __(): returns the key itself when there is no
 * translation, and replaces :name, :Name and :NAME placeholders.
 */
export function t(key: string, replace: Replacements = {}): string {
    const line = messages[key] ?? key;

    return applyReplacements(line, replace);
}

/**
 * One pass over the line, like PHP's strtr() in Laravel's Translator: values
 * that themselves contain ":something" are never substituted again.
 */
function applyReplacements(line: string, replace: Replacements): string {
    const map = new Map<string, string>();
    for (const [key, raw] of Object.entries(replace)) {
        const value = String(raw ?? '');
        map.set(`:${key.charAt(0).toUpperCase()}${key.slice(1)}`, value.charAt(0).toUpperCase() + value.slice(1));
        map.set(`:${key.toUpperCase()}`, value.toUpperCase());
        map.set(`:${key}`, value);
    }

    if (map.size === 0) {
        return line;
    }

    // Longest placeholders first so ":name" never shadows ":names".
    const pattern = [...map.keys()]
        .sort((a, b) => b.length - a.length)
        .map((placeholder) => placeholder.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
        .join('|');

    return line.replace(new RegExp(pattern, 'g'), (match) => map.get(match) ?? match);
}
