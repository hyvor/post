<script lang="ts">
	import { getMarketingI18n } from "../locale";

	const I18n = getMarketingI18n();

	const features = [
		{
			title: I18n.t('easyInstallation'),
			description:
				I18n.t('easyInstallationDescription'),
			image: '/img/easy-install.svg'
		},

		{
			title: I18n.t('embeddableNewsletterForm'),
			description:
				I18n.t('embeddableNewsletterFormDescription'),
			image: '/img/embed-newsletter-form.svg'
		},

		{
			title: I18n.t('multipleSegments'),
			description:
				I18n.t('multipleSegmentsDescription'),
			image: '/img/multiple-segments.svg'
		},

		{
			title: I18n.t('comprehensiveAnalytics'),
			description: 
				I18n.t('comprehensiveAnalyticsDescription'),
			image: '/img/comprehensive-analytics.svg'
		},
		{
			title: I18n.t('customEmailDomain'),
			description:
				I18n.t('customEmailDomainDescription'),
			image: '/img/custom-domain.svg'
		},

		{
			title: I18n.t('customizableTemplates'),
			description: 
				I18n.t('customizableTemplatesDescription'),
			image: '/img/custom-templates.svg'
		}
	];
</script>

<div class="feature-wrapper hds-container">
	{#each features as feature}
		<div class="feature">
			<div class="feature-image">
				<img src={feature.image} alt={feature.title} width={180} height={140} />
			</div>
			<div class="feature-content">
				<div class="title">{feature.title}</div>
				<div class="desc">{feature.description}</div>
			</div>
		</div>
	{/each}
</div>

<style>
	.feature-wrapper {
		margin: 140px auto;
		display: grid;
		grid-template-columns: repeat(2, minmax(300px, 1fr));
		gap: 2.4rem;
	}

	.feature {
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
	}

	.feature-content {
		width: 70%;
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.title {
		font-size: 1.2rem;
		font-weight: 600;
	}

	.desc {
		margin-top: 10px;
		font-size: 1rem;
		color: var(--text-light);
		width: 100%;
	}

	@media (max-width: 992px) {
		.feature-wrapper {
			grid-template-columns: 1fr;
		}
	}
</style>
