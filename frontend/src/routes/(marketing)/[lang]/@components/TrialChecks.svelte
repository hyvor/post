<script>
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();
</script>

<div class="checks" {...$$restProps}>
	<div class="check">
		<IconCheckCircleFill />
		{I18n.t('14DayFreeTrial')}
	</div>

	<div class="check">
		<IconCheckCircleFill />
		{I18n.t('noCreditCardRequired')}
	</div>

	<div class="check">
		<IconCheckCircleFill />
		{I18n.t('cancelAnytime')}
	</div>
</div>

<style>
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
		}
		.check {
			width: 250px;
			max-width: 100%;
		}
	}
</style>
