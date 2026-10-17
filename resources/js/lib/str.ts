/** Laravel's Str::headline(): "case-inspection_requests" -> "Case Inspection Requests". */
export function headline(value: string): string {
    return value
        .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
        .split(/[\s_.-]+/)
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

/** First letter(s) for avatar placeholders. */
export function initials(value: string, length = 1): string {
    return Array.from(value.trim()).slice(0, length).join('').toUpperCase() || '?';
}
