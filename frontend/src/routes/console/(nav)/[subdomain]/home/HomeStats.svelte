<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import Stat from './Stat.svelte';
	import { newsletterStatsStore, newsletterStore } from '../../../lib/stores/newsletterStore';
	import { getI18n } from '../../../lib/i18n';

	const I18n = getI18n();

	let loading = false;
</script>

<div class="wrap">
	{#if loading}
		<Loader block padding={25} />
	{:else}
		<div class="stats">
			<Stat
				title={I18n.t('console.nav.subscribers')}
				counts={$newsletterStatsStore.subscribers}
				href={`/console/${$newsletterStore.subdomain}/subscribers`}
			/>
			<Stat
				title={I18n.t('console.nav.issues')}
				counts={$newsletterStatsStore.issues}
				href={`/console/${$newsletterStore.subdomain}/issues`}
			/>
			<Stat
				title={I18n.t('console.home.stats.bouncedRate')}
				counts={$newsletterStatsStore.bounced_rate}
				percent
			/>
			<Stat
				title={I18n.t('console.home.stats.complaintRate')}
				counts={$newsletterStatsStore.complained_rate}
				percent
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
