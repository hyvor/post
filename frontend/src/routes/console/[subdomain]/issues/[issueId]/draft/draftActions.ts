import {debounce} from "$lib/helpers/debounce";
import {get} from "svelte/store";
import type {Issue} from "../../../../types";
import {
    draftIssueEditingStore,
    draftIssueStore,
    draftPreviewKey,
    draftSendableSubscribersCountStore
} from "./draftStore";
import {updateIssue} from "../../../../lib/actions/issueActions";
import {toast} from "@hyvor/design/components";

export const debouncedUpdateDraftIssue = debounce(updateDraftIssue, 800);

export function updateDraftIssue() {

    const updatableFields: (keyof Issue)[] = [
        'subject',
        'content',
        'lists',
        'sending_profile_id'
    ]

    const changedFields: Partial<Issue> = {};

    const draftIssue = get(draftIssueStore);
    const draftIssueEditing = get(draftIssueEditingStore);

    for (const field of updatableFields) {
        if (field === 'lists') {
            if (JSON.stringify(draftIssue[field].sort()) !== JSON.stringify(draftIssueEditing[field].sort())) {
                (changedFields as any)[field] = draftIssueEditing[field];
            }
            continue;
        }
        if (draftIssue[field] !== draftIssueEditing[field]) {
            (changedFields as any)[field] = draftIssueEditing[field];
        }
    }

    const keys = Object.keys(changedFields) as (keyof Issue)[];
    if (keys.length === 0) {
        return;
    }

    const hasPreviewChanges = keys.some((key) => key === 'content' || key === 'subject');

    updateIssue(draftIssue.id, changedFields)
        .then((issue) => {
            draftIssueStore.set(issue);
            draftSendableSubscribersCountStore.set({
                loading: false,
                count: issue.sendable_subscribers_count
            });

            if (hasPreviewChanges) {
                draftPreviewKey.update((key) => key + 1);
            }
        })
        .catch((err) => {
            toast.error("Error updating draft issue:", err);
        });

}
