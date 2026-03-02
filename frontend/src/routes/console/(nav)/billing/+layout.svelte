<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { canAccessBilling } from '../../orgPermission';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	onMount(() => {
		if (!canAccessBilling()) {
			goto('/console');
		}
	});
</script>

{@render children?.()}
