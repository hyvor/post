<script lang="ts">
	interface Props {
		icon?: import('svelte').Snippet;
		text?: import('svelte').Snippet;
		end?: import('svelte').Snippet;
	}

	let { icon, text, end }: Props = $props();
	
</script>

<div class="nav-item">
	{@render icon?.()}
	{@render text?.()}
	{#if end}
		<div class="end">
			{@render end?.()}
		</div>
	{/if}
</div>

<style>
	.nav-item {
		display: flex;
		align-items: center;
		gap: 0.5rem;
		width: 100%;
	}
	.end {
		flex: 1;
		text-align: right;
	}
</style>
