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
