<script lang="ts">
	import { onMount } from 'svelte';
	import { userNewslettersStore } from './lib/stores/userNewslettersStore';
	import { consoleUrl } from './lib/consoleUrl';
	import { goto } from '$app/navigation';

	onMount(() => {
		if ($userNewslettersStore.length > 0) {
			goto(consoleUrl($userNewslettersStore[0]!.project.id.toString()), {
				replaceState: true
			});
		} else {
			goto(consoleUrl('new'), { replaceState: true });
		}
	});
</script>
