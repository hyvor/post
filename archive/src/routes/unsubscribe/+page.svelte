<script lang="ts">
    import {onMount} from 'svelte';
    import Loader from '../@components/Loader.svelte';
    import Notice from "../@components/Notice.svelte";
    import IconEnvelopeSlash from "@hyvor/icons/IconEnvelopeSlash";
    import IconExclamationOctagon from "@hyvor/icons/IconExclamationOctagon";
    import {newsletterStore, listsStore} from "$lib/archiveStore";
    import Resubscribe from "./Resubscribe.svelte";
    import {unsubscribe} from "$lib/actions/subscriptionActions";

    let isLoading = $state(true);
    let error = $state<string | undefined>(undefined);
    let token: string | undefined = $state(undefined);

    onMount(async () => {
        const url = new URL(window.location.href);
        const param = url.searchParams.get('token');

        if (!param) {
            error = 'Invalid unsubscription link';
            isLoading = false;
            return;
        }

        token = param;

        unsubscribe(token)
            .then((data) => {
                listsStore.set(data.lists);
            })
            .catch((e) => {
                error = e.message || 'An unexpected error occurred';
            })
            .finally(() => {
                isLoading = false;
            });
    });
</script>

<div class="container">
    <div class="inner-container hds-box">
        {#if isLoading}
            <div class="loader-wrap">
                <Loader
                    color="var(--hp-box-text)"
                    block
                >
                    Unsubscribing...
                </Loader>
            </div>
        {:else if error}
            <Notice
                heading="An error occurred"
                message={error}
                icon={IconExclamationOctagon}
            />
        {:else}
            <Notice
                heading="Unsubscribe successful"
                message="You have unsubscribed from all lists of <strong>{$newsletterStore.name}</strong>.
                    <br />
                    If this was a mistake, you can easily resubscribe below. Thank you."
                icon={IconEnvelopeSlash}
            >
                <Resubscribe lists={$listsStore} {token} bind:error/>
            </Notice>
        {/if}
    </div>
</div>

<style>
    .container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 650px;
        margin: auto;
        max-width: 100%;
        text-align: center;
        height: 100vh;
    }

    .inner-container {
        min-height: 409px;
        color: var(--hp-box-text);
        background-color: var(--hp-box);
        box-shadow: var(--hp-box-shadow);
        border: var(--hp-box-border);
        border-radius: var(--hp-box-radius);
    }

    .loader-wrap {
        height: 350px;
    }
</style>
