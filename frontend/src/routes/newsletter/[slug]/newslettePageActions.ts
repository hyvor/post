import type { Issue, IssueList, Newsletter } from "./newsletterPageTypes";

interface InitNewsletterResponse {
    newsletter: Newsletter
    issues: IssueList[]
}


async function call<T>(
    endpoint: string,
    params: Record<string, any> = {},
): Promise<T> {

    const query = new URLSearchParams(params).toString();
  
    const response = await fetch(
        location.origin + `/api/public/newsletter-page${endpoint}?${query}`
    );

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message);
    }
    const data = await response.json();

    return data;
}

export function initNewsletter(slug: string) {
    return call<InitNewsletterResponse>("/newsletter", { slug });
}

export function getIssueHtml(issueUuid: string) {
    return call<Issue>("/issues/" + issueUuid);
}