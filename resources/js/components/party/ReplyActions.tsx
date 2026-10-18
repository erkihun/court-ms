import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';
import { t } from '@/lib/i18n';
import type { ResponseReplyData } from '@/types/party';

/** View / edit / delete for an applicant reply; edit and delete disappear once the court accepts it. */
export function ReplyActions({ reply, showView = true }: { reply: ResponseReplyData; showView?: boolean }) {
    const [confirming, setConfirming] = useState(false);

    return (
        <div className="flex flex-wrap items-center gap-2 text-xs">
            {showView && (
                <Link href={reply.showUrl} className="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 font-semibold text-blue-700 hover:bg-blue-100">
                    {t('cases.view')}
                </Link>
            )}
            {!reply.locked && (
                <>
                    <Link href={reply.editUrl} className="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-700 hover:bg-amber-100">
                        {t('cases.general.edit')}
                    </Link>
                    <button type="button" onClick={() => setConfirming(true)} className="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1 font-semibold text-red-700 hover:bg-red-100">
                        {t('cases.general.delete')}
                    </button>
                    <ConfirmDialog
                        open={confirming}
                        title={t('cases.general.delete')}
                        message={t('cases.confirm.remove_document')}
                        confirmLabel={t('cases.general.delete')}
                        onCancel={() => setConfirming(false)}
                        onConfirm={() => router.delete(reply.destroyUrl, { onFinish: () => setConfirming(false) })}
                    />
                </>
            )}
        </div>
    );
}
