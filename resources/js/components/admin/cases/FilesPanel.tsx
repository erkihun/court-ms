import { ArrowUpTrayIcon, DocumentIcon } from '@heroicons/react/24/outline';
import { useForm } from '@inertiajs/react';
import { type FormEvent, useRef } from 'react';
import { Button } from '@/components/ui/Button';
import { ConfirmButton } from '@/components/ui/ConfirmButton';
import { Input } from '@/components/ui/Input';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { CaseFileRow } from '@/types/admin/case';
import { CaseSection, EmptyBox } from './CaseSection';

interface FilesSectionProps {
    caseId: number;
    files: CaseFileRow[];
    canUpload: boolean;
    canDelete: boolean;
}

export function FilesSection({ caseId, files, canUpload, canDelete }: FilesSectionProps) {
    return (
        <CaseSection id="uploaded-files" title={t('cases.files.title')} count={files.length} countLabel={t('cases.files.total')}>
            {canUpload && <UploadForm caseId={caseId} />}

            {files.length === 0 ? (
                <EmptyBox>{t('cases.files.no_files')}</EmptyBox>
            ) : (
                <ul className="divide-y divide-(--border)">
                    {files.map((file) => (
                        <li key={file.id} className="flex flex-wrap items-center justify-between gap-3 rounded-lg px-3 py-3 hover:bg-(--surface-soft)">
                            <div className="min-w-0">
                                <div className="flex items-center gap-2 font-medium text-(--text)">
                                    <DocumentIcon className="h-4 w-4 shrink-0 text-(--text-subtle)" />
                                    <span className="truncate">{file.label}</span>
                                </div>
                                <div className="mt-1 flex flex-wrap items-center gap-x-3 text-xs text-(--text-subtle)">
                                    <span>{file.mime ?? t('cases.file')}</span>
                                    <span>• {file.sizeKb.toFixed(1)} KB</span>
                                    <span>• {file.uploaded}</span>
                                    {file.by && (
                                        <span>
                                            • {t('cases.files.uploaded_by')} {file.by}
                                        </span>
                                    )}
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                {file.url && (
                                    <a
                                        href={file.url}
                                        className="rounded-lg border border-(--border-strong) px-3 py-1.5 text-xs font-medium text-(--text-muted) hover:bg-(--surface-soft)"
                                    >
                                        {t('cases.documents.view')}
                                    </a>
                                )}
                                {canDelete && (
                                    <ConfirmButton
                                        url={route('cases.files.delete', [caseId, file.id])}
                                        title={t('cases.files.remove_confirm')}
                                        message={file.label}
                                    >
                                        {t('cases.general.delete')}
                                    </ConfirmButton>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </CaseSection>
    );
}

function UploadForm({ caseId }: { caseId: number }) {
    const input = useRef<HTMLInputElement>(null);
    const form = useForm<{ label: string; file: File | null }>({ label: '', file: null });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('cases.files.upload', caseId), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                if (input.current) input.current.value = '';
            },
        });
    };

    return (
        <form onSubmit={submit} className="space-y-2 rounded-lg border border-(--border) bg-(--surface-soft) p-4">
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-center">
                <label className="sr-only" htmlFor="case-file-label">
                    {t('cases.files.label_placeholder')}
                </label>
                <Input
                    id="case-file-label"
                    value={form.data.label}
                    onChange={(event) => form.setData('label', event.target.value)}
                    placeholder={t('cases.files.label_placeholder')}
                    maxLength={255}
                />
                <input
                    ref={input}
                    type="file"
                    required
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                    aria-label={t('cases.files.upload')}
                    onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                    className="text-sm text-(--text-muted) file:me-3 file:rounded-lg file:border-0 file:bg-(--surface-strong) file:px-4 file:py-2 file:font-medium"
                />
                <Button type="submit" variant="success" loading={form.processing} disabled={!form.data.file}>
                    <ArrowUpTrayIcon className="h-4 w-4" />
                    {t('cases.files.upload')}
                </Button>
            </div>
            {form.errors.file && <p className="text-xs text-red-600">{form.errors.file}</p>}
            {form.errors.label && <p className="text-xs text-red-600">{form.errors.label}</p>}
        </form>
    );
}
