<script lang="ts">
    import {Base} from '@hyvor/design/components';
    import {subdomainStore} from "$lib/archiveStore";
    import type {LayoutProps} from './$types';
    import {onMount} from "svelte";

    let {data, children}: LayoutProps = $props();

    onMount(() => {
        subdomainStore.set(data.subdomain)
    });
</script>

<Base>
    {@render children?.()}
</Base>
