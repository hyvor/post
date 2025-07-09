<script lang="ts">
    import { onMount } from 'svelte';
    import { Loader } from '@hyvor/design/components';

    let isLoading = $state(true);
    let error = $state<string | null>(null);
    let success = $state(false);

    onMount(async () => {
        const url = new URL(window.location.href);
        const token = url.searchParams.get('token');
        
        if (!token) {
            error = 'Invalid confirmation link';
            isLoading = false;
            return;
        }

        try {
            const response = await fetch('/api/public/subscriber/confirm?token=' + token, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message);
            }

            success = true;
        } catch (e) {
            error = e instanceof Error ? e.message : 'An unexpected error occurred';
        } finally {
            isLoading = false;
        }
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
        <div class="success">
            <h2>Subscription Confirmed!</h2>
            <p>Thank you for confirming your subscription. You will now receive our newsletters.</p>
        </div>
    {/if}
</div>

<style>
    .container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        text-align: center;
    }

    .loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style> 
