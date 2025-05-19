<script lang="ts">
import { onMount } from 'svelte';
import { Loader, Button, IconButton, SplitControl, TextInput, Modal, FormControl, Validation, toast, IconMessage, confirm } from '@hyvor/design/components';
import { getSendingEmails, createSendingEmail, updateSendingEmail, deleteSendingEmail } from '../../../lib/actions/sendingEmailActions';
import type { SendingEmail } from '../../../types';
import IconTrash from '@hyvor/icons/IconTrash';

export let updateContent: (() => void) | null = null;

let loading = true;
let sendingEmails: SendingEmail[] = [];
let error: string | null = null;

let newEmail = '';
let addError: string | null = null;
let addLoading = false;


function load() {
    loading = true;
    getSendingEmails()
        .then((data) => {
            sendingEmails = data;
        })
        .catch((e) => {
            error = e.message;
        })
        .finally(() => {
            loading = false;
        });
}

function addSendingEmail() {
    if (!newEmail.trim()) {
        addError = 'Email is required.';
        return;
    }
    addLoading = true;
    createSendingEmail(newEmail.trim())
        .then(() => {
            load();
            toast.success('Sending email added');
            updateContent?.();
        })
        .catch((e) => {
            toast.error(e.message);
        })
        .finally(() => {
            addLoading = false;
        });
}

async function removeSendingEmail(id: number) {
    const confirmation = await confirm({
				title: 'Delete Sending Email',
				content: 'Are you sure you want to delete this sending email?',
				confirmText: 'Delete',
				cancelText: 'Cancel',
				danger: true
			});

			if (!confirmation) return;
    deleteSendingEmail(id)
        .then(() => {
            load();
            toast.success('Sending email deleted');
            updateContent?.();
        })
        .catch((e) => {
            toast.error(e.message);
        });
}

onMount(load);

</script>

<SplitControl label="Add sending Email">
    <div class="add-sending-email">
        <TextInput
            type="email"
            placeholder="Enter new sending email"
            bind:value={newEmail}
            on:keydown={(e) => { if (e.key === 'Enter') addSendingEmail(); }}
            disabled={addLoading}
            block
        />
        <Button on:click={addSendingEmail} disabled={addLoading || !newEmail.trim()}>Add</Button>
    </div>
</SplitControl>
{#if loading}
    <Loader />
{:else if sendingEmails.length > 0}
    <SplitControl label="Sending Email available">
        <div class="sending-email-list">
            {#each sendingEmails as email}
                <div class="sending-email-item">
                    <span class="sending-email-label">{email.email}</span>
                    <span class="sending-email-actions">
                        <IconButton color="red" variant="fill-light" size="small" on:click={() => removeSendingEmail(email.id)}>
                            <IconTrash size={12} />
                        </IconButton>
                    </span>
                </div>
            {/each}
        </div>
    </SplitControl>
{/if}

<style>
    .add-sending-email {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .sending-email-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .sending-email-item {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: space-between;
    }
    .sending-email-label {
        flex: 1 1 auto;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sending-email-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
</style>
