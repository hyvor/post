<script lang="ts">
	export let title: string;
	export let value: number;
	export let total: number | undefined = undefined;

	$: percentage = total ? ((value / total) * 100).toFixed(2) : 0;
</script>

<div class="stat">
	<div class="title">{title}</div>
	<div class="value-wrap">
		<span class="value">{value}</span>
		{#if total}
			<span class="perc">
				{+percentage}%
			</span>
		{/if}
	</div>
</div>

<style>
	.stat {
		padding: 10px 0;
	}
	.title {
		margin-bottom: 5px;
	}
	.value {
		font-size: 35px;
		margin-right: 5px;
	}
	.perc {
		color: var(--text-light);
	}
</style>
