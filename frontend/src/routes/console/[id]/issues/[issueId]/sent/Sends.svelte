<script lang="ts">
	import { onMount } from 'svelte';
	import { LoadButton, Loader, toast, TextInput, IconButton } from '@hyvor/design/components';
	import type { Issue, IssueSend, SendStatus } from '../../../../types';
	import { getIssueSends } from '../../../../lib/actions/issueActions';
	import SendsRow from './SendsRow.svelte';
	import Selector from '../../../@components/content/Selector.svelte';
	import IconX from '@hyvor/icons/IconX';

	export let issue: Issue;

	let loading = true;
	let loadingMore = false;
	let hasMore = false;
	let sends: IssueSend[] = [];
	
	let search = '';


	const SENDS_LIMIT = 50;

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);
		getIssueSends(issue.id, SENDS_LIMIT, more ? sends.length : 0, search)
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

	function searchFromEmail(e: KeyboardEvent) {
		load();
	}

	onMount(load);
</script>

<div class="wrap">
	{#if loading}
		<Loader full padding={100} />
	{:else}
		<TextInput
			bind:value={search}
			placeholder="Search"
			style="width:250px"
			on:keydown={searchFromEmail}
			size="small"
		>
		</TextInput>
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
</div>
