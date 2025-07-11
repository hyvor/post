<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import TrialChecks from './TrialChecks.svelte';
	import MainGraphic from './MainGraphic.svelte';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();
</script>

<div class="hds-container above-fold">
	<div class="left">

		<h1 id="waitlist">
			<!-- {I18n.t('homepage.heading')} -->
			 Hyvor Post
		</h1>

		<h2>
			Privacy-first, simple newsletter platform
			<!-- {I18n.t('homepage.subHeading')} -->
		</h2>

		<div class="coming-soon">
			Coming soon 🚀
		</div>

		<div class="buttons">
			<!-- <Button as="a" href="/console?signup" size="large">{I18n.t('cta')}
				{#snippet end()}
					&rarr;
				{/snippet}
			</Button> -->
			<!-- <Button as="a" href="/console?signup" size="large">Join the waitlist
				{#snippet end()}
					&rarr;
				{/snippet}
			</Button> -->
			<script async src="https://talk.hyvor.com/embed/newsletter.js" type="module"></script>
			<hyvor-talk-newsletter website-id="13652"></hyvor-talk-newsletter>
		</div>

		<!-- <div class="trial-checks">
			<TrialChecks />
		</div> -->
	</div>

	<div class="right">
		<MainGraphic />	
	</div>
</div>

<style>

	.above-fold {
		padding-top: 75px;
		display: flex;
	}

	.left {
			width: 50%;
	}

	.right {
		width: 50%;
		padding-left: 25px;
		
	}

	h1 {
		margin: 0;
		font-size: 50px;
		margin-bottom: 10px;
	}

	h2 {
		font-weight: normal;
		font-size: 30px;
		margin: 0;
		color: var(--text-light);
		line-height: 1.5;
	}

	.coming-soon {
		font-size: 24px;
		font-weight: 600;
		margin-top: 35px;
		padding-top: 35px;
		border-top: 1px solid #eee;
	}

	.buttons {
		display: flex;
		gap: 10px;
		margin-top: 10px;
	}

	 .trial-checks {
	 	margin-top: 25px;
	 	margin-left: 5px;
	 }

	@media (max-width: 992px) {
		.above-fold {
			flex-direction: column;
			text-align: center;
		}

		.left {
			width: 100%;
			padding-left: 0;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		h1 {
			margin-top: 20px;
		}

		h2 {
			padding-top: 10px;
		}

		.right {
			margin: auto;
			margin-top: 30px;
			padding-left: 0;
			width: 500px;
			max-width: 100%;
		}
		.buttons {
			justify-content: center;
		}

		.trial-checks {
			margin-left: 0;
			display: flex;
			justify-content: center;
		}
	}
</style>
