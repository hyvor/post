<script lang="ts">
    import {page} from '$app/state';
    import {onMount} from 'svelte';
    import {IconMessage, Loader} from '@hyvor/design/components';
    import {currentIssueStore} from '../../../lib/stores/newsletterStore';
    import {getIssue} from '../../../lib/actions/issueActions';
    import DraftIssue from './draft/DraftIssue.svelte';
    import IssueSending from './sending/IssueSending.svelte';
    import SentIssue from './sent/SentIssue.svelte';
    import SingleBox from '../../../@components/content/SingleBox.svelte';
    import {draftSendableSubscribersCountStore} from './draft/draftStore';

    const id = Number(page.params.issueId);
    let loading = $state(true);
    let error: null | string = $state(null);

    function fetchIssue() {
        getIssue(id)
            .then((res) => {
                currentIssueStore.set(res);
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

    function onStatusChange() {
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
        {:else if $currentIssueStore}
            <div class="content">
                {#if $currentIssueStore.status === 'draft'}
                    <DraftIssue {onStatusChange}/>
                {:else if $currentIssueStore.status === 'sending'}
                    <IssueSending {onStatusChange}/>
                {:else if $currentIssueStore.status === 'sent' || $currentIssueStore.status === 'failed'}
                    <SentIssue/>
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
