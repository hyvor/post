<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import Stat from './Stat.svelte';
	import { newsletterStatsStore, newsletterStore } from '../../lib/stores/newsletterStore';

	let loading = false;
</script>

<div class="wrap">
	{#if loading}
		<Loader block padding={25} />
	{:else}
		<div class="stats">
			<Stat
				title="Subscribers"
				counts={$newsletterStatsStore.subscribers}
				href={`/console/${$newsletterStore.id}/subscribers`}
			/>
			<Stat
				title="Issues"
				counts={$newsletterStatsStore.issues}
				href={`/console/${$newsletterStore.id}/issues`}
			/>
			<Stat
				title="Lists"
				counts={$newsletterStatsStore.lists}
				href={`/console/${$newsletterStore.id}`}
			/>
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
