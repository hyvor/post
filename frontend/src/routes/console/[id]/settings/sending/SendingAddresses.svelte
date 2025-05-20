<script lang="ts">
import { onMount } from 'svelte';
import { Loader, Button, IconButton, SplitControl, TextInput, Modal, FormControl, Validation, toast, IconMessage, confirm } from '@hyvor/design/components';
import { getSendingAddresses, createSendingAddress, updateSendingAddress, deleteSendingAddress } from '../../../lib/actions/sendingAddressActions';
import type { SendingAddress } from '../../../types';
import IconTrash from '@hyvor/icons/IconTrash';

export let updateContent: (() => void) | null = null;

let loading = true;
let sendingAddresses: SendingAddress[] = [];
let error: string | null = null;

let newAddress = '';
let addError: string | null = null;
let addLoading = false;


function load() {
    loading = true;
    getSendingAddresses()
        .then((data) => {
            sendingAddresses = data;
        })
        .catch((e) => {
            error = e.message;
        })
        .finally(() => {
            loading = false;
        });
}

function addSendingAddress() {
    if (!newAddress.trim()) {
        addError = 'Address is required.';
        return;
    }
    addLoading = true;
    createSendingAddress(newAddress.trim())
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
    deleteSendingAddress(id)
        .then(() => {
            load();
            toast.success('Sending address deleted');
            updateContent?.();
        })
        .catch((e) => {
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
            on:keydown={(e) => { if (e.key === 'Enter') addSendingAddress(); }}
            disabled={addLoading}
            block
        />
        <Button on:click={addSendingAddress} disabled={addLoading || !newAddress.trim()}>Add</Button>
    </div>
</SplitControl>
{#if loading}
    <Loader />
{:else if sendingAddresses.length > 0}
    <SplitControl label="Sending address available">
        <div class="sending-address-list">
            {#each sendingAddresses as address}
                <div class="sending-address-item">
                    <span class="sending-address-label">{address.email}</span>
                    <span class="sending-address-actions">
                        <IconButton color="red" variant="fill-light" size="small" on:click={() => removeSendingaddress(address.id)}>
                            <IconTrash size={12} />
                        </IconButton>
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
</style>
