<script lang="ts">
	import { onMount, onDestroy } from 'svelte';
	import DomainRow from './DomainRow.svelte';
	import { getDomains } from '../lib/actions/domainActions';
	import type { Domain } from '../types';
	import { IconMessage } from '@hyvor/design/components';

	let domains: Domain[] = [];
	let loading = false;
	let isInitialLoad = true;
	let checkInterval: number;
	let currentInterval = 30 * 1000; // Start with 30 seconds
	const MAX_INTERVAL = 3 * 60 * 1000; // Max 3 minutes

	export function refreshDomains(refresh = false) {
		if (!refresh && isInitialLoad)
			loading = true;
		getDomains()
			.then((res) => {
				domains = res;
				// If all domains are verified, reset the interval
				if (!res.some(domain => !domain.verified_in_ses)) {
					currentInterval = 30 * 1000;
				}
			})
			.catch((error: any) => {
				console.error('Failed to load domains:', error);
			})
			.finally(() => {
				loading = false;
				isInitialLoad = false;
			});
	}

	function startVerificationChecks() {
		refreshDomains();

		checkInterval = window.setInterval(() => {
			// Only refresh if there are unverified domains
			if (domains.some(domain => !domain.verified_in_ses)) {
				refreshDomains();
				currentInterval = Math.min(currentInterval * 2, MAX_INTERVAL);
			}
		}, currentInterval);
	}

	onMount(() => {
		startVerificationChecks();
	});

	onDestroy(() => {
		if (checkInterval) {
			clearInterval(checkInterval);
		}
	});
</script>

<div class="domain-list">
	{#if loading}
		<div class="loading">Loading domains...</div>
	{:else if domains.length === 0}
		<IconMessage empty message={"No domain"}/>
	{:else}
		{#each domains as domain (domain.id)}
			<DomainRow {domain} onDelete={refreshDomains} />
		{/each}
	{/if}
</div>

<style>
	.domain-list {
		display: flex;
		flex-direction: column;
		background-color: var(--hds-color-background);
		border-radius: var(--hds-border-radius);
		border: 1px solid var(--hds-color-border);
	}

	.loading {
		padding: 20px;
		text-align: center;
		color: var(--hds-color-text-light);
	}
</style> 