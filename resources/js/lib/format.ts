/** "1234567812345678" -> "1234 5678 1234 5678" (the server strips the spaces again). */
export function formatNationalId(value: string): string {
    return (
        value
            .replace(/\D/g, '')
            .slice(0, 16)
            .match(/.{1,4}/g)
            ?.join(' ') ?? ''
    );
}
