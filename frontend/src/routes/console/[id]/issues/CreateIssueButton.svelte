<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
	import { goto } from '$app/navigation';
	import { issueStore } from '../../lib/stores/projectStore';
	import { createIssueDraft } from '../../lib/actions/issueActions';
	import { consoleUrlWithProject } from '../../lib/consoleUrl';
	import type { Component } from 'svelte';

	interface Props {
		text: string;
		size?: 'small' | 'medium' | 'large';
		icon: Component;
	}

	let { text, size = 'medium', icon }: Props = $props();

	function create() {
		if (loading) return;

		loading = true;

		setTimeout(() => {
			loading = false;
		}, 2000);

		createIssueDraft()
			.then((res) => {
				issueStore.update((prev) => [res, ...prev]);
				goto(consoleUrlWithProject(`/issues/${res.id}`));
			})
			.catch((e) => {
				toast.error(e.message);
			});
	}

	let loading = $state(false);
</script>

<Button on:click={create} {size}>
	{text}
	{#snippet action()}
		{#if loading}
			<Loader size={14} invert />
		{:else}
			{@const SvelteComponent = icon}
			<SvelteComponent size={14} />
		{/if}
	{/snippet}
</Button>
