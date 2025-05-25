import { writable } from "svelte/store";
import { type Issue } from "../../../../types";

export const draftIssueStore = writable<Issue>({} as Issue);
export const draftIssueEditingStore = writable<Issue>({} as Issue);

export const draftErrorsStore = writable({} as Record<keyof Issue, string>);

export function initDraftStores(issue: Issue) {
    draftIssueStore.set(issue);
    draftIssueEditingStore.set({ ...issue });
}