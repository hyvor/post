<script>
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconBarChart from '@hyvor/icons/IconBarChart';
	import IconWallet from '@hyvor/icons/IconWallet';
	import IconBoxArrowRight from '@hyvor/icons/IconBoxArrowRight';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';
	import { Accordion } from '@hyvor/design/components';

	const I18n = getMarketingI18n();
</script>

<div class="wrap hds-container">
	<FeatureSectionHead title={I18n.t('pricing.faqs')} />
	<div class="faqs">
		<Accordion
			title={I18n.t('pricing.isThereATrial')}
			icon={IconArrowClockwise}
			headerColor="var(--accent-light-mid)"
			openedColor="var(--accent-light)"
			borderColor="#f3f4f6"
			topBorderColor="#f3f4f6"
		>
			{I18n.t('pricing.isThereATrialAnswer')}
		</Accordion>
		<Accordion
			title={I18n.t('pricing.whatHappensIfIExceedTheEmailsLimit')}
			icon={IconBarChart}
			headerColor="var(--accent-light-mid)"
			openedColor="var(--accent-light)"
			borderColor="#f3f4f6"
			topBorderColor="#f3f4f6"
		>
			{I18n.t('pricing.whatHappensIfIExceedTheEmailsLimitAnswer')}
		</Accordion>
		<Accordion
			title={I18n.t('pricing.howDoPaymentsWork')}
			icon={IconWallet}
			headerColor="var(--accent-light-mid)"
			openedColor="var(--accent-light)"
			borderColor="#f3f4f6"
			topBorderColor="#f3f4f6"
		>
			{I18n.t('pricing.howDoPaymentsWorkAnswer')}
		</Accordion>
		<Accordion
			title={I18n.t('pricing.canICancelAnytime')}
			icon={IconBoxArrowRight}
			headerColor="var(--accent-light-mid)"
			openedColor="var(--accent-light)"
			borderColor="#f3f4f6"
			topBorderColor="#f3f4f6"
		>
			{I18n.t('pricing.canICancelAnytimeAnswer')}
		</Accordion>
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
