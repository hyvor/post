<script lang="ts">
    import {Checkbox, Loader, SplitControl, Validation} from '@hyvor/design/components';
    import {getI18n} from '../../../../../lib/i18n';
    import {
        draftErrorsStore,
        draftIssueEditingStore,
        draftSendableSubscribersCountStore
    } from '../draftStore';
    import {debouncedUpdateDraftIssue} from '../draftActions';
    import {listStore} from '../../../../../lib/stores/newsletterStore';

    const I18n = getI18n();

    let currentLists = $state($draftIssueEditingStore.lists);

    function onChange(id: number) {
        $draftErrorsStore.lists = '';

        if (currentLists.includes(id)) {
            currentLists = currentLists.filter((s) => s !== id);

            if (currentLists.length === 0) {
                $draftErrorsStore.lists = I18n.t('console.issues.draft.listsEmptyError');
                return;
            }
        } else {
            currentLists = [...currentLists, id];
        }

        draftSendableSubscribersCountStore.update((state) => ({
            ...state,
            loading: true
        }));

        $draftIssueEditingStore.lists = currentLists;
        debouncedUpdateDraftIssue();
    }
</script>

<SplitControl label={I18n.t('console.issues.draft.lists')}>
    {#each $listStore as list (list.id)}
        <div class="list">
            <Checkbox checked={currentLists.includes(list.id)} on:change={() => onChange(list.id)}>
                {list.name}
                <span class="subscriber-count"
                >({I18n.t('console.issues.draft.subscribersCount', {
                    count: list.subscribers_count
                })})
				</span>
            </Checkbox>
        </div>
    {/each}
    {#if $draftErrorsStore.lists}
        <Validation state="error">{$draftErrorsStore.lists}</Validation>
    {:else}
        <div class="sendable-count">
            {#if $draftSendableSubscribersCountStore.loading}
                <Loader size="small"/>
            {:else}
                <I18n.T
                    key="console.issues.draft.sendableSubscribersCount"
                    params={{
						count: $draftSendableSubscribersCountStore.count,
						b: {
							element: 'strong'
						}
					}}
                />
            {/if}
        </div>
    {/if}
</SplitControl>

<style>
    .list {
        margin-bottom: 10px;
    }

    .subscriber-count {
        font-size: 14px;
        color: var(--text-light);
        margin-left: 3px;
    }

    .sendable-count {
        margin-top: 15px;
        font-size: 14px;
        color: var(--text-light);
    }
</style>
