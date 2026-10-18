import { cn } from '@/lib/cn';

/**
 * Renders HTML that the server has already passed through HTMLPurifier
 * (mews/purifier). Never pass unsanitised user input here.
 */
export function RichText({ html, className }: { html: string; className?: string }) {
    return <div className={cn('tiny-content rich-text', className)} dangerouslySetInnerHTML={{ __html: html }} />;
}
