<script lang="ts">
    import {Tag} from '@hyvor/design/components';
    import type {IssueSend} from '../../../../../types';
    import RelativeTime from '../../../../../@components/utils/RelativeTime.svelte';
    import SendDetailsModal from './SendDetailsModal.svelte';

    export let send: IssueSend;

    let showModal = false;

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            showModal = true;
        }
    }
</script>

<button
        type="button"
        class="send-row"
        on:click={() => showModal = true}
        on:keydown={handleKeydown}
>
    <div class="email">
        {send.email}
    </div>
    <div class="time">
        {#if send.delivered_at}
            Delivered
            <RelativeTime unix={send.delivered_at}/>
        {:else if send.sent_at}
            Sent
            <RelativeTime unix={send.sent_at}/>
        {/if}
    </div>
    <div class="bad-tags tags">
        {#if send.bounced_at}
            <Tag size="small" color="orange">Bounced {send.hard_bounce ? '(Hard)' : '(Soft)'}</Tag>
        {/if}
        {#if send.unsubscribed_at}
            <Tag size="small" color="red">Unsubscribed</Tag>
        {/if}
        {#if send.complained_at}
            <Tag size="small" color="red">Complained</Tag>
        {/if}
        {#if send.failed_at}
            <!-- This should not happen -->
            <Tag size="small" color="red">Failed</Tag>
        {/if}
    </div>
</button>

<SendDetailsModal {send} bind:show={showModal}/>

<style>
    .send-row {
        display: flex;
        align-items: center;
        padding: 10px 10px;
        cursor: pointer;
        width: 100%;
        background: none;
        border: none;
        text-align: left;
        margin-top: 10px;
    }

    .send-row:hover {
        background: var(--hover);
        border-radius: 10px;
    }

    .time {
        flex: 1;
    }

    .email {
        flex: 1;
    }

    .tags {
        flex: 1;
    }
</style>
