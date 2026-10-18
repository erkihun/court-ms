/** Header-bell feeds served by ApplicantNotificationFeed / RespondentNotificationFeed. */
export type ApplicantNotificationType = 'message' | 'hearing' | 'respondent_view' | 'status';

export interface ApplicantNotificationItem {
    type: ApplicantNotificationType;
    sourceId: number;
    caseId: number;
    caseNumber: string;
    href: string;
    text: string;
    when: string | null;
    /** External link carried by a message (letter preview), when the message is just a URL. */
    url: string | null;
}

export interface ApplicantNotificationFeedData {
    count: number;
    groups: Array<{ type: ApplicantNotificationType; items: ApplicantNotificationItem[] }>;
}

export type RespondentNotificationType = 'respondent_view' | 'hearing' | 'status' | 'message';

export interface RespondentNotificationItem {
    type: RespondentNotificationType;
    sourceId: number;
    caseNumber: string;
    title: string | null;
    href: string;
    when: string | null;
}

export interface RespondentNotificationFeedData {
    count: number;
    items: RespondentNotificationItem[];
}

/** An accepted respondent response, as the case owner sees it (App\Support\Party\RespondentResponseView). */
export interface RespondentResponseData {
    caseId: number;
    caseNumber: string;
    id: number;
    title: string;
    responseNumber: string | null;
    created: string;
    respondentName: string | null;
    respondentEmail: string | null;
    respondentPhone: string | null;
    respondentOrganization: string | null;
    summary: string | null;
    descriptionHtml: string;
    pdfInlineUrl: string | null;
}

export interface ResponseReplyData {
    id: number;
    description: string;
    reviewStatus: 'awaiting_review' | 'accepted' | 'returned' | 'rejected';
    reviewNote: string | null;
    created: string;
    locked: boolean;
    showUrl: string;
    editUrl: string;
    destroyUrl: string;
    downloadUrl: string;
    inlineUrl: string;
}
