<script>
	import TrialSignUp from "../@components/TrialSignUp.svelte";
import FeatureSectionHead from "../@homepage/FeatureSectionHead.svelte";
	import Faq from "./Faq.svelte";
	import PricingPlans from "./PricingPlans.svelte";

</script>

<svelte:head>
	<title>Pricing - Hyvor Post</title>
</svelte:head>
<div class="hds-container">
	<FeatureSectionHead
		title="Simple, transparent pricing"
		subtitle="Our pricing is based on the emails you use per month."
		wrapStyle="margin-top:50px"
	/>
</div>

<PricingPlans />

<Faq />
<TrialSignUp />
