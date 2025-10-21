<script lang="ts">
	import DetailsSummary from './DetailsSummary.svelte';
	import IconLayers from '@hyvor/icons/IconLayers';
	import IconArrowLeftRight from '@hyvor/icons/IconArrowLeftRight';
	import IconLock from '@hyvor/icons/IconLock';
	import IconHourglass from '@hyvor/icons/IconHourglass';

	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';
	// import Accordion from './Accordion.svelte';
	import { Accordion } from '@hyvor/design/components';
	const I18n = getMarketingI18n();
</script>

<FeatureSectionHead title={I18n.t('homepage.faqs')} wrapStyle="padding: 0 0 50px 0" />

<div class="faq-accordion hds-container-max">
	<Accordion
		title="Can I start sending newsletters right away?"
		icon={IconHourglass}
		headerColor="var(--accent-light-mid)"
		openedColor="var(--accent-light)"
		borderColor="#f3f4f6"
		topBorderColor="#f3f4f6"
	>
		Before sending your first newsletter, you must get your account approved by our team. We do
		this to ensure zero spam is sent through Hyvor Post and that your newsletteer complies with
		our <a href="/terms" class="hds-link">Terms of Service</a>. The approval process is quick,
		and you will usually get a response within 1 business day (usually much faster).
		<br /><br />
		Note that before getting approval, you can collect or import subscribers, prepare your first
		newsletter issue, and test it with your own email address.</Accordion
	>

	<Accordion
		title="Can I migrate subscribers from another platform?"
		icon={IconArrowLeftRight}
		headerColor="var(--accent-light-mid)"
		openedColor="var(--accent-light)"
		borderColor="#f3f4f6"
		topBorderColor="#f3f4f6"
	>
		Yes, we provide a tool to import subscribers from a CSV file. For more complex migrations,
		contact our support team. We will help you quickly migrate your subscribers.
	</Accordion>

	<Accordion
		title="So, there is no way to track opens or clicks?"
		icon={IconLock}
		headerColor="var(--accent-light-mid)"
		openedColor="var(--accent-light)"
		borderColor="#f3f4f6"
		topBorderColor="#f3f4f6"
	>
		No, tracking is not available in Hyvor Post. If you are used to these features in other
		platforms, a little shift in mindset may be required.
	</Accordion>

	<Accordion
		title="Are emails shared with third parties?"
		icon={IconLayers}
		headerColor="var(--accent-light-mid)"
		openedColor="var(--accent-light)"
		borderColor="#f3f4f6"
		topBorderColor="#f3f4f6"
	>
		No, all emails are sent through <a
			href="https://relay.hyvor.com"
			target="_blank"
			class="hds-link">Hyvor Relay</a
		>, a first-party email delivery service.</Accordion
	>
</div>

<style>
	.faq-accordion {
		max-width: 800px;
		display: flex;
		flex-direction: column;
		gap: 20px;
		padding-bottom: 80px;
	}

	@media (max-width: 768px) {
		.faq-accordion {
			padding: 0 20px 60px 20px;
			max-width: 100%;
		}
	}
</style>
