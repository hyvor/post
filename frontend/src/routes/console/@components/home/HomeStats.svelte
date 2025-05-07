<script lang="ts">
	import { Button, Loader } from '@hyvor/design/components';
	import type { ProjectStats } from '../../types';
	import Stat from './Stat.svelte';
	import { projectStatsStore, projectStore } from '../../lib/stores/projectStore';

	let loading = false;
</script>

<div class="wrap">
	{#if loading}
		<Loader block padding={25} />
	{:else}
		<div class="stats">
			<Stat title="Subscribers" counts={$projectStatsStore.subscribers} href={`/console/${$projectStore.id}/subscribers`} />
			<Stat title="Issues" counts={$projectStatsStore.issues} href={`/console/${$projectStore.id}/issues`} />
			<Stat title="Lists" counts={$projectStatsStore.lists} href={`/console/${$projectStore.id}`}/>
		</div>
	{/if}
</div>

<style>
	.stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 15px;
	}
	.wrap {
		padding: 25px 35px;
		border-bottom: 1px solid var(--border);
	}
</style>
