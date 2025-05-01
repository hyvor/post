<script lang="ts">
	import { Button, IconButton, Modal, SplitControl, Tag, Tooltip, confirm, toast } from '@hyvor/design/components';
	import { deleteDomain, verifyDomain } from '../lib/actions/domainActions';
	import type { Domain } from '../types';
    import DnsRecordsModal from './DnsRecordsModal.svelte';
	import IconTrash from '@hyvor/icons/IconTrash';

	export let domain: Domain;
	export let onDelete: () => void;

	let loading = false;
	let showDnsRecords = false;
	let showVerificationDebug = false;
	let verificationDebug: null | Record<string, string> = null;

	function getVerificationStatusColor(verified: boolean) {
		return verified ? 'green' : 'red';
	}

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
			.then((res) => {
				toast.success('Domain verification started');
				domain = res.domain;
				if (res.data.verified === false) {
					verificationDebug = res.data.debug;
				}
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
			<Tag 
				size="small" 
				color={getVerificationStatusColor(domain.verified_in_ses)}
				on:click={() => showVerificationDebug = true}
				interactive={true}
			>
				{domain.verified_in_ses ? 'Verified' : 'Not Verified'}
			</Tag>
		</div>
	</div>
	<div class="domain-actions">
		<Button
			size="small"
			color="input"
			on:click={() => showDnsRecords = true}
			{loading}
		>
			View DNS Records
		</Button>
		{#if !domain.verified_in_ses}
			<Button
				size="small"
				color="input"
				on:click={handleVerify}
				{loading}
			>
				Verify Domain
			</Button>
		{/if}
		<Tooltip
			text="Delete Domain"
		>
			<IconButton
			size="small"
			color="red"
			variant="fill-light"
			on:click={handleDelete}
		>
				<IconTrash />
			</IconButton>
		</Tooltip>
	</div>
</div>

{#if showVerificationDebug}
	<Modal
		title="Verification Status"
		footer={{ confirm: false, cancel: { text: 'Close' } }}
		on:cancel={() => (showVerificationDebug = false)}
		show={true}
	>
		<p>
			Domain verification for <strong>{domain.domain}</strong> is {domain.verified_in_ses ? 'verified' : 'not verified'}.
			{#if !domain.verified_in_ses}
				Please note that it may take up to 72 hours for the changes to take effect.
			{/if}
		</p>
		{#if verificationDebug}
			<SplitControl label="Debug Information" column>
				<pre>{JSON.stringify(verificationDebug, null, 2)}</pre>
			</SplitControl>
		{/if}
	</Modal>
{/if}

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
</style> 