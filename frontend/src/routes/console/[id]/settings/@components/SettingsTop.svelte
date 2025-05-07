<script lang="ts">
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="top">
	{@render children?.()}
</div>

<style>
	.top {
		display: flex;
		padding: 15px 25px;
		border-bottom: 1px solid var(--border);
		align-items: center;
	}
</style>
