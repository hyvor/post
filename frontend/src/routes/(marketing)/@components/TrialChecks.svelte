<script>
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import { getMarketingI18n } from '../locale';
	import TrialLink from './TrialLink.svelte';

	const I18n = getMarketingI18n();
</script>

<div class="checks" {...$$restProps}>
	<div class="check">
		<IconCheckCircleFill />
		<div>
			<I18n.T
				key="homepage.14DayFreeTrial"
				params={{
					trial: { component: TrialLink }
				}}
			/>
		</div>
	</div>

	<div class="check">
		<IconCheckCircleFill />
		{I18n.t('homepage.noCreditCardRequired')}
	</div>

	<div class="check">
		<IconCheckCircleFill />
		{I18n.t('homepage.cancelAnytime')}
	</div>
</div>

<style>
	.checks {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.check {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-bottom: 6px;
	}

	.check :global(svg) {
		color: var(--text-light);
	}

	@media (max-width: 992px) {
		.checks {
			justify-content: center;
			flex-direction: column;
			align-items: center;
		}

		.check {
			width: 300px;
			max-width: 100%;
		}
	}
</style>
