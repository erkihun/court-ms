import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Field } from '@/components/ui/Field';
import { Input, Textarea } from '@/components/ui/Input';
import { cn } from '@/lib/cn';
import { t } from '@/lib/i18n';
import { route } from '@/lib/route';
import { CollectionPanel, EditorCard, FormActions, HiddenPill, RowActions, useEditor } from './CollectionPanel';
import type { Faq } from './types';

function FaqForm({ faq, onDone }: { faq: Faq | null; onDone: () => void }) {
    const form = useForm({ question: faq?.question ?? '', answer: faq?.answer ?? '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onDone };
        if (faq) {
            form.put(route('admin.landing.faqs.update', faq.id), options);
        } else {
            form.post(route('admin.landing.faqs.store'), options);
        }
    };

    return (
        <EditorCard title={faq ? t('admin_landing.common.edit_faq') : t('admin_landing.common.new_faq')}>
            <form onSubmit={submit} className="space-y-4" noValidate>
                <Field label={t('admin_landing.common.question')} error={form.errors.question} required>
                    <Input value={form.data.question} onChange={(event) => form.setData('question', event.target.value)} maxLength={500} required />
                </Field>
                <Field label={t('admin_landing.common.answer')} error={form.errors.answer} required>
                    <Textarea value={form.data.answer} onChange={(event) => form.setData('answer', event.target.value)} rows={4} maxLength={2000} required />
                </Field>
                <FormActions saveLabel={t('admin_landing.common.save_faq')} processing={form.processing} onCancel={onDone} />
            </form>
        </EditorCard>
    );
}

export function FaqsTab({ faqs }: { faqs: Faq[] }) {
    const editor = useEditor<Faq>();

    return (
        <CollectionPanel
            title={t('admin_landing.faqs.title')}
            description={t('admin_landing.faqs.description')}
            addLabel={t('admin_landing.faqs.add')}
            onAdd={editor.create}
            form={editor.isOpen && <FaqForm key={editor.item?.id ?? 'new'} faq={editor.item} onDone={editor.close} />}
            empty={faqs.length === 0}
            emptyText={t('admin_landing.common.no_faqs')}
        >
            {faqs.map((faq) => (
                <li
                    key={faq.id}
                    className={cn('flex items-start gap-4 rounded-xl border border-(--border) bg-(--surface-strong) p-3', !faq.isActive && 'opacity-60')}
                >
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-1.5">
                            <p className="font-semibold text-(--text)">{faq.question}</p>
                            <HiddenPill hidden={!faq.isActive} />
                        </div>
                        <p className="mt-1 line-clamp-2 text-xs text-(--text-subtle)">{faq.answer}</p>
                    </div>
                    <RowActions
                        label={faq.question}
                        isActive={faq.isActive}
                        toggleUrl={route('admin.landing.faqs.toggle', faq.id)}
                        deleteUrl={route('admin.landing.faqs.destroy', faq.id)}
                        onEdit={() => editor.edit(faq)}
                    />
                </li>
            ))}
        </CollectionPanel>
    );
}
