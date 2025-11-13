<script lang="ts">
    import {onMount} from 'svelte';
    import {draftIssueEditingStore, draftStepStore, initDraftStores} from './draftStore';
    import ContentView from './content/ContentView.svelte';
    import Steps from './Steps.svelte';
    import Audience from './audience/Audience.svelte';
    import {currentIssueStore} from "../../../../lib/stores/newsletterStore";

    interface Props {
        onStatusChange: () => void;
    }

    let {onStatusChange}: Props = $props();

    let init = $state(false);

    onMount(() => {
        initDraftStores($currentIssueStore);
        init = true;
    });
</script>

{#if init}
    <div class="draft-wrap">
        {#if $draftStepStore[$draftIssueEditingStore.id] === 'content'}
            <ContentView/>
        {:else if $draftStepStore[$draftIssueEditingStore.id] === 'audience'}
            <Audience/>
        {/if}

        <Steps {onStatusChange}/>
    </div>
{/if}

<style>
    .draft-wrap {
        display: flex;
        flex-direction: column;
        flex: 1;
        height: 100%;
        overflow: hidden;
    }
</style>
