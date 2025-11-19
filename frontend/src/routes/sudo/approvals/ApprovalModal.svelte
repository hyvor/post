<script lang="ts">
	import {
		Divider,
		Modal,
		SplitControl,
		Textarea,
		TextInput,
		Button
	} from '@hyvor/design/components';
	import type { Approval } from '../types';

	interface Props {
		show: boolean;
		approval: Approval;
		onApprove: (approval: Approval) => void;
		onReject: (approval: Approval) => void;
		onMarkAsPending: (approval: Approval) => void;
	}

	let { show = $bindable(), approval, onApprove, onReject, onMarkAsPending }: Props = $props();
</script>

<Modal bind:show title="Approval Details" size="large">
	<div class="content">
		<SplitControl label="Company name">
			<TextInput bind:value={approval.company_name} disabled block />
		</SplitControl>

		<SplitControl label="Country">
			<TextInput bind:value={approval.country} disabled block />
		</SplitControl>

		<SplitControl label="Website">
			<TextInput type="url" bind:value={approval.website} disabled block />
		</SplitControl>

		<SplitControl label="Social Links">
			<Textarea bind:value={approval.social_links} disabled block />
		</SplitControl>

		<SplitControl label="Type of Content">
			<TextInput bind:value={approval.type_of_content} disabled block />
		</SplitControl>

		<SplitControl label="Frequency">
			<TextInput bind:value={approval.frequency} disabled block />
		</SplitControl>

		<SplitControl label="Existing List">
			<Textarea bind:value={approval.existing_list} disabled block />
		</SplitControl>

		<SplitControl label="Sample">
			<TextInput bind:value={approval.sample} disabled block />
		</SplitControl>

		<SplitControl label="Why Post?">
			<Textarea bind:value={approval.why_post} disabled block />
		</SplitControl>

		<Divider color={'var(--accent-light)'} margin={10} />

		<SplitControl label="Public Note">
			<Textarea bind:value={approval.public_note} block />
		</SplitControl>

		<SplitControl label="Private Note">
			<Textarea bind:value={approval.private_note} block />
		</SplitControl>
	</div>

	{#snippet footer()}
		<div class="footer-buttons">
			<Button
				color="input"
				disabled={approval.status === 'pending'}
				on:click={() => onMarkAsPending(approval)}
			>
				Mark as pending
			</Button>
			<Button
				color="red"
				disabled={approval.status === 'rejected'}
				on:click={() => onReject(approval)}
			>
				Reject
			</Button>
			<Button disabled={approval.status === 'approved'} on:click={() => onApprove(approval)}>
				Approve
			</Button>
		</div>
	{/snippet}
</Modal>

<style>
	.content {
		height: 70vh;
		overflow-y: auto;
	}

	.footer-buttons {
		display: flex;
		gap: 6px;
	}
</style>
