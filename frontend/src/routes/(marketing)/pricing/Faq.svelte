<script>
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconBarChart from '@hyvor/icons/IconBarChart';
	import IconWallet from '@hyvor/icons/IconWallet';
	import IconBoxArrowRight from '@hyvor/icons/IconBoxArrowRight';
	import FAQs from '../@components/FAQs.svelte';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';
	import DetailsSummary from '../@components/DetailsSummary.svelte';

	const I18n = getMarketingI18n();
</script>

<div class="wrap hds-container">
	<FeatureSectionHead title={I18n.t('pricing.faqs')} />
	<div class="faqs">
		<DetailsSummary
			summary={I18n.t('pricing.isThereATrial')}
			icon={IconArrowClockwise}
			content={I18n.t('pricing.isThereATrialAnswer')}
		/>
		<DetailsSummary
			summary={I18n.t('pricing.whatHappensIfIExceedTheEmailsLimit')}
			icon={IconBarChart}
			content={I18n.t('pricing.whatHappensIfIExceedTheEmailsLimitAnswer')}
		/>
		<DetailsSummary
			summary={I18n.t('pricing.howDoPaymentsWork')}
			icon={IconWallet}
			content={I18n.t('pricing.howDoPaymentsWorkAnswer')}
		/>
		<DetailsSummary
			summary={I18n.t('pricing.canICancelAnytime')}
			icon={IconBoxArrowRight}
			content={I18n.t('pricing.canICancelAnytimeAnswer')}
		/>
	</div>
</div>

<style>
	.wrap {
		padding-top: 40px;
		padding-bottom: 40px;
	}

	.faqs {
		margin: 40px auto;
		max-width: 800px;
		display: flex;
		flex-direction: column;
		gap: 20px;
		padding: 0 0 80px 0;
	}
</style>
