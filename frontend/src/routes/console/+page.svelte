<script lang="ts">
	import { onMount } from 'svelte';
	import { newsletterStore } from './lib/stores/newsletterStore';
	import { consoleUrl } from './lib/consoleUrl';
	import { goto } from '$app/navigation';
	import { isEmbedded } from './lib/embedded';

	onMount(() => {
		if ($newsletterStore) {
			goto(consoleUrl($newsletterStore.subdomain), {
				replaceState: true
			});
		} else {
			if ($isEmbedded) {
				return;
			}
			goto(consoleUrl('new'), { replaceState: true });
		}
	});
</script>
