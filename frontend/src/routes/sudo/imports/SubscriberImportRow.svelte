<script lang="ts">
    import type {SubscriberImport} from "../types";
    import RelativeTime from "../../console/@components/utils/RelativeTime.svelte";

    interface Props {
        subscriberImport: SubscriberImport;
        handleSelect: (subscriberImport: SubscriberImport) => void;
    }

    let {subscriberImport, handleSelect}: Props = $props();
</script>

<button class="row" onclick={ () => handleSelect(subscriberImport) }>

    <div class="created-at">
        <RelativeTime unix={subscriberImport.created_at}/>
    </div>

    <div class="newsletter">
        {subscriberImport.newsletter_subdomain}
    </div>

    <div class="total-rows">
        {subscriberImport.total_rows}
    </div>

    <div class="source">
        {subscriberImport.source}
    </div>

</button>

<style>
    .row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background-color: var(--bg-light);
        border-radius: var(--box-radius);
        text-align: left;
        width: 100%;
    }

    .row:hover {
        background: var(--hover);
    }

    .created-at {
        width: 15%;
    }

    .newsletter {
        width: 30%;
    }

    .total-rows {
        width: 10%;
    }

    .source {
        width: 45%;
    }
</style>