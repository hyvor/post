<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Newsletter } from '../types';
	import type { Organization } from '../lib/actions/newsletterActions';
	import { flagByCountryCode } from '$lib/helpers/countryCode';
	import { configStore } from '../lib/stores/sudoStore';
	import RelativeTime from '../../console/@components/utils/RelativeTime.svelte';

	interface Props {
		newsletter: Newsletter;
		org?: Organization;
		handleSelect: (newsletter: Newsletter) => void;
	}

	let { newsletter, org, handleSelect }: Props = $props();
</script>

<button class="row" onclick={() => handleSelect(newsletter)}>
	<div class="name">
		{newsletter.name}
	</div>

	<div class="subdomain">
		{newsletter.subdomain}
	</div>

	<div class="org">
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
				>
					Org &rarr;
				</Button>
			</div>
		{:else}
			<span class="no-org">-</span>
		{/if}
	</div>

	<div class="created-at">
		<RelativeTime unix={newsletter.created_at} />
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

	.name {
		width: 25%;
	}

	.subdomain {
		width: 20%;
	}

	.org {
		width: 30%;
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

	.created-at {
		width: 25%;
	}
</style>
