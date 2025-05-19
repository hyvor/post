<script>
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
    import IconBarChart from '@hyvor/icons/IconBarChart';
    import IconWallet from '@hyvor/icons/IconWallet';
    import IconBoxArrowRight from '@hyvor/icons/IconBoxArrowRight';
    import FAQs from '../@components/FAQs.svelte'
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
</script>

<div class="wrap hds-container">
	<FeatureSectionHead title="FAQs" />
	<div class="faqs">
		<FAQs q="Is there a trial?" icon={IconArrowClockwise}>
			Yes, we offer a 14-days trial with all the features, except email custom domain. After
			the trial, you can choose to upgrade to a paid plan. Please contact us if you want to
			extend your trial.
		</FAQs>

		<FAQs q="What happens if I exceed the emails limit?" icon={IconBarChart}>
			The embed will be disabled. You must upgrade your plan to continue using the service
		</FAQs>

		<FAQs q="How do payments work?" icon={IconWallet}>
			Payments are processed securely through our in-house billing system. We accept all major
            credit/debit cards. You can choose to pay monthly or annually.
		</FAQs>

		<FAQs q="Can I cancel anytime?" icon={IconBoxArrowRight}>
			Yes, all subscriptions are self-managed. You can change your plan or cancel your
			subscription at any time in a few clicks. No questions asked. We do not offer refunds
			for the remaining period, except for unintentional auto-renewals.
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
