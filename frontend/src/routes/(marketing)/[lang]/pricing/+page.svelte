<script>
	import TrialSignUp from '../@components/TrialSignUp.svelte';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import Faq from './Faq.svelte';
	import PricingPlans from './PricingPlans.svelte';
	import { getMarketingI18n } from '../locale';
	import PriceCompare from '../@components/PriceCompare.svelte';

	const I18n = getMarketingI18n();
</script>

<svelte:head>
	<title>Pricing - Hyvor Post</title>
</svelte:head>
<div class="hds-container">
	<FeatureSectionHead
		title={I18n.t('pricing.simpleTransparentPricing')}
		subtitle={I18n.t('pricing.pricingBasedOnEmails')}
		wrapStyle="margin-top:50px"
	/>
</div>

<PricingPlans />
<PriceCompare />
<Faq />
<TrialSignUp />

