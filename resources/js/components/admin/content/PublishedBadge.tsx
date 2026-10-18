import { Badge } from '@/components/ui/Badge';
import { t } from '@/lib/i18n';

/** Published/draft chip for CMS entries (about pages, terms). */
export function PublishedBadge({ published }: { published: boolean }) {
    return <Badge tone={published ? 'success' : 'neutral'}>{published ? t('about.status_published') : t('about.status_draft')}</Badge>;
}
