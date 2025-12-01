<script>
	import { Button } from '@hyvor/design/components';
	import PricingPlan from './PricingPlan.svelte';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	let currency = '€';
	let yearly = false;
</script>

<!-- <div class="yearly-wrap hds-container">
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
</div> -->

<div class="plans hds-container">
	<PricingPlan {yearly} {currency} />

	<PricingPlan enterprise={true} {yearly} {currency} />
</div>

<div class="disclaimers">
	<div>{I18n.t('pricing.vatIncluded')}</div>
</div>

<style>
	.plans {
		display: flex;
		justify-content: center;
		gap: 25px;
		flex-wrap: wrap;
		padding-top: 40px;
		width: 100%;
		max-width: 1000px;
		margin: 0 auto;
		position: relative;
		z-index: 1;
	}

	/* .yearly-wrap {
		display: flex;
		justify-content: center;
		padding-top: 35px;
		position: relative;
		z-index: 1;
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
	} */
	.disclaimers {
		text-align: center;
		color: var(--text-light);
		font-size: 14px;
		padding-top: 30px;
		position: relative;
		z-index: 1;
	}
	.disclaimers div {
		margin-bottom: 5px;
	}

	/* Responsive */
	@media (max-width: 992px) {
		.plans {
			flex-direction: column;
			flex-wrap: unset !important;
		}
	}
</style>
