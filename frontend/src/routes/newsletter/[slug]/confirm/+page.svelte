<script lang="ts">
    import { onMount } from 'svelte';
    import { Loader } from '@hyvor/design/components';
	import Icon from './Icon.svelte';

    let isLoading = $state(false);
    let error = $state<string | null>(null);
    let success = $state(true);

    onMount(async () => {
        const url = new URL(window.location.href);
        const token = url.searchParams.get('token');
        
        if (!token) {
            error = 'Invalid confirmation link';
            isLoading = false;
            return;
        }

        // try {
        //     const response = await fetch('/api/public/subscriber/confirm?token=' + token, {
        //         method: 'GET',
        //         headers: {
        //             'Content-Type': 'application/json',
        //         },
        //     });

        //     if (!response.ok) {
        //         const data = await response.json();
        //         throw new Error(data.message);
        //     }

        //     success = true;
        // } catch (e) {
        //     error = e instanceof Error ? e.message : 'An unexpected error occurred';
        // } finally {
        //     isLoading = false;
        // }
    });
</script>

<div class="container">
    {#if isLoading}
        <div class="loader-wrap">
            <Loader full size="large" />
        </div>
    {:else if error}
        <div class="error">
            <h2>Error</h2>
            <p>{error}</p>
        </div>
    {:else if success}

        <div class="container">
            <div class="inner-container hds-box">
                <div class="icon">
                    <Icon />
                </div>
                <div class="message">
                    <h2>Subscription Confirmed!</h2>
                    <p>
                        Thank you for confirming your subscription to <strong>Test Newsletter</strong>!
                        You will start receiving the latest updates straight to your inbox.
                        Stay tuned!
                    </p>
                </div>
            </div>
        </div>
                
    {/if}
</div>


<style>
    .container {
        width: 700px;
        margin: 0 auto;
        max-width: 100%;
        padding: 20px 0;
        text-align: center;
    }
    .inner-container {
        padding: 20px 0;
    }
    .icon {
        width: 50%;
        height: 50%;
        margin: 0 auto;
    }
    .message {
        width: 70%;
        margin: 0 auto;
        margin-top: -10px;
        margin-bottom: 30px;
    }
    .loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
    }
</style>
