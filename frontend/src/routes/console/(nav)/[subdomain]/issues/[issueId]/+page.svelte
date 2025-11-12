<script lang="ts">
    import {page} from '$app/state';
    import {onMount} from 'svelte';
    import {IconMessage, Loader, confirm, toast} from '@hyvor/design/components';
    import {goto} from '$app/navigation';
    import {consoleUrlWithNewsletter} from '../../../../lib/consoleUrl';
    import {issueStore} from '../../../../lib/stores/newsletterStore';
    import type {Issue} from '../../../../types';
    import {deleteIssue, getIssue} from '../../../../lib/actions/issueActions';
    import DraftIssue from './draft/DraftIssue.svelte';
    import IssueSending from './sending/IssueSending.svelte';
    import SentIssue from './sent/SentIssue.svelte';
    import SingleBox from '../../../../@components/content/SingleBox.svelte';
    import {draftSendableSubscribersCountStore} from './draft/draftStore';
    import {getI18n} from '../../../../lib/i18n';

    const id = Number(page.params.issueId);

    let issue: Issue | undefined = $state(undefined);
    let loading = $state(true);
    let error: null | string = $state(null);

    const I18n = getI18n();

    // TODO: Not called anywhere
    async function onDelete() {
        const confirmed = await confirm({
            title: I18n.t('console.issues.delete.title'),
            content: I18n.t('console.issues.delete.content'),
            confirmText: I18n.t('console.issues.delete.confirmText'),
            cancelText: I18n.t('console.common.cancel'),
            danger: true
        });

        if (confirmed) {
            confirmed.loading();

            deleteIssue(id)
                .then(() => {
                    $issueStore = $issueStore.filter((i) => i.id !== id);
                    toast.success(I18n.t('console.issues.delete.success'));
                    goto(consoleUrlWithNewsletter('/issues'));
                })
                .catch((err) => {
                    toast.error(err.message);
                })
                .finally(() => {
                    confirmed.close();
                });
        }
    }

    function fetchIssue() {
        getIssue(id)
            .then((res) => {
                issue = res;

                draftSendableSubscribersCountStore.set({
                    loading: false,
                    count: res.sendable_subscribers_count
                });
            })
            .catch((err) => {
                error = err.message;
            })
            .finally(() => {
                loading = false;
            });
    }

    function onSendingStart(e: Issue) {
        issue = e;
    }

    function onSendingComplete() {
        fetchIssue();
    }

    onMount(() => {
        fetchIssue();
    });
</script>

<SingleBox>
    <div class="wrap">
        {#if loading}
            <Loader full/>
        {:else if error}
            <IconMessage error message={error}/>
        {:else if issue}
            <div class="content">
                {#if issue.status === 'draft'}
                    <DraftIssue {issue} send={onSendingStart}/>
                {:else if issue.status === 'sending'}
                    <IssueSending {issue} complete={onSendingComplete}/>
                {:else if issue.status === 'sent' || issue.status === 'failed'}
                    <SentIssue {issue}/>
                {/if}
            </div>
        {/if}
    </div>
</SingleBox>

<style>
    .wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: auto;
    }

    .content {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    @media (max-width: 992px) {
        .wrap {
            padding: 15px 0;
        }
    }
</style>
