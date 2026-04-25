<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Newsletter } from '../types';
	import type { NewsletterRowStats, Organization } from '../lib/actions/newsletterActions';
	import { flagByCountryCode } from '$lib/helpers/countryCode';
	import { configStore } from '../lib/stores/sudoStore';
	import RelativeTime from '../../console/@components/utils/RelativeTime.svelte';

	interface Props {
		newsletter: Newsletter;
		org?: Organization;
		stats?: NewsletterRowStats;
		handleSelect: (newsletter: Newsletter) => void;
	}

	let { newsletter, org, stats, handleSelect }: Props = $props();
</script>

<button class="row" onclick={() => handleSelect(newsletter)}>
	<div class="col-newsletter">
		<div class="nl-name">{newsletter.name}</div>
		<div class="nl-subdomain">{newsletter.subdomain}</div>
		<div class="nl-created">
			<RelativeTime unix={newsletter.created_at} />
		</div>
	</div>

	<div class="col-org">
		{#if org}
			<div class="org-name">
				{org.name}

				{#if org.billing_address?.country}
					<span title={org.billing_address?.country}>
						{flagByCountryCode(org.billing_address?.country)}
					</span>
				{/if}
			</div>
			<div class="org-email">
				{org.billing_email}
			</div>
			<div class="view-button">
				<Button
					as="a"
					href="{$configStore.hyvor.instance}/sudo/core/organizations/{org.id}"
					size="x-small"
					target="_blank"
					color="input"
					on:click={(e: Event) => e.stopPropagation()}
				>
					Org &rarr;
				</Button>
			</div>
		{:else}
			<span class="no-org">-</span>
		{/if}
	</div>

	<div class="col-stats">
		{#if stats}
			<div class="stat-line">Issues: {stats.issues_count}</div>
			<div class="stat-line">Subscribers: {stats.subscribers_count}</div>
		{:else}
			<span class="loading-stats">…</span>
		{/if}
	</div>
</button>

<style>
	.row {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 15px;
		background-color: var(--bg-light);
		border-radius: var(--box-radius);
		text-align: left;
		width: 100%;
	}

	.row:hover {
		background: var(--hover);
	}

	.col-newsletter {
		width: 35%;
	}

	.nl-name {
		font-weight: 500;
	}

	.nl-subdomain {
		font-size: 13px;
		color: var(--text-light);
		margin-top: 2px;
	}

	.nl-created {
		font-size: 12px;
		color: var(--text-light);
		margin-top: 4px;
	}

	.col-org {
		width: 35%;
	}

	.org-name {
		font-weight: 500;
	}

	.org-email {
		font-size: 13px;
		color: var(--text-light);
	}

	.view-button {
		margin-top: 4px;
	}

	.no-org {
		color: var(--text-light);
	}

	.col-stats {
		width: 30%;
	}

	.stat-line {
		font-size: 13px;
	}

	.loading-stats {
		color: var(--text-light);
	}
</style>
