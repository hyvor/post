<script lang="ts">
	import { onMount } from 'svelte';
	import { authUserOrganizationStore } from '../../lib/stores/consoleStore';
	import { goto } from '$app/navigation';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	onMount(() => {
		if (
			$authUserOrganizationStore?.role !== 'admin' &&
			$authUserOrganizationStore?.role !== 'billing'
		) {
			goto('/console');
		}
	});
</script>

{@render children?.()}
