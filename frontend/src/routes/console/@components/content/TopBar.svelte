<script lang="ts">
	export let right = false;
</script>

<div class="top" class:right>
	<slot />
</div>

<style>
	.top {
		display: flex;
		padding: 15px 25px;
		border-bottom: 1px solid var(--border);
		align-items: center;
	}
	.top.right {
		justify-content: flex-end;
	}
	.top :global(.left) {
		flex: 1;
	}
</style>
