<script lang="ts">
    import {Modal, SplitControl} from "@hyvor/design/components";
    import type {ImportingSubscriber, SubscriberImport} from "../types";
    import ImportingSubscriberRow from "./ImportingSubscriberRow.svelte";

    interface Props {
        show: boolean;
        subscriberImport: SubscriberImport;
        onApprove: (subscriberImport: SubscriberImport) => void;
    }

    let {show = $bindable(), subscriberImport, onApprove}: Props = $props();

    let importingSubscribers: ImportingSubscriber[] = $state([]);
</script>

<Modal
        bind:show
        title="Subscriber Import: {subscriberImport.newsletter_subdomain}"
        size="large"
>
    <SplitControl label="Column Mapping">
        {subscriberImport.mapping}
    </SplitControl>

    {#each importingSubscribers as importingSubscriber (importingSubscriber.email)}
        <ImportingSubscriberRow {importingSubscriber}/>
    {/each}
</Modal>