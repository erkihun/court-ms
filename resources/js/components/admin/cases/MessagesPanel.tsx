import { ChatBubbleLeftRightIcon, LockClosedIcon, PaperAirplaneIcon } from '@heroicons/react/24/outline';
import { useForm } from '@inertiajs/react';
import { type FormEvent, type KeyboardEvent, useEffect, useRef } from 'react';
import { useShared } from '@/hooks/useShared';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import type { CaseMessage } from '@/types/admin/case';

const initial = (name: string) => name.trim().charAt(0).toLocaleUpperCase() || '?';

interface MessagesSectionProps {
    caseId: number;
    caseNumber: string;
    messages: CaseMessage[];
    canPost: boolean;
    locked: boolean;
}

export function MessagesSection({ caseId, caseNumber, messages, canPost, locked }: MessagesSectionProps) {
    const me = useShared().auth.user;
    const thread = useRef<HTMLDivElement>(null);
    const form = useForm({ body: '' });

    // Newest message in view after loading or sending.
    useEffect(() => {
        thread.current?.scrollTo({ top: thread.current.scrollHeight });
    }, [messages.length]);

    const submit = (event?: FormEvent) => {
        event?.preventDefault();
        if (!form.data.body.trim() || form.processing) return;
        form.post(route('cases.messages.post', caseId), { preserveScroll: true, onSuccess: () => form.reset() });
    };

    const onKeyDown = (event: KeyboardEvent<HTMLTextAreaElement>) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            submit();
        }
    };

    return (
        <section id="messages" aria-labelledby="messages-title" className="ui-card scroll-mt-24 overflow-hidden">
            <div className="flex items-center justify-between border-b border-(--border) bg-(--surface-soft) px-5 py-4">
                <div className="flex items-center gap-3">
                    <ChatBubbleLeftRightIcon className="h-5 w-5 text-(--text-subtle)" />
                    <div>
                        <h2 id="messages-title" className="text-sm font-bold text-(--text)">
                            {t('cases.messages_section.title')}
                        </h2>
                        <p className="mt-0.5 text-[11px] text-(--text-subtle)">
                            {t('cases.case_number')}: {caseNumber}
                        </p>
                    </div>
                </div>
                <span className="rounded-full bg-(--surface-strong) px-2.5 py-1 text-xs font-medium text-(--text-muted)">
                    {messages.length} {t('cases.messages.total')}
                </span>
            </div>

            <div ref={thread} className="max-h-[480px] min-h-[220px] space-y-4 overflow-y-auto px-5 py-4" aria-live="polite">
                {messages.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-16 text-center">
                        <p className="text-sm font-medium text-(--text-muted)">{t('cases.messages_section.no_messages')}</p>
                    </div>
                ) : (
                    messages.map((message) => (
                        <div key={message.id} className={cn('flex items-end gap-2.5', message.fromStaff && 'flex-row-reverse')}>
                            <div
                                className={cn(
                                    'grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold shadow-xs',
                                    message.fromStaff ? 'bg-[rgb(var(--ac))] text-white' : 'border border-(--border) bg-(--surface-soft) text-(--text-subtle)',
                                )}
                                aria-hidden="true"
                            >
                                {initial(message.who)}
                            </div>
                            <div className={cn('flex max-w-[72%] flex-col space-y-1', message.fromStaff ? 'items-end' : 'items-start')}>
                                <div className={cn('flex items-center gap-2', message.fromStaff && 'flex-row-reverse')}>
                                    <span className="text-[11.5px] font-semibold text-(--text)">{message.who}</span>
                                    <span className="text-[10.5px] text-(--text-subtle)">{message.when}</span>
                                </div>
                                <div
                                    className={cn(
                                        'rounded-2xl px-4 py-2.5 text-sm leading-relaxed wrap-break-word whitespace-pre-wrap shadow-xs',
                                        message.fromStaff
                                            ? 'rounded-br-sm bg-[rgb(var(--ac))] text-white'
                                            : 'rounded-bl-sm border border-(--border) bg-(--surface-soft) text-(--text)',
                                    )}
                                >
                                    {message.body}
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {locked ? (
                <div className="flex items-center gap-2.5 border-t border-(--border) bg-amber-50/60 px-5 py-3 text-xs text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                    <LockClosedIcon className="h-4 w-4" />
                    {t('cases.show.actions_locked')}
                </div>
            ) : (
                canPost && (
                    <form onSubmit={submit} className="border-t border-(--border) bg-(--surface-soft) px-5 py-4">
                        <div className="flex items-end gap-3">
                            <div
                                className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[rgb(var(--ac))] text-xs font-bold text-white shadow-xs"
                                aria-hidden="true"
                            >
                                {initial(me?.name ?? 'A')}
                            </div>
                            <div className="relative flex-1">
                                <label htmlFor="case-message-body" className="sr-only">
                                    {t('cases.messages_section.write_message_placeholder')}
                                </label>
                                <textarea
                                    id="case-message-body"
                                    rows={2}
                                    value={form.data.body}
                                    onChange={(event) => form.setData('body', event.target.value)}
                                    onKeyDown={onKeyDown}
                                    maxLength={5000}
                                    placeholder={t('cases.messages_section.write_message_placeholder')}
                                    aria-invalid={!!form.errors.body || undefined}
                                    className="ui-textarea max-h-40 min-h-[42px] w-full resize-y rounded-2xl pe-12"
                                />
                                <button
                                    type="submit"
                                    disabled={!form.data.body.trim() || form.processing}
                                    aria-label={t('cases.show.send')}
                                    className="absolute end-2 bottom-3 grid h-7 w-7 place-items-center rounded-xl bg-[rgb(var(--ac))] text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:bg-(--border) disabled:text-(--text-subtle)"
                                >
                                    <PaperAirplaneIcon className="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        {form.errors.body && <p className="mt-2 text-xs text-red-600">{form.errors.body}</p>}
                        <p className="mt-2 ps-11 text-[10.5px] text-(--text-subtle)">
                            {t('cases.messages_section.reply_to_applicant')} · {t('cases.show.send_hint')}
                        </p>
                    </form>
                )
            )}
        </section>
    );
}
