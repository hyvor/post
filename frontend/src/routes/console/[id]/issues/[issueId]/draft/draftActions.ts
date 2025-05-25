import { debounce } from "$lib/helpers/debounce";
import { get } from "svelte/store";
import type { Issue } from "../../../../types";
import { draftIssueEditingStore, draftIssueStore, draftPreviewKey } from "./draftStore";
import { updateIssue } from "../../../../lib/actions/issueActions";
import { toast } from "@hyvor/design/components";

export const debouncedUpdateDraftIssue = debounce(updateDraftIssue, 1000);

export function updateDraftIssue() {

    const updatableFields : (keyof Issue)[] = [
        'subject',
        'from_name',
        'from_email',
        'reply_to_email',
        'content',
        'lists'
    ]

    const changedFields: Partial<Issue> = {};

    const draftIssue = get(draftIssueStore);
    const draftIssueEditing = get(draftIssueEditingStore);

    for (const field of updatableFields) {
        if (draftIssue[field] !== draftIssueEditing[field]) {
            (changedFields as any)[field] = draftIssueEditing[field];
        }
    }

    const keys = Object.keys(changedFields) as (keyof Issue)[];
    if (keys.length === 0) {
        return;
    }

    const hasPreviewChanges = keys.some((key) => key === 'content' || key === 'lists' || key === 'subject');

    updateIssue(draftIssue.id, changedFields)
        .then((issue) => {
            draftIssueStore.set(issue);

            if (hasPreviewChanges) {
                draftPreviewKey.update((key) => key + 1);
            }
        })
        .catch((err) => {
            toast.error("Error updating draft issue:", err);
        });

}