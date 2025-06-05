<script lang="ts">
	import { IconMessage, LoadButton, Loader } from '@hyvor/design/components';
	import SubscriberRow from './SubscriberRow.svelte';
	import type { NewsletterSubscriberStatus, Subscriber } from '../../types';
	import { getI18n } from '../../lib/i18n';

	interface Props {
		status: NewsletterSubscriberStatus | null;
		key: number;
		subscribers: Subscriber[];
		loading: boolean;
		loadingMore: boolean;
		hasMore: boolean;
		error: string | null;
		onLoadMore: () => void;
		onDelete: (ids: number[]) => void;
	}

	let { status, key, subscribers, loading, loadingMore, hasMore, error, onLoadMore, onDelete }: Props = $props();

	const I18n = getI18n();
</script>

{#if loading}
	<Loader full />
{:else if error}
	<IconMessage error message={error} />
{:else if subscribers.length === 0}
	<IconMessage empty message={I18n.t('console.subscribers.emptyList')} />
{:else}
	<div class="list">
		{#each subscribers as subscriber (subscriber.id)}
			<SubscriberRow 
				{subscriber} 
				refreshList={() => onDelete([subscriber.id])} 
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
