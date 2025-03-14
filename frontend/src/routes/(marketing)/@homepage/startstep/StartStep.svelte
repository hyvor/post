<script>
	// import IconCopy, IconPencil, IconPersonAdd from '@hyvor/icons';
	import IconCopy from '@hyvor/icons/IconCopy';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconPersonAdd from '@hyvor/icons/IconPersonAdd';
	import FeatureSectionHead from '../FeatureSectionHead.svelte';
	import Step from './Step.svelte';
</script>

<div class="hds-container">
	<FeatureSectionHead
		title="Just 3 Steps"
		subtitle="It's just simple and easy to get started with Hyvor Post"
		wrapStyle="margin-top:40px"
	/>
</div>

<div class="wrap">
	<div class="inner">
		<Step
			icon={IconPersonAdd}
			number={1}
			title="Sign up"
			description="Register for Hyvor Post and enter your website's details in the Console."
		/>
		<Step
			icon={IconCopy}
			number={2}
			title="Copy & Paste"
			description="Insert the provided code snippet into your website's HTML."
		/>
		<Step
			icon={IconPencil}
			number={3}
			title="Customize & Enjoy"
			description="Personalize the appearance and text, then enjoy Hyvor Post on your site."
		></Step>
	</div>
</div>

<style>
	.wrap {
		margin: 80px 0;
		padding: 60px 20px;
		background-color: var(--accent-light);
	}

	.inner {
		display: flex;
		justify-content: space-between;
		width: 1200px;
		padding: 0 15px;
		margin: 0 auto;
		max-width: 100%;
	}

	@media (max-width: 992px) {
		.inner {
			flex-direction: column;
		}
	}
</style>
