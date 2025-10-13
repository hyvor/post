<script lang="ts">
	import { Button, Slider } from '@hyvor/design/components';
	import IconCheckCircle from '@hyvor/icons/IconCheckCircle';
	import IconXCircle from '@hyvor/icons/IconXCircle';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	interface Props {
		yearly?: boolean;
		currency?: string;
		enterprise?: boolean;
	}

	let { yearly = false, currency, enterprise = false }: Props = $props();

	const plans = {
		25_000: 10,
		100_000: 35,
		300_000: 90,
		1_000_000: 225
	};

	const currentPlans = plans;
	let currentPlan = $state(25_000);
	let sliderVal = $state(1);

	// let currentPlanDisplay = $derived(
	// 	currentPlan >= 1_000_000 ? `${currentPlan / 1_000_000}M` : `${currentPlan / 1000}K`
	// );
	let currentPlanDisplay = $derived(
		enterprise
			? '1M+'
			: currentPlan >= 1_000_000
				? `${currentPlan / 1_000_000}M`
				: `${currentPlan / 1000}K`
	);

	let currentPrice = $derived((currentPlans as any)[currentPlan] * (yearly ? 10 : 1));

	function onSliderChange(event: any) {
		sliderVal = event.detail;

		const keys = Object.keys(currentPlans);
		if (sliderVal > keys.length) {
			currentPlan = 0; // dummy value
			enterprise = true;
		} else {
			const key = keys[sliderVal - 1];
			currentPlan = parseInt(key);
			enterprise = false;
		}
	}
	function getFeatures() {
		if (enterprise) {
			return [
				I18n.t('pricing.featureList.allFeaturesIncluded'),
				I18n.t('pricing.featureList.samlSso'),
				I18n.t('pricing.featureList.customSla'),
				I18n.t('pricing.featureList.dedicatedSupport'),
				I18n.t('pricing.featureList.customOnboarding')
			];
		}
		return [
			I18n.t('pricing.featureList.unlimitedTeam'),
			I18n.t('pricing.featureList.emailAndChatSupport'),
			I18n.t('pricing.featureList.customizableTemplates'),
			I18n.t('pricing.featureList.customEmailDomain')
		];
	}

	let features = $derived(getFeatures());
	const url = '/console/billing';
	const target = '_blank';
</script>

<div class="wrap hds-box">
	<div class="name">
		{#if enterprise}
			Enterprise Plan
		{:else}
			{I18n.t('pricing.saas')}
		{/if}
		{#if !enterprise}
			<div class="plan-toggle">
				<Button
					on:click={() => (yearly = false)}
					color="input"
					size="small"
					variant={yearly ? 'invisible' : 'fill'}
				>
					{I18n.t('pricing.monthly')}
				</Button>
				<Button
					on:click={() => (yearly = true)}
					color="input"
					size="small"
					variant={!yearly ? 'invisible' : 'fill'}
				>
					{I18n.t('pricing.yearly')}
					<span class="off">{I18n.t('pricing.2MonthsOff')}</span>
				</Button>
			</div>
		{:else}
			<div class="plan-yearly-only">
				<div class="yearly-only">Yearly Contracts</div>
			</div>
		{/if}
	</div>

	<div class="features">
		{#each getFeatures() as feature}
			<div class="feature">
				<IconCheckCircle />
				<div class="feature-text">
					{feature}
				</div>
			</div>
		{/each}

		{#if !enterprise}
			{#if currentPlan === 25_000}
				<div class="feature">
					<IconXCircle style="color: var(--text-light);" />
					<div class="feature-text">
						{I18n.t('pricing.featureList.noBranding')}
					</div>
					<!-- {I18n.t('pricing.plan25kNote')} -->
				</div>
			{:else}
				<div class="feature">
					<IconCheckCircle />
					<div class="feature-text">
						{I18n.t('pricing.featureList.noBranding')}
					</div>
				</div>
			{/if}
		{/if}
	</div>

	{#if !enterprise}
		<div class="email-selector">
			<div class="min-max">
				<span class="min"> 25k </span>
				<div>{currentPlanDisplay}</div>
				<span class="max"> 1M </span>
			</div>
			<Slider min={1} max={4} step={1} value={sliderVal} on:change={onSliderChange} />
			<div class="amount">{I18n.t('pricing.monthlyEmail')}</div>
		</div>
	{:else}
		<div class="email-selector">
			<div class="min-max" style="justify-content: center;">
				<div>1M+ emails per month</div>
			</div>
		</div>
	{/if}

	<div class="card-footer">
		<div class="price">
			{#if !enterprise}
				<span class="price-amount">{currency}{currentPrice}</span>
				<span class="price-period">
					/{yearly ? I18n.t('pricing.year') : I18n.t('pricing.month')}
					{#if currentPrice === 10}*{/if}</span
				>
			{:else}
				<span class="price-amount">Custom Pricing</span>
			{/if}
		</div>

		<div class="button-wrap">
			{#if !enterprise}
				<Button size="large" {target} as="a" href={url}
					>{I18n.t('pricing.choosePlan')}</Button
				>
			{:else}
				<Button size="large" {target} as="a" href="https://hyvor.com/enterprise"
					>Contact Us</Button
				>
			{/if}
		</div>
	</div>
</div>

<style>
	.wrap {
		flex: 1;
		display: flex;
		flex-direction: column;
	}
	.name {
		display: flex;
		flex-direction: column;
		font-weight: 600;
		font-size: 25px;
		text-align: center;
		text-transform: capitalize;
		padding: 25px 20px 0 20px;
		border-bottom: 1px solid var(--border);
	}
	.price {
		padding: 20px;
		text-align: center;
		background-color: #fafafa;
	}
	.price-period {
		font-size: 18px;
		font-weight: normal;
		color: var(--text-light);
	}

	.features {
		margin: 0 auto;
		padding: 20px 30px;
		min-height: 220px;
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

	.card-footer {
		text-align: center;
		margin-top: auto;
	}

	.price {
		margin-bottom: 15px;
		color: black;
		font-weight: 600;
		font-size: 28px;
	}

	.plan-toggle {
		margin: 15px auto 25px auto;
		display: inline-flex;
		width: 350px;
		background-color: var(--accent-lightest);
		padding: 5px;
		border-radius: 20px;
		gap: 3px;
	}

	.plan-toggle :global(button) {
		flex: 1;
	}
	.off {
		font-size: 12px;
		color: var(--text-light);
		margin-left: 5px;
	}

	.plan-yearly-only {
		margin: 15px auto 25px auto;
		justify-content: center;
		display: inline-flex;
		width: 350px;
		padding: 5px;
	}

	.yearly-only {
		height: 26px;
		padding: 0 12px;
		text-align: center;
		font-size: 15px;
		color: var(--text-light);
	}
</style>
