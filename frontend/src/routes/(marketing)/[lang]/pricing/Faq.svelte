<script>
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
    import IconBarChart from '@hyvor/icons/IconBarChart';
    import IconWallet from '@hyvor/icons/IconWallet';
    import IconBoxArrowRight from '@hyvor/icons/IconBoxArrowRight';
    import FAQs from '../@components/FAQs.svelte'
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n()
</script>

<!-- "faqs": "FAQs",
		"isThereATrial": "Is there a trial?",
		"isThereATrialAnswer": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nunc necultricies ultricies, nunc nunc ultricies ultricies, nunc nunc ultricies",
		"whatHappensIfIExceedTheEmailsLimit": "What happens if I exceed the emails limit?",
		"whatHappensIfIExceedTheEmailsLimitAnswer": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nunc necultricies ultricies, nunc nunc ultricies ultricies, nunc nunc ultricies",
		"howDoPaymentsWork": "How do payments work?",
		"howDoPaymentsWorkAnswer": "Payments are processed securely through our in-house billing system. We accept all major credit/debit cards. You can choose to pay monthly or annually.",
		"canICancelAnytime": "Can I cancel anytime?",
		"canICancelAnytimeAnswer": "Yes, all subscriptions are self-managed. You can change your plan or cancel your subscription at any time in a few clicks. No questions asked. We do not offer refunds for the remaining period, except for unintentional auto-renewals." -->

<div class="wrap hds-container">
	<FeatureSectionHead title={I18n.t('pricing.faqs')} />
	<div class="faqs">
		<FAQs q={I18n.t('pricing.isThereATrial')} icon={IconArrowClockwise}>
			<!-- Yes, we offer a 14-days trial with all the features, except email custom domain. After
			the trial, you can choose to upgrade to a paid plan. Please contact us if you want to
			extend your trial. -->
			{I18n.t('pricing.isThereATrialAnswer')}
		</FAQs>

		<FAQs q={I18n.t('pricing.whatHappensIfIExceedTheEmailsLimit')} icon={IconBarChart}>
			<!-- Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nunc necultricies ultricies, nunc nunc ultricies ultricies, nunc nunc ultricies ultricies, -->
			{I18n.t('pricing.whatHappensIfIExceedTheEmailsLimitAnswer')}
		</FAQs>

		<FAQs q={I18n.t('pricing.howDoPaymentsWork')} icon={IconWallet}>
			{I18n.t('pricing.howDoPaymentsWorkAnswer')}
			<!-- Payments are processed securely through our in-house billing system. We accept all major
            credit/debit cards. You can choose to pay monthly or annually. -->
		</FAQs>

		<FAQs q={I18n.t('pricing.canICancelAnytime')} icon={IconBoxArrowRight}>
			{I18n.t('pricing.canICancelAnytimeAnswer')}
			<!-- Yes, all subscriptions are self-managed. You can change your plan or cancel your
			subscription at any time in a few clicks. No questions asked. We do not offer refunds
			for the remaining period, except for unintentional auto-renewals. -->
		</FAQs>
	</div>
</div>

<style>
	.wrap {
		padding-top: 40px;
		padding-bottom: 40px;
	}

	.faqs {
		display: flex;
		flex-wrap: wrap;
		gap: 60px 30px;
		padding: 20px 0;
		margin-top: 40px;
	}
</style>
