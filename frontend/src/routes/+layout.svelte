<script>
	import './app.css';
	import { Base } from '@hyvor/design/components';
	import { track } from '@hyvor/design/marketing';
	import { onMount } from 'svelte';

	onMount(() => {
		track.init({
			// forceTrack: true,
			context: {
				component: 'core'
			}
		});
	});
</script>

<Base>
	<slot />
</Base>
