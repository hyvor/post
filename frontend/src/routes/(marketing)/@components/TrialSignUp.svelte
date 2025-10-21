<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { getMarketingI18n } from '../locale';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import TrialLink from './TrialLink.svelte';

	const I18n = getMarketingI18n();

	interface Props {
		title?: string;
		[key: string]: any;
	}

	let { title = I18n.t('homepage.readyToStartYourNewsletter'), ...rest }: Props = $props();
	// let { title = I18n.t('comingSoon.readytoExplore'), ...rest }: Props = $props();
</script>

<div class="wrap" {...rest}>
	<div class="hds-container-max inner">
		<div class="text">
			{title}
		</div>
		<div class="button">
			<Button as="a" size="x-large" href="/console?signup"
				>{I18n.t('homepage.startYourNewsletter')}</Button
			>
			<!-- <Button as="a" size="x-large" href={`/#waitlist`}
				>{I18n.t('comingSoon.joinWaitlist')}</Button
			> -->
		</div>

		<div class="checks" {...rest}>
			<div class="check">
				<IconCheckCircleFill color="#fffff" />
				<!-- 14-day free trial -->
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
				No credit card required
			</div>

			<div class="check">
				<IconCheckCircleFill />
				Cancel anytime
			</div>
		</div>
	</div>
</div>

<!-- 
<style>
	.wrap {
		background-color: var(--accent-light);
		padding: 80px 15px;
		margin: 60px 0;
	}

	.inner {
		
		max-width: 100%;
		margin: auto;
	}

	h3 {
		text-align: center;
		font-size: 25px;
		font-weight: 600;
	}

	.button-wrap {
		padding: 20px;
		text-align: center;
	}

	.trial-checks {
		display: flex;
		gap: 20px;
		justify-content: center;
		margin-top: 50px;
		flex-direction: row;
	}

	.trial-checks :global(.checks) {
		flex-direction: row;
		gap: 15px !important;
	}
</style> -->
<style>
	.wrap {
		margin: 60px 0;
		padding: 60px 20px;
		background-color: var(--accent-light);
	}

	.text {
		text-align: center;
		margin-bottom: 50px;
		font-size: 30px;
		font-weight: 600;
	}

	.button {
		display: flex;
		justify-content: center;
		/* margin-top: 50px; */
	}
	.checks {
		display: flex;
		gap: 20px;
		justify-content: center;
		margin-top: 50px;
		flex-direction: row;
	}
	.check {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-bottom: 6px;
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
			text-align: center;
		}
	}
</style>
