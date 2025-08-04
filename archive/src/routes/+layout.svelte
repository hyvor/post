<script lang="ts">
    import './app.css';
    import {onMount} from 'svelte';
    import {initNewsletter} from '$lib/actions/archiveActions';
    import {subdomainStore, issuesStore, newsletterStore, listsStore} from '$lib/archiveStore';
    import Loader from './@components/Loader.svelte';
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
                listsStore.set(res.lists);
                setPaletteVars(res.palette);
                loading = false;
            })
            .catch((err) => {
                console.error(err.message);
            });
    });
</script>

{#if loading}
    <div class="loader-wrap">
        <Loader full size="large"/>
    </div>
{:else}
    <div class="outer-wrap">
        {@render children?.()}
    </div>
{/if}

<style>
    .loader-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .outer-wrap {
        background-color: var(--hp-background);
        height: 100vh;
    }
</style>
