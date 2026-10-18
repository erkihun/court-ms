/** GET a same-origin JSON endpoint; null on any failure (callers keep their last good state). */
export async function getJson<T>(url: string): Promise<T | null> {
    try {
        const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        return response.ok ? ((await response.json()) as T) : null;
    } catch {
        return null;
    }
}
