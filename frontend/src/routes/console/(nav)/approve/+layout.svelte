<script lang="ts">
    import {onMount} from 'svelte';
    import {goto} from '$app/navigation';
    import {canAccessSettings} from "../../orgPermission";

    interface Props {
        children?: import('svelte').Snippet;
    }

    let {children}: Props = $props();

    onMount(() => {
        if (!canAccessSettings()) {
            goto('/console');
        }
    });
</script>

{@render children?.()}
