<script lang="ts">
	import { onMount } from 'svelte';
	import { LoadButton, Loader, toast, TextInput } from '@hyvor/design/components';
	import type { Issue, IssueSend, SendType } from '../../../../types';
	import { getIssueSends } from '../../../../lib/actions/issueActions';
	import SendsRow from './SendsRow.svelte';

	export let issue: Issue;
	export let search: string | null = null;
	export let sendType: SendType = 'all';

	let loading = true;
	let loadingMore = false;
	let hasMore = false;
	let sends: IssueSend[] = [];
	


	const SENDS_LIMIT = 50;

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);
		getIssueSends(issue.id, SENDS_LIMIT, more ? sends.length : 0, search, sendType)
			.then((res) => {
				sends = more ? [...sends, ...res] : res;
				hasMore = res.length === SENDS_LIMIT;
			})
			.catch((err) => {
				toast.error('Failed to load issue sends: ' + err.message);
			})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	$: {
		search;
		sendType;
		load();
	}
</script>

<div class="wrap">
	{#if loading}
		<Loader full padding={100} />
	{:else}
		{#if sends.length === 0}
			No sends found
		{:else}
			{#each sends as send}
				<SendsRow {send} />
			{/each}
			<LoadButton
				show={hasMore}
				loading={loadingMore}
				on:click={() => load(true)}
				text="Load more"
			/>
		{/if}
	{/if}
</div>

<style lang="scss">
	.wrap {
		margin: 10px;
	}

</style>