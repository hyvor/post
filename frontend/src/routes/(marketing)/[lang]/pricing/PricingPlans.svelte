<script>
	import { Button } from '@hyvor/design/components';
	import PricingPlan from './PricingPlan.svelte';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	let currency = '€';
	let yearly = false;
</script>

<div class="yearly-wrap hds-container">
	<div class="yearly hds-box">
		<Button
			on:click={() => (yearly = false)}
			color="input"
			size="small"
			variant={yearly ? 'invisible' : 'fill'}>{I18n.t('pricing.monthly')}</Button
		>
		<Button
			on:click={() => (yearly = true)}
			color="input"
			size="small"
			variant={!yearly ? 'invisible' : 'fill'}
			>{I18n.t('pricing.yearly')}<span class="off">{I18n.t('pricing.2MonthsOff')}</span
			></Button
		>
	</div>
</div>

<div class="plans hds-container">
	<PricingPlan {yearly} {currency} />
</div>

<div class="disclaimers">
	<div>{I18n.t('pricing.vatIncluded')}</div>
</div>

<style>
	.plans {
		display: flex;
		gap: 15px;
		padding-top: 20px;
		width: 450px;
		max-width: 100%;
	}

	.yearly-wrap {
		display: flex;
		justify-content: center;
		padding-top: 35px;
	}
	.yearly {
		display: inline-flex;
		width: 350px;
		background-color: #fff;
		padding: 5px;
		border-radius: 20px;
		gap: 3px;
	}
	.yearly :global(button) {
		flex: 1;
	}
	.off {
		color: var(--text-light);
		font-size: 12px;
		margin-left: 5px;
	}
	.disclaimers {
		text-align: center;
		color: var(--text-light);
		font-size: 14px;
		padding-top: 30px;
	}
	.disclaimers div {
		margin-bottom: 5px;
	}

	@media (max-width: 992px) {
		.plans {
			flex-direction: column;
		}
	}
</style>
