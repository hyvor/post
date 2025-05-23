<script lang="ts">
	import { Button, Slider } from '@hyvor/design/components';
	import IconCheckCircle from '@hyvor/icons/IconCheckCircle';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	interface Props {
		yearly?: boolean;
		currency: string;
	}

	let { yearly = false, currency }: Props = $props();

	const plans = {
		25_000: 10,
		100_000: 35,
		300_000: 90,
		1_000_000: 225
	};

	const currentPlans = plans;
	let currentPlan = $state(25_000);
	let sliderVal = $state(1);

	let currentPlanDisplay = $derived(
	currentPlan >= 1_000_000
		? `${currentPlan / 1_000_000}M`
		: `${currentPlan / 1000}K`
);

	let currentPrice = $derived((currentPlans as any)[currentPlan] * (yearly ? 10 : 1));

	function onSliderChange(event: any) {
		sliderVal = event.detail;

		const keys = Object.keys(currentPlans);
		const key = keys[sliderVal - 1];
		currentPlan = parseInt(key);
	}
	function getFeatures() {
		return [
			'lorem ipsum',
			'dolor sit amet',
			'consectetur adipiscing elit',
			'sed do eiusmod tempor',
			'incididunt ut labore et dolore magna aliqua',
			'ut enim ad minim veniam',
			'quis nostrud exercitation ullamco laboris'
		];
	}

	const features = getFeatures();
	const url = '/console/billing';
	const target = '_blank';
</script>

<div class="wrap hds-box">
	<div class="name">{I18n.t('pricing.chooseYourPlan')}</div>

	<div class="features">
		{#each features as feature}
			<div class="feature">
				<IconCheckCircle />
				<div class="feature-text">
					{feature}
				</div>
			</div>
		{/each}
	</div>

	<div class="email-selector">
		<div class="min-max">
			<span class="min"> 25k </span>
			<div>{currentPlanDisplay}</div>
			<span class="max"> 1M </span>
		</div>
		<Slider min={1} max={4} step={1} value={sliderVal} on:change={onSliderChange} />
		<div class="amount">{I18n.t('pricing.monthlyEmail')}</div>
	</div>

	<div class="price">
		<div class="price-display">
			<span class="price-amount">{currency}{currentPrice}</span><span class="price-period"
				>/{yearly ? I18n.t('pricing.year') : I18n.t('pricing.month')}{#if currentPrice === 10}*{/if}</span
			>
		</div>
	</div>

	<div class="button-wrap">
		<Button size="large" {target} as="a" href={url}>{I18n.t('pricing.choosePlan')}</Button>
	</div>
</div>

<style lang="scss">
	.wrap {
		flex: 1;
		display: flex;
		flex-direction: column;
	}
	.name {
		font-weight: 600;
		font-size: 25px;
		text-align: center;
		text-transform: capitalize;
		padding: 25px 20px;
		border-bottom: 1px solid var(--border);
	}
	.price {
		padding: 20px;
		text-align: center;
		background-color: #fafafa;
	}
	.price-display {
		font-weight: 600;
		font-size: 28px;
	}
	.price-period {
		font-size: 18px;
		font-weight: normal;
		color: var(--text-light);
	}

	.features {
		margin: 0 auto;
		padding: 20px 30px;
	}
	.feature {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 6px 0;
	}

	.feature :global(svg) {
		color: var(--green);
	}
	
	.feature-text {
		flex: 1;
	}
	.button-wrap {
		padding: 25px 20px;
		display: flex;
		justify-content: center;
	}
	.email-selector {
		padding: 20px 40px;
	}
	.email-selector .min-max {
		display: flex;
		align-items: flex-end;
		font-size: 12px;
		color: var(--text-light);
		margin-bottom: 10px;
	}

	.email-selector .min-max div {
		flex: 1;
		text-align: center;
		color: var(--text);
		font-weight: 600;
		font-size: 20px;
	}
	.email-selector .min-max .min {
		transform: translateX(-50%);
	}
	.email-selector .min-max .max {
		transform: translateX(50%);
	}
	.email-selector .amount {
		font-size: 14px;
		text-align: center;
		margin-top: 10px;
	}
	.email-selector :global(.tip) {
		display: none !important;
	}
</style>
