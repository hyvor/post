import type { Issue, IssueList, Newsletter, List, Palette } from '../types';
import publicApi from "$lib/publicApi";

const ARCHIVE_PREFIX = '/archive';

interface InitNewsletterResponse {
    newsletter: Newsletter
    issues: IssueList[]
    palette: Palette
}

export function initNewsletter(subdomain: string) {
    return publicApi.get<InitNewsletterResponse>({
        endpoint: ARCHIVE_PREFIX + "/newsletter",
        data: { subdomain }
    });
}

export function getIssueHtml(issueUuid: string) {
    return publicApi.get<Issue>({
        endpoint: ARCHIVE_PREFIX + `/issues/${issueUuid}`,
    });
}
