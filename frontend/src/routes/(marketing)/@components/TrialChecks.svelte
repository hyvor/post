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
					a: { component: TrialLink }
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
		gap: 20px;
		justify-content: space-between;
		width: 700px;
		max-width: 100%;
		margin: auto;
	}
	.check {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-bottom: 6px;
	}

	.check :global(svg) {
		color: var(--accent);
	}

	@media (max-width: 992px) {
		.checks {
			justify-content: center;
			flex-direction: column;
			align-items: center;
			width: auto;
		}
		.check {
			width: auto;
		}
	}
</style>
