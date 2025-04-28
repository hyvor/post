<script lang="ts">
	import { IconButton, Tag, Tooltip, confirm, toast } from '@hyvor/design/components';
	import { deleteDomain, verifyDomain } from '../lib/actions/domainActions';
	import type { Domain } from '../types';
	import IconTrash from '@hyvor/icons/IconTrash';
    import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
    import IconDatabase from '@hyvor/icons/IconDatabase';
    import DnsRecordsModal from './DnsRecordsModal.svelte';
    import { onMount, onDestroy } from 'svelte';

	export let domain: Domain;
	export let onDelete: () => void;

	let loading = false;
	let showDnsRecords = false;
	let verificationInterval: number;

	function getVerificationStatusColor(verified: boolean) {
		return verified ? 'green' : 'red';
	}

	onMount(() => {
		if (!domain.verified_in_ses) {
			verificationInterval = window.setInterval(() => {
				if (!loading) {
					handleVerify();
				}
			}, 5 * 60 * 1000); // 5 minutes
		}
	});

	onDestroy(() => {
		if (verificationInterval) {
			clearInterval(verificationInterval);
		}
	});

	async function handleDelete() {
		const confirmation = await confirm({
			title: 'Delete Domain',
			content: 'Are you sure you want to delete this domain?',
			confirmText: 'Delete',
			cancelText: 'Cancel',
			danger: true
		});

		if (!confirmation) return;

		confirmation.loading();
		loading = true;

		deleteDomain(domain.id)
			.then(() => {
				toast.success('Domain deleted successfully');
				onDelete();
			})
			.catch((error: any) => {
				toast.error(error?.message || 'Failed to delete domain');
			})
			.finally(() => {
				loading = false;
				confirmation.close();
			});
	}

	function handleVerify() {
		loading = true;
		verifyDomain(domain.id)
			.then(() => {
				toast.success('Domain verification started');
			})
			.catch((error: any) => {
				toast.error(error?.message || 'Failed to verify domain');
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

<div class="domain-row">
	<div class="domain-info">
		<div class="domain-name">{domain.domain}</div>
		<div class="domain-status">
			<Tag size="small" color={getVerificationStatusColor(domain.verified_in_ses)}>
				{domain.verified_in_ses ? 'Verified' : 'Not Verified'}
			</Tag>
		</div>
		{#if !domain.verified_in_ses}
			<div class="dns-records">
				<div class="dns-record">
					<div class="label">DKIM Public Key:</div>
					<pre>{domain.dkim_public_key}</pre>
				</div>
				<div class="dns-record">
					<div class="label">DKIM TXT Name:</div>
					<pre>{domain.dkim_txt_name}</pre>
				</div>
				<div class="dns-record">
					<div class="label">DKIM TXT Value:</div>
					<pre>{domain.dkim_txt_value}</pre>
				</div>
			</div>
		{/if}
	</div>
	<div class="domain-actions">
		{#if domain.verified_in_ses}
			<Tooltip text="View DNS Records">
				<IconButton
					size="small"
					color="input"
					on:click={() => showDnsRecords = true}
					{loading}
				>
					<IconDatabase size={12} />
				</IconButton>
			</Tooltip>
		{/if}
		{#if !domain.verified_in_ses}
			<Tooltip text="Verify Domain">
				<IconButton
					size="small"
					color="input"
					on:click={handleVerify}
					{loading}
				>
					<IconArrowClockwise size={12} />
				</IconButton>
			</Tooltip>
		{/if}
		<Tooltip text="Delete Domain">
			<IconButton
				size="small"
				color="input"
				on:click={handleDelete}
				{loading}
			>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</div>

<DnsRecordsModal {domain} bind:show={showDnsRecords} />

<style>
	.domain-row {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		padding: 10px 15px;
		border-bottom: 1px solid var(--hds-color-border);
	}

	.domain-info {
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.domain-name {
		font-weight: 500;
	}

	.domain-status {
		display: flex;
		gap: 5px;
	}

	.domain-actions {
		display: flex;
		gap: 5px;
	}

	.dns-records {
		margin-top: 10px;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.dns-record {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	.dns-record .label {
		font-size: 12px;
		color: var(--hds-color-text-light);
	}

	.dns-record pre {
		background-color: var(--hds-color-background);
		padding: 8px;
		border-radius: var(--hds-border-radius);
		white-space: pre-wrap;
		word-break: break-all;
		font-family: monospace;
		font-size: 12px;
		margin: 0;
	}
</style> 