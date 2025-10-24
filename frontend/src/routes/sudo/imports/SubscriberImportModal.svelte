<script lang="ts">
    import {IconMessage, LoadButton, Modal, SplitControl} from "@hyvor/design/components";
    import type {ImportingSubscriber, SubscriberImport} from "../types";
    import ImportingSubscriberRow from "./ImportingSubscriberRow.svelte";
    import {onMount} from "svelte";

    interface Props {
        show: boolean;
        subscriberImport: SubscriberImport;
        onApprove: (subscriberImport: SubscriberImport) => void;
    }

    let {show = $bindable(), subscriberImport, onApprove}: Props = $props();

    let loading = $state(true);
    let hasMore = $state(true);
    let loadingMore = $state(false);

    let importingSubscribers: ImportingSubscriber[] = $state([]);

    function load(more = false) {
        setTimeout(() => {
            loading = false;
        }, 500);
    }

    onMount(() => {
        load();
    })
</script>

<Modal
        bind:show
        {loading}
        title="Subscriber Import: {subscriberImport.newsletter_subdomain}"
        size="large"
>
    <div class="content-wrap">
        <SplitControl label="Columns">
            {subscriberImport.columns}
        </SplitControl>


        {#if importingSubscribers.length === 0}
            <IconMessage empty message="No subscribers found in the import file"/>
        {:else}
            <div class="list">
                {#each importingSubscribers as importingSubscriber (importingSubscriber.email)}
                    <ImportingSubscriberRow {importingSubscriber}/>
                {/each}

                <LoadButton
                        text="Load More"
                        loading={loadingMore}
                        show={hasMore}
                        on:click={() => load(true)}
                />
            </div>
        {/if}
    </div>
</Modal>

<style>
    .content-wrap {
        max-height: 80vh;
        overflow-y: auto;
    }

    .list {
        flex: 1;
        overflow: auto;
        padding: 20px 30px;
    }
</style>