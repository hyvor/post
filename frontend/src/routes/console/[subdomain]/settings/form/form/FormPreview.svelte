<script lang="ts">
	import { resizeIframe } from "./iframe";

	let iframe: HTMLIFrameElement | undefined = $state();

	interface Props {
		id: number;
	}

	let { id }: Props = $props();

	function getSrc() {
		return `/api/public/form/render?id=${id}`;
	}

	$effect(() => {
		if (iframe && id) {
			iframe.src = getSrc();
		}
	});
</script>

<iframe
	src={getSrc()}
	title="Signup form preview"
	frameborder="0"
	height="100%"
	width="100%"
	bind:this={iframe}
	onload={() => resizeIframe(iframe)}
	style="height:100%;min-height:100%;"
></iframe>
