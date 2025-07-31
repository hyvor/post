import type {Issue, IssueList, Newsletter, Palette} from '../types';
import publicApi from "$lib/publicApi";
import {subdomainStore} from "$lib/archiveStore";
import {get} from "svelte/store";

interface InitNewsletterResponse {
    newsletter: Newsletter
    issues: IssueList[]
    palette_light: Palette
    palette_dark: Palette
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
