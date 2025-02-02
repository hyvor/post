<script lang="ts">
	import { Button, Loader } from '@hyvor/design/components';
	import type { Stats } from '../../../types';
	import { consoleUrl } from '../../../lib/consoleUrl';
	import Stat from './Stat.svelte';


    // TODO: Remove fake data
	let stats: Stats = {
        subscribers: { total: 300, last_30d: 100 },
        issues: { total: 50, last_30d: 5 },
        lists: { total: 12, last_30d: 2 },
    };
	let loading = false;
    
</script>

<div class="wrap">
	{#if loading}
		<Loader block padding={25} />
	{:else}
		<div class="stats">
			<Stat
				title="Subscribers"
				counts={stats.subscribers}
				href={consoleUrl('/subscribers')}
			/>
			<Stat title="Issues" 
                counts={stats.issues} 
                href={consoleUrl('/issues')} 
            />
			<Stat
				title="Lists"
				counts={stats.lists}
				href={consoleUrl('/lists')}
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
