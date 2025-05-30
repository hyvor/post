<script lang="ts">
	import { onMount } from 'svelte';
	import {
		Loader,
		Button,
		IconButton,
		SplitControl,
		TextInput,
		toast,
		confirm,
		Dropdown,
		ActionList,
		ActionListItem
	} from '@hyvor/design/components';
	import {
		getSendingProfilees,
		createSendingProfile,
		updateSendingProfile,
		deleteSendingProfile
	} from '../../../lib/actions/sendingProfileActions';
	import type { SendingProfile } from '../../../types';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';

	interface Props {
		updateContent?: (() => void) | null;
	}

	let { updateContent = null }: Props = $props();

	let loading = $state(true);
	let sendingProfilees: SendingProfile[] = $state([]);
	let error: string | null = null;

	let newAddress = $state('');
	let addError: string | null = null;
	let addLoading = $state(false);

	function load() {
		loading = true;
		getSendingProfilees()
			.then((data) => {
				sendingProfilees = data;
			})
			.catch((e) => {
				error = e.message;
			})
			.finally(() => {
				loading = false;
			});
	}

	function addSendingProfile() {
		if (!newAddress.trim()) {
			addError = 'Address is required.';
			return;
		}
		addLoading = true;
		createSendingProfile(newAddress.trim())
			.then(() => {
				load();
				toast.success('Sending address added');
				updateContent?.();
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				addLoading = false;
			});
	}

	async function removeSendingaddress(id: number) {
		const confirmation = await confirm({
			title: 'Delete Sending address',
			content: 'Are you sure you want to delete this sending address?',
			confirmText: 'Delete',
			cancelText: 'Cancel',
			danger: true
		});

		if (!confirmation) return;
		deleteSendingProfile(id)
			.then(() => {
				load();
				toast.success('Sending address deleted');
				updateContent?.();
			})
			.catch((e) => {
				toast.error(e.message);
			});
	}

	function setDefault(id: number) {
		updateSendingProfile(id, sendingProfilees.find((a) => a.id === id)?.email || '', true)
			.then(() => {
				load();
				toast.success('Default sending address updated');
				updateContent?.();
			})
			.catch((e: any) => {
				toast.error(e.message);
			});
	}

	onMount(load);
</script>

<SplitControl label="Add sending address">
	<div class="add-sending-address">
		<TextInput
			type="address"
			placeholder="Enter new sending address"
			bind:value={newAddress}
			on:keydown={(e) => {
				if (e.key === 'Enter') addSendingProfile();
			}}
			disabled={addLoading}
			block
		/>
		<Button on:click={addSendingProfile} disabled={addLoading || !newAddress.trim()}>Add</Button
		>
	</div>
</SplitControl>
{#if loading}
	<Loader />
{:else if sendingProfilees.length > 0}
	<SplitControl label="Sending address available">
		<div class="sending-address-list">
			{#each sendingProfilees as address}
				<div class="sending-address-item">
					<span class="sending-address-label">
						{address.email}
						{#if address.is_default}
							<span class="default-badge">Default</span>
						{/if}
					</span>
					<span class="sending-address-actions">
						<Dropdown width={200}>
							{#snippet trigger()}
								<IconButton size="small" color="input">
									<IconCaretDown size={12} />
								</IconButton>
							{/snippet}
							{#snippet content()}
								<ActionList>
									{#if !address.is_default}
										<ActionListItem on:click={() => setDefault(address.id)}>
											Set as default
										</ActionListItem>
									{/if}
									<ActionListItem
										color="red"
										on:click={() => removeSendingaddress(address.id)}
									>
										Delete
									</ActionListItem>
								</ActionList>
							{/snippet}
						</Dropdown>
					</span>
				</div>
			{/each}
		</div>
	</SplitControl>
{/if}

<style>
	.add-sending-address {
		display: flex;
		gap: 10px;
		margin-bottom: 20px;
	}
	.sending-address-list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}
	.sending-address-item {
		display: flex;
		align-items: center;
		gap: 8px;
		justify-content: space-between;
	}
	.sending-address-label {
		flex: 1 1 auto;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	.sending-address-actions {
		display: flex;
		gap: 8px;
		flex-shrink: 0;
	}
	.default-badge {
		font-size: 12px;
		color: var(--text-light);
		margin-left: 8px;
	}
</style>
