<script lang="ts">
    import {IconMessage, LoadButton, Loader} from '@hyvor/design/components';
    import SubscriberRow from './SubscriberRow.svelte';
    import type {NewsletterSubscriberStatus, Subscriber} from '../../../types';
    import {getI18n} from '../../../lib/i18n';
    import {subscriberStore} from "../../../lib/stores/newsletterStore";

    interface Props {
        status: NewsletterSubscriberStatus | null;
        loading: boolean;
        loadingMore: boolean;
        hasMore: boolean;
        error: string | null;
        onLoadMore: () => void;
        onDelete: (ids: number[]) => void;
        onUpdate: (subscriber: Subscriber) => void;
    }

    let {status, loading, loadingMore, hasMore, error, onLoadMore, onDelete, onUpdate}: Props = $props();

    const I18n = getI18n();
</script>

{#if loading}
    <Loader full/>
{:else if error}
    <IconMessage error message={error}/>
{:else if $subscriberStore.length === 0}
    <IconMessage empty message={I18n.t('console.subscribers.emptyList')}/>
{:else}
    <div class="list">
        {#each $subscriberStore as subscriber (subscriber.id)}
            <SubscriberRow
                    {subscriber}
                    handleDelete={onDelete}
                    handleUpdate={onUpdate}
            />
        {/each}
        <LoadButton
                text={I18n.t('console.common.loadMore')}
                loading={loadingMore}
                show={hasMore}
                on:click={onLoadMore}
        />
    </div>
{/if}

<style>
    .list {
        flex: 1;
        overflow: auto;
        padding: 20px 30px;
    }
</style>
