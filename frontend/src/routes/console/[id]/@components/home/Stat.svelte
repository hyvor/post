<script lang="ts">

	export let href: string;
	export let title: string;
	export let counts: {
		total: number;
		last_30d: number;
	};
	export let enabled = true;

	$: total = counts.total;
	$: change = counts.last_30d;

</script>

<div class="stat">
	<div class="title">
		<a {href}>
			{title}
		</a>
	</div>
	<div class="value-wrap">
		{#if enabled}
			<span class="value">{enabled ? total.toLocaleString() : '-'}</span>
			{#if change !== null && enabled}
				<span class="change" class:positive={change >= 0} class:negative={change < 0}>
					{change >= 0 ? '+' : ''}{change.toLocaleString()}
					<span class="last-30d-tag">30d</span>
				</span>
			{/if}
		{/if}
		{#if $$slots.default && !enabled}
			<div class="below">
				<slot />
			</div>
		{/if}
	</div>
</div>

<style>
	a:hover {
		text-decoration: underline;
	}
	.title {
		margin-bottom: 5px;
	}
	.value {
		font-size: 35px;
	}
	.last-30d-tag {
		font-size: 12px;
		color: var(--text-light);
	}

	.change.positive {
		color: var(--green);
	}
	.change.negative {
		color: var(--red);
	}
	.below {
		margin-top: 10px;
	}
</style>
