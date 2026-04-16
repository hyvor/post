<script lang="ts">
	import Install from '$lib/install/Install.svelte';

	interface Props {
		installPlatform: string;
	}

	let { installPlatform }: Props = $props();
</script>

<h1>Install</h1>

<div class="install-wrap">
	<Install platform={installPlatform} prefix="/docs/install" />
</div>

<style>
	.install-wrap :global(.platforms) {
		background-color: var(--accent-lightest);
		/* #f9faff */
		padding: 30px 50px;
		margin: 0 -50px;
	}

	@media (max-width: 992px) {
		.install-wrap :global(.platforms) {
			margin: 0 -25px;
			padding: 30px 25px;
		}
	}
</style>
