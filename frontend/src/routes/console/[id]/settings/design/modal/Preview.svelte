<script lang="ts">
	import { onMount } from 'svelte';
	import { renderTemplate } from '../../../../../(tools)/design/lib/actions/templateActions';
	import { Loader, toast } from '@hyvor/design/components';

	let iframeEl: HTMLIFrameElement;
	let loading = $state(true);

	onMount(function () {
		renderTemplate()
			.then((response) => {
				iframeEl.srcdoc = response.html;
			})
			.catch((error) => {
				toast.error(error.message);
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<iframe
	bind:this={iframeEl}
	sandbox="allow-same-origin allow-scripts"
	title="Preview"
	class:loaded={!loading}
></iframe>

{#if loading}
	<Loader full />
{/if}

<style>
	iframe {
		border: none;
	}
	iframe.loaded {
		width: 100%;
		height: 100%;
	}
</style>
