<script>
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconBarChart from '@hyvor/icons/IconBarChart';
	import IconWallet from '@hyvor/icons/IconWallet';
	import IconBoxArrowRight from '@hyvor/icons/IconBoxArrowRight';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';

	import Accordion from '../@components/Accordion.svelte';

	const I18n = getMarketingI18n();
</script>

<div class="wrap hds-container">
	<FeatureSectionHead title={I18n.t('pricing.faqs')} />
	<div class="faqs">
		<Accordion
			title="Do you offer a free trial?"
			icon={IconArrowClockwise}
			content="Yes, we offer a 14-days trial with all features included. In this period, you can send test emails to your team members. When you're ready to send emails to your subscribers, you should request an approval and upgrade to a paid plan."
		/>
		<Accordion
			title="How do payments work?"
			icon={IconWallet}
			content="Payments are processed securely through Hyvor Billing, which uses Stripe as the payment gateway. We accept all major credit/debit cards. You can choose to pay monthly or annually. Annual plans come with a discount of 2 months free."
		/>
		<Accordion
			title="What happens if I exceed the email limit?"
			icon={IconBarChart}
			content="If you exceed your email limit, you will have to upgrade to the next plan before sending another issue. We do not charge overages."
		/>
		<Accordion
			title="Can I cancel anytime?"
			icon={IconBoxArrowRight}
			content="Yes, you can cancel your subscription anytime. You can also export your subscriber list and easily migrate to another platform if needed."
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
