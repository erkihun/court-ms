/** A4, Word-like styles for printed case documents (Ethiopic fonts first in line after Times). */
const PRINT_STYLES = `
@page { size: A4; margin: 2.54cm; }
body { font-family: 'Times New Roman', 'Nyala', 'Abyssinica SIL', 'Noto Sans Ethiopic', serif; font-size: 12pt; line-height: 1.7; color: #000; margin: 0; }
h1 { font-size: 16pt; text-align: center; margin: 0 0 4pt; }
.doc-meta-row { display: flex; justify-content: space-between; gap: 24pt; margin: 0 0 18pt; font-size: 11pt; }
.doc-meta-row p { margin: 0; font-weight: bold; }
h2 { font-size: 13pt; margin: 16pt 0 6pt; border-bottom: 1px solid #000; padding-bottom: 2pt; }
.doc-fields { border-collapse: collapse; width: 100%; }
.doc-fields td { padding: 2pt 6pt 2pt 0; vertical-align: top; }
.doc-fields td:first-child { width: 30%; font-weight: bold; }
.doc-terms-list { margin: 6pt 0 12pt; padding-left: 22pt; text-align: justify; }
.doc-terms-list li { margin: 0 0 5pt; }
.doc-body { text-align: justify; }
.doc-body p { margin: 0 0 8pt; }
.doc-body table { border-collapse: collapse; width: 100%; }
.doc-body td, .doc-body th { border: 1px solid #000; padding: 3pt 5pt; }
.doc-list { margin: 0; padding-left: 20pt; }
.doc-table { border-collapse: collapse; width: 100%; }
.doc-table th, .doc-table td { border: 1px solid #000; padding: 3pt 6pt; text-align: left; vertical-align: top; }
.doc-printed { margin-top: 24pt; text-align: right; font-size: 10pt; }
`;

/**
 * Print `bodyHtml` in a blank window and close it afterwards. Everything is
 * driven from this window, so the popup needs no inline script (CSP).
 */
export function printDocument(title: string, bodyHtml: string): void {
    const popup = window.open('', '_blank');
    if (!popup) return;

    const doc = popup.document;
    doc.open();
    doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title></title><style></style></head><body></body></html>');
    doc.close();
    doc.documentElement.lang = document.documentElement.lang;
    doc.title = title;
    doc.querySelector('style')!.textContent = PRINT_STYLES;
    doc.body.innerHTML = bodyHtml;

    const close = () => window.setTimeout(() => !popup.closed && popup.close(), 100);
    popup.addEventListener('afterprint', close);
    popup.focus();
    window.setTimeout(() => popup.print(), 300);
}
