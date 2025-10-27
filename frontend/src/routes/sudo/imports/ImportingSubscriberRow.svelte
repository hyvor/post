<script lang="ts">
	import { CodeBlock } from "@hyvor/design/components";
    import FriendlyDate from "../../console/@components/utils/FriendlyDate.svelte";
import type {ImportingSubscriber} from "../types";

    interface Props {
        importingSubscriber: ImportingSubscriber;
    }

    let {importingSubscriber}: Props = $props();
</script>

<div class="row">
    <div class="email">
        {importingSubscriber.email}
    </div>
    <div class="lists">
        {importingSubscriber.lists.join(', ')}
    </div>
    <div class="status">
        {importingSubscriber.status}
    </div>
    <div class="subscribed-at">
        {#if importingSubscriber.status === 'subscribed' && importingSubscriber.subscribed_at !== null}
            <FriendlyDate time={importingSubscriber.subscribed_at} />
        {:else}
            N/A
        {/if}
    </div>
    <div class="subscribe-ip">
        {#if importingSubscriber.status === 'subscribed' && importingSubscriber.subscribe_ip !== null}
            {importingSubscriber.subscribe_ip}
        {:else}
            N/A
        {/if}
    </div>
    <div class="metadata">
        {#if importingSubscriber.metadata}
            <CodeBlock code={JSON.stringify(importingSubscriber.metadata, null, 2)} language="json" />
        {:else}
            N/A
        {/if}
    </div>
</div>

<style>
        .row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 15px;
        background-color: var(--bg-light);
        border-radius: var(--box-radius);
        text-align: left;
        width: 100%;
    }

    .row:hover {
        background: var(--hover);
    }
    .email, .lists, .metadata {
        width: calc((100% - 420px)/3);
    }
    .status, .subscribed-at, .subscribe-ip {
        width: 140px;
    }
    .status {
        text-transform: capitalize;
    }
    .metadata {
        :global(.language-json) {
            margin-top: 5px !important;
            margin-bottom: 5px !important;
            padding: 15px 20px !important; 
        }
    }
</style>