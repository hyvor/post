import { writable } from "svelte/store";
import { type Issue } from "../../../../../types";

export const draftIssueStore = writable<Issue>({} as Issue);
export const draftIssueEditingStore = writable<Issue>({} as Issue);

export const draftErrorsStore = writable({} as Record<keyof Issue, string>);
export const draftPreviewKey = writable(0);
export const draftSendableSubscribersCountStore = writable({
    count: 0,
    loading: true,
});

export function initDraftStores(issue: Issue) {
    draftIssueStore.set(issue);
    draftIssueEditingStore.set({ ...issue });
}

export type StepKey = 'content' | 'audience';
export const draftStepStore = writable<Record<number, StepKey>>({});

draftIssueStore.subscribe(issue => {

    const issueId = issue?.id;
    if (!issueId) return;

    draftStepStore.update(steps => {
        if (steps[issueId] === undefined) {
            steps[issueId] = 'content';
        }
        return steps;
    });
});