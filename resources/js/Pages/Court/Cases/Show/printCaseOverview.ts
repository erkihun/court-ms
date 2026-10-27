import type { CaseDetailCase, CaseDocument, CaseWitness } from '@/court/types/caseDetail';

export interface PrintLabels {
    title: string;
    caseNumber: string;
    filingDate: string;
    applicant: string;
    submittedByLawyer: string | null;
    name: string;
    address: string;
    email: string;
    respondent: string;
    details: string;
    noDetails: string;
    relief: string;
    noRelief: string;
    documents: string;
    document: string;
    witnesses: string;
    phone: string;
    printedAt: string;
}

export interface PrintData {
    kase: CaseDetailCase;
    filingDate: string;
    docs: Array<CaseDocument & { created_display: string | null }>;
    witnesses: CaseWitness[];
    termsItems: string[];
    locale: string;
}

const escape = (value: string | null | undefined): string =>
    String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

const dash = (value: string | null | undefined): string => {
    const text = (value ?? '').trim();
    return text === '' ? '—' : escape(text);
};

/**
 * The Word-style printable overview the Blade page opened in a new window:
 * the same A4 stylesheet, the same sections, closed again after printing.
 * Returns false when the browser blocked the window.
 */
export function printCaseOverview(labels: PrintLabels, data: PrintData): boolean {
    const { kase } = data;
    const body = `
<h1>${escape(labels.title)}</h1>
<div class="doc-meta-row">
    <p>${escape(labels.caseNumber)} <strong>${escape(kase.case_number)}</strong></p>
    <p>${escape(labels.filingDate)} <strong>${escape(data.filingDate)}</strong></p>
</div>
<h2>${escape(labels.applicant)}</h2>
${labels.submittedByLawyer ? `<p><strong>${escape(labels.submittedByLawyer)}</strong></p>` : ''}
<table class="doc-fields">
    <tr><td>${escape(labels.name)}</td><td>${dash(kase.applicant_display_name)}</td></tr>
    <tr><td>${escape(labels.address)}</td><td>${dash(kase.applicant_address)}</td></tr>
    <tr><td>${escape(labels.email)}</td><td>${dash(kase.applicant_email)}</td></tr>
</table>
<h2>${escape(labels.respondent)}</h2>
<table class="doc-fields">
    <tr><td>${escape(labels.name)}</td><td>${dash(kase.respondent_name)}</td></tr>
    <tr><td>${escape(labels.address)}</td><td>${dash(kase.respondent_address)}</td></tr>
</table>
${data.termsItems.length > 0 ? `<ol class="doc-terms-list">${data.termsItems.map((item) => `<li>${item}</li>`).join('')}</ol>` : ''}
<h2>${escape(labels.details)}</h2>
<div class="doc-body">${kase.description_html || escape(labels.noDetails)}</div>
<h2>${escape(labels.relief)}</h2>
<div class="doc-body">${kase.relief_html ?? escape(labels.noRelief)}</div>
${data.docs.length > 0 ? `<h2>${escape(labels.documents)}</h2><ol class="doc-list">${data.docs.map((doc) => `<li>${dash(doc.title) === '—' ? escape(labels.document) : escape(doc.title)}${doc.created_display ? ` — ${escape(doc.created_display)}` : ''}</li>`).join('')}</ol>` : ''}
${data.witnesses.length > 0 ? `<h2>${escape(labels.witnesses)}</h2><table class="doc-table"><thead><tr><th>${escape(labels.name)}</th><th>${escape(labels.phone)}</th><th>${escape(labels.email)}</th><th>${escape(labels.address)}</th></tr></thead><tbody>${data.witnesses.map((w) => `<tr><td>${escape(w.full_name)}</td><td>${dash(w.phone)}</td><td>${dash(w.email)}</td><td>${dash(w.address)}</td></tr>`).join('')}</tbody></table>` : ''}
<p class="doc-printed">${escape(labels.printedAt)}</p>`;

    const w = window.open('', '_blank');
    if (!w) return false;

    w.document.write(`<!DOCTYPE html>
<html lang="${escape(data.locale.replace('_', '-'))}">
<head>
<meta charset="utf-8">
<title>${escape(labels.title)} - ${escape(kase.case_number)}</title>
<style>
    @page { size: A4; margin: 2.54cm; }
    body { font-family: 'Times New Roman', 'Nyala', 'Abyssinica SIL', 'Noto Sans Ethiopic', serif; font-size: 12pt; line-height: 1.7; color: #000; margin: 0; }
    h1 { font-size: 16pt; text-align: center; margin: 0 0 4pt; }
    .doc-meta-row { display: flex; justify-content: space-between; gap: 24pt; margin: 0 0 18pt; font-size: 11pt; }
    .doc-meta-row p { margin: 0; font-weight: bold; }
    .doc-meta-row p:first-child { text-align: left; }
    .doc-meta-row p:last-child { text-align: right; }
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
    .doc-table th { font-weight: bold; }
    .doc-printed { margin-top: 24pt; text-align: right; font-size: 10pt; }
</style>
</head>
<body>${body}</body>
</html>`);
    w.document.close();
    w.focus();

    const closePrintWindow = () => {
        setTimeout(() => {
            if (!w.closed) w.close();
        }, 100);
    };
    w.addEventListener('afterprint', closePrintWindow);
    if (w.matchMedia) {
        const mediaQueryList = w.matchMedia('print');
        mediaQueryList.addEventListener('change', (event) => {
            if (!event.matches) closePrintWindow();
        });
    }
    setTimeout(() => {
        w.print();
        setTimeout(closePrintWindow, 500);
    }, 300);

    return true;
}
