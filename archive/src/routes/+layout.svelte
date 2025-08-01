<script lang="ts">
    import './app.css';
    import {onMount} from 'svelte';
    import {initNewsletter} from '$lib/actions/archiveActions';
    import {subdomainStore, issuesStore, newsletterStore} from '$lib/archiveStore';
    import {Loader, toast} from '@hyvor/design/components';
    import type {Palette} from '$lib/types';
    import type {LayoutProps} from "../../.svelte-kit/types/src/routes/$types";

    let {data, children}: LayoutProps = $props();

    let loading = $state(true);

    function setPaletteVars(palette: Palette) {
        const root = document.documentElement;
        Object.entries(palette).forEach(([key, value]) => {
            root.style.setProperty(`--hp-${key.replace(/_/g, '-')}`, value);
        });
    }

    onMount(() => {
        subdomainStore.set(data.subdomain)
        initNewsletter($subdomainStore)
            .then((res) => {
                newsletterStore.set(res.newsletter);
                issuesStore.set(res.issues);
                setPaletteVars(res.palette);
                loading = false;
            })
            .catch((err) => {
                toast.error(err.message);
            });
    });
</script>

{#if loading}
    <div class="loader-wrap">
        <Loader full size="large"/>
    </div>
{:else}
    {@render children?.()}
{/if}

<style>
    .loader-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
</style>
