<script lang="ts">
    import type {Issue} from '../../../../../types';
    import {onMount} from 'svelte';
    import {draftStepStore, initDraftStores} from './draftStore';
    import ContentView from './content/ContentView.svelte';
    import Steps from './Steps.svelte';
    import Audience from './audience/Audience.svelte';

    interface Props {
        issue: Issue;
        send: (e: Issue) => void;
    }

    let {issue, send}: Props = $props();

    let init = $state(false);

    onMount(() => {
        initDraftStores(issue);
        init = true;
    });
</script>

{#if init}
    <div class="draft-wrap">
        {#if $draftStepStore === 'content'}
            <ContentView/>
        {:else if $draftStepStore === 'audience'}
            <Audience/>
        {/if}

        <Steps/>
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
