<script lang="ts">
	import DetailsSummary from './DetailsSummary.svelte';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconBarChart from '@hyvor/icons/IconBarChart';
	import IconCheck from '@hyvor/icons/IconCheck';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';
	const I18n = getMarketingI18n();
</script>

<FeatureSectionHead
	title={I18n.t('homepage.faqs')}
	subtitle={I18n.t('homepage.faqsDescription')}
	wrapStyle="padding: 30px 0 0 0"
/>

<div class="faq-accordion hds-container">
	<DetailsSummary
		summary="Question 1"
		content="Question 1 content goes here."
		icon={IconArrowClockwise}
	/>

	<DetailsSummary
		summary="Question 2"
		content="Question 2 content goes here."
		icon={IconBarChart}
	/>

	<DetailsSummary summary="Question 3" content="Question 3 content goes here." icon={IconCheck} />
</div>

<style>
	.faq-accordion {
		margin: 40px auto;
		max-width: 800px;
		display: flex;
		flex-direction: column;
		gap: 20px;
		padding: 0 0 80px 0;
	}
</style>
