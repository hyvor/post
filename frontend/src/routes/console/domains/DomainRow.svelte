<script lang="ts">
	import {
		Button,
		IconButton,
		Modal,
		SplitControl,
		Tag,
		Tooltip,
		confirm,
		toast
	} from '@hyvor/design/components';
	import { deleteDomain, verifyDomain } from '../lib/actions/domainActions';
	import type { Domain } from '../types';
	import DnsRecordsModal from './DnsRecordsModal.svelte';
	import IconTrash from '@hyvor/icons/IconTrash';
	import DomainStatusTag from './DomainStatusTag.svelte';
	import RelativeTime from '../@components/utils/RelativeTime.svelte';

	export let domain: Domain;
	export let onDelete: () => void;

	let loading = false;
	let showDnsRecords = false;
	let showVerificationDebug = false;
	let verificationDebug: null | Record<string, string> = null;

	function getVerificationStatusColor(verified: boolean) {
		return verified ? 'green' : 'orange';
	}

	async function handleDelete() {
		const confirmation = await confirm({
			title: 'Delete Domain',
			content: 'Are you sure you want to delete this domain?',
			confirmText: 'Delete',
			cancelText: 'Cancel',
			danger: true,
			autoClose: false
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
				domain = res.domain;
				if (res.domain.relay_status !== 'active') {
					verificationDebug = res.data.debug;
					showVerificationDebug = true;
				} else {
					toast.success('Verification Successful');
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
		<div class="top">
			<div class="domain-name">{domain.domain}</div>
			<DomainStatusTag status={domain.relay_status} bind:showVerificationDebug />
		</div>
		<div class="domain-meta">
			<!--            <div class="error">-->
			{#if domain.relay_status === 'pending' || domain.relay_status === 'warning'}
				{#if domain.relay_last_checked_at}
					<div>
						Last Checked:
						<RelativeTime unix={domain.relay_last_checked_at} />
					</div>
				{/if}
				{#if domain.relay_error_message}
					<div>Error: {domain.relay_error_message}</div>
				{/if}
			{/if}
			<!--            </div>-->
		</div>
	</div>
	<div class="domain-actions">
		<Button size="small" color="input" on:click={() => (showDnsRecords = true)} {loading}>
			View DNS Records
		</Button>
		{#if domain.relay_status === 'pending'}
			<Button size="small" color="input" on:click={handleVerify} {loading}>
				Verify Domain
			</Button>
		{/if}
		<IconButton size="small" color="red" variant="fill-light" on:click={handleDelete}>
			<IconTrash size={12} />
		</IconButton>
	</div>
</div>

{#if showVerificationDebug}
	<Modal
		title="Verification Status"
		footer={{ confirm: false, cancel: { text: 'Close' } }}
		on:cancel={() => (showVerificationDebug = false)}
		closeOnOutsideClick={false}
		closeOnEscape={false}
		show={true}
	>
		<p>
			{#if domain.relay_status === 'suspended'}
				Domain <strong>{domain.domain}</strong> is suspended. Please contact support for more
				information.
			{:else if domain.relay_status === 'warning'}
				Domain <strong>{domain.domain}</strong> is put on warning status. Please check the DNS
				records and ensure they are correct. Please contact support for more information.
			{:else if domain.relay_status === 'pending'}
				Domain verification for <strong>{domain.domain}</strong> is in pending status. Please
				note that it may take up to 72 hours for the changes to take effect.
			{:else}
				Domain verification for <strong>{domain.domain}</strong> is in active status.
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
		padding: 10px 0;
		border-bottom: 1px solid var(--hds-color-border);
	}

	.domain-info {
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.top {
		display: flex;
		gap: 10px;
	}

	.domain-name {
		font-weight: 500;
	}

	.domain-meta {
		font-size: 12px;
		color: var(--text-light);
	}

	.domain-actions {
		display: flex;
		gap: 5px;
	}

	pre {
		white-space: pre-wrap;
	}
</style>
