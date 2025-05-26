<script lang="ts">
	import { IconMessage, LoadButton, Loader } from '@hyvor/design/components';
	import SubscriberRow from './SubscriberRow.svelte';
	import type { NewsletterSubscriberStatus, Subscriber } from '../../types';
	import { getSubscribers } from '../../lib/actions/subscriberActions';
	import { getI18n } from '../../lib/i18n';

	interface Props {
		status: NewsletterSubscriberStatus | null;
		list_id: number | null;
		search?: string | null;
		key: number; // just for forcing re-render
	}

	let { status, list_id, search = null, key = $bindable() }: Props = $props();

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	let error: null | string = $state(null);

	const SUBSCRIBERS_PER_PAGE = 25;

	let subscribers: Subscriber[] = $state([]);

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getSubscribers(status, list_id, search, SUBSCRIBERS_PER_PAGE, more ? subscribers.length : 0)
			.then((data) => {
				subscribers = more ? [...subscribers, ...data] : data;
				hasMore = data.length === SUBSCRIBERS_PER_PAGE;
			})
			.catch((e) => {
				error = e.message;
			})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	const I18n = getI18n();

	$effect(() => {
		status;
		key;
		search;
		list_id;

		load();
	});
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
			<SubscriberRow {subscriber} refreshList={() => (key += 1)} />
		{/each}
		<LoadButton
			text={I18n.t('console.common.loadMore')}
			loading={loadingMore}
			show={hasMore}
			on:click={() => load(true)}
		/>
	</div>
{/if}

<style>
	.list {
		flex: 1;
		overflow: auto;
		padding-bottom: 20px;
	}
</style>
