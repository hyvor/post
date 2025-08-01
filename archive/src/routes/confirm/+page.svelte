<script lang="ts">
    import {onMount} from 'svelte';
    import {Loader} from '@hyvor/design/components';
    import {confirm} from '$lib/actions/confirmActions';
    import Notice from "./Notice.svelte";

    let isLoading = $state(true);
    let error = $state<string | null>(null);

    onMount(async () => {
        const url = new URL(window.location.href);
        const token = url.searchParams.get('token');

        if (!token) {
            error = 'Invalid confirmation link';
            // isLoading = false;
            return;
        }

        confirm(token)
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
            <Loader
                color="var(--hp-box-text)"
                block
            >
                Confirming your subscription...
            </Loader>
        {:else if error}
            <Notice
                heading="An error occurred"
                message={error}
                isError={true}
            />
        {:else}
            <Notice
                heading="Subscription Confirmed!"
                message="Thank you for confirming your subscription to <strong>Test Newsletter</strong>!
                    You will start receiving the latest updates straight to your inbox.
                    Stay tuned!"
            />
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
        padding: 40px 0;
        text-align: center;
        height: 100vh;
    }

    .inner-container {
        padding: 20px 0;
        min-height: 364px;
        color: var(--hp-box-text);
        background-color: var(--hp-box);
        box-shadow: var(--hp-box-shadow);
        border: var(--hp-box-border);
        border-radius: var(--hp-box-radius);
    }
</style>
