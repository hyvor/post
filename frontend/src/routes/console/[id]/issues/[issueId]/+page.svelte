<script lang="ts">
    import {page} from '$app/state';
    import {onMount} from 'svelte';
    import {Button, IconMessage, Loader, confirm, toast} from '@hyvor/design/components';
    import IssueStatusTag from '../IssueStatusTag.svelte';
    import {goto} from '$app/navigation';
    import {consoleUrlWithNewsletter} from '../../../lib/consoleUrl';
    import {issueStore} from '../../../lib/stores/newsletterStore';
    import type {Issue} from '../../../types';
    import IconCaretLeft from '@hyvor/icons/IconCaretLeft';
    import IconTrash from '@hyvor/icons/IconTrash';
    import {deleteIssue, getIssue} from '../../../lib/actions/issueActions';
    import DraftIssue from './draft/DraftIssue.svelte';
    import IssueSending from './sending/IssueSending.svelte';
    import SentIssue from './sent/SentIssue.svelte';
    import SingleBox from '../../../@components/content/SingleBox.svelte';

    const id = Number(page.params.issueId);

    let issue: Issue | undefined = $state(undefined);
    let loading = $state(true);
    let error: null | string = $state(null);

    async function onDelete() {
        const confirmed = await confirm({
            title: 'Delete issue',
            content: 'Are you sure you want to delete this issue?',
            confirmText: 'Yes, delete',
            cancelText: 'Cancel',
            danger: true
        });

        if (confirmed) {
            confirmed.loading();

            deleteIssue(id)
                .then(() => {
                    $issueStore = $issueStore.filter((i) => i.id !== id);
                    toast.success('Issue deleted successfully');
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
            <!-- <div class="top">
                <div class="left">
                    <Button
                        size="small"
                        color="input"
                        as="a"
                        href={consoleUrlWithNewsletter('/issues')}
                    >
                        {#snippet start()}
                            <IconCaretLeft size={12} />
                        {/snippet}
                        All issues
                    </Button>
                </div>
                <div>
                    {#if issue.status === 'draft'}
                        <Button variant="fill-light" color="red" on:click={onDelete}>
                            Delete
                            {#snippet end()}
                                <IconTrash size={12} />
                            {/snippet}
                        </Button>
                    {/if}
                    <IssueStatusTag status={issue.status} size="large" />
                </div>
            </div> -->
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

    .top {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 0 15px;
    }

    .left {
        flex: 1;
    }

    @media (max-width: 992px) {
        .wrap {
            padding: 15px 0;
        }
    }
</style>
