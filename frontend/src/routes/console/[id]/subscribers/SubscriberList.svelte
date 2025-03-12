<script lang="ts">
	import { IconMessage, LoadButton, Loader } from '@hyvor/design/components';
	import SubscriberRow from './SubscriberRow.svelte';
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import type { NewsletterSubscriberStatus, Subscriber } from '../../types';
	import { getSubscribers } from '../../lib/actions/subscriberActions';

	export let status: NewsletterSubscriberStatus;
	export let key: number; // just for forcing re-render

	let loading = true;
	let hasMore = true;
	let loadingMore = false;
	let error: null | string = null;

	const SUBSCRIBERS_PER_PAGE = 25;

	let subscribers: Subscriber[] = [];

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getSubscribers(status, SUBSCRIBERS_PER_PAGE, more ? subscribers.length : 0)
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

	function onDelete(id: number) {
		subscribers = subscribers.filter((s) => s.id !== id);
	}

	function onUpdate(e: CustomEvent<Subscriber>) {
		subscribers = subscribers.map((s) => (s.id === e.detail.id ? e.detail : s));
	}

	$: {
		status, key, load();
	}
</script>

{#if loading}
	<Loader full />
{:else if error}
	<IconMessage error message={error} />
{:else if subscribers.length === 0}
	<IconMessage empty message={"No result"}/>
{:else}
	<div class="list">
		{#each subscribers as subscriber (subscriber.id)}
			<SubscriberRow
				subscriber={subscriber}
				refreshList={() => key += 1}
			/>
		{/each}
	</div>
	<LoadButton
		text={"Load more"}
		loading={loadingMore}
		show={hasMore}
		on:click={() => load(true)}
	/>
{/if}
