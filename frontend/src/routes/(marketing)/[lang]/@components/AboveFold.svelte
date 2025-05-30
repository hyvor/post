<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import TrialChecks from './TrialChecks.svelte';
	import MainGraphic from './MainGraphic.svelte';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();
</script>

<div class="hds-container above-fold">
	<div class="left">

		<h1>{I18n.t('homepage.heading')}</h1>

		<h2>
			{I18n.t('homepage.subHeading')}
		</h2>

		<div class="buttons">
			<Button as="a" href="/console?signup" size="large">{I18n.t('cta')}
				{#snippet end()}
					&rarr;
				{/snippet}
			</Button>
		</div>

		<div class="trial-checks">
			<TrialChecks />
		</div>
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
		font-size: 40px;
		margin-bottom: 10px;
	}

	h2 {
		font-weight: normal;
		font-size: 20px;
		margin: 0;
		padding-top: 15px;
		color: var(--text-light);
		line-height: 1.5;
	}

	.buttons {
		display: flex;
		gap: 10px;
		margin-top: 30px;
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
