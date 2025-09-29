<script lang="ts">
	import Nav from './Nav.svelte';
	import { onMount } from 'svelte';
	import sudoApi from './lib/sudoApi';
	import type { SudoConfig } from './types';
	import { configStore } from './lib/stores/sudoStore';
	import { Loader, toast } from '@hyvor/design/components';
	import { onNavigate } from '$app/navigation';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		config: SudoConfig;
	}

	let isLoading = $state(true);

	onMount(() => {
		sudoApi
			.get<InitResponse>({
				endpoint: '/init'
			})
			.then((res) => {
				configStore.set(res.config);
				isLoading = false;
			})
			.catch((err) => {
				toast.error(err.message);
			});
	});

	onNavigate((nav) => {
		if (window.parent) {
			window.parent.postMessage(
				{
					type: 'navigate',
					to: nav.to?.url.pathname
				},
				'*'
			);
		}
	});
</script>

<div class="main-inner">
	{#if isLoading}
		<Loader full />
	{:else}
		<Nav />
		<div class="content hds-box">
			{@render children?.()}
		</div>
	{/if}
</div>

<style>
	.main-inner {
		display: flex;
		flex: 1;
		width: 100%;
		height: 100vh;
		min-height: 0;
	}
	.content {
		display: flex;
		flex-direction: column;
		margin: 15px;
		padding: 15px;
		flex: 1;
		width: 100%;
		height: calc(100vh - 30px);
		min-width: 0;
	}
</style>
