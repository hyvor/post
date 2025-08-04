import type {Issue, IssueList, Newsletter, List, Palette} from '../types';
import publicApi from "$lib/publicApi";

interface InitNewsletterResponse {
    newsletter: Newsletter
    issues: IssueList[]
    lists: List[]
    palette: Palette
}

export function initNewsletter(slug: string) {
    return publicApi.get<InitNewsletterResponse>({
        endpoint: "/newsletter",
        data: {slug}
    });
}

export function getIssueHtml(issueUuid: string) {
    return publicApi.get<Issue>({
        endpoint: `/issues/${issueUuid}`,
    });
}
