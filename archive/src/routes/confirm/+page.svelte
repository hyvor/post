<script lang="ts">
    import {onMount} from 'svelte';
    import {Loader} from '@hyvor/design/components';
    import {confirm} from '$lib/actions/confirmActions';
    import Notice from "./Notice.svelte";

    let isLoading = $state(false);
    let error = $state<string | null>(null);

    onMount(async () => {
        const url = new URL(window.location.href);
        const token = url.searchParams.get('token');

        if (!token) {
            error = 'Invalid confirmation link';
            isLoading = false;
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
    {#if isLoading}
        <div class="loader-wrap">
            <Loader full size="large"/>
        </div>
    {:else if error}
        <Notice
            heading="An error occurred"
            message={error}
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


<style>
    .container {
        width: 700px;
        margin: 0 auto;
        max-width: 100%;
        padding: 40px 0;
        text-align: center;
    }

    .loader-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
</style>
