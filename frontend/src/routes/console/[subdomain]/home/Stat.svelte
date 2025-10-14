<script lang="ts">
	import { getI18n } from '../../lib/i18n';

	interface Props {
		href?: string;
		title: string;
		counts: {
			total: number;
			last_30_days: number;
		};
		enabled?: boolean;
		children?: import('svelte').Snippet;
		percent?: boolean;
	}

	let { href, title, counts, enabled = true, children, percent = false }: Props = $props();

	let change = $derived(percent ? counts.last_30_days - counts.total : counts.last_30_days);

	const I18n = getI18n();
</script>

<div class="stat">
	<div class="title">
		{#if href}
			<a {href}>
				{title}
			</a>
		{:else}
			{title}
		{/if}
	</div>
	<div class="value-wrap">
		{#if enabled}
			<span class="value"
				>{enabled ? counts.total.toLocaleString() : '-'}{#if percent}<span class="percent"
						>%</span
					>{/if}</span
			>
			{#if change !== null && enabled}
				<span class="change" class:positive={change >= 0} class:negative={change < 0}>
					{change >= 0 ? '+' : ''}{change.toLocaleString()}{#if percent}<span
							class="percent">%</span
						>{/if}
					<span class="last-30d-tag">{I18n.t('console.billing.usage.30days')}</span>
				</span>
			{/if}
		{/if}
		{#if children && !enabled}
			<div class="below">
				{@render children?.()}
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

	.percent {
		font-size: 0.6em;
		opacity: 0.5;
	}
</style>
