<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	const features = [
		{
			title: I18n.t('homepage.customizableTemplates'),
			description: I18n.t('homepage.customizableTemplatesDescription'),
			image: '/img/custom-templates.svg'
		},
		{
			title: I18n.t('homepage.embeddableNewsletterForm'),
			description: I18n.t('homepage.embeddableNewsletterFormDescription'),
			image: '/img/embed-newsletter-form.svg'
		},
		{
			title: I18n.t('homepage.listMngmt'),
			description: I18n.t('homepage.listMngmtDescription'),
			image: '/img/multiple-segments.svg'
		},
		{
			title: I18n.t('homepage.customSendingDomain'),
			description: I18n.t('homepage.customSendingDomainDescription'),
			image: '/img/custom-domain.svg'
		},
		{
			title: I18n.t('homepage.apiWebhooks'),
			description: I18n.t('homepage.apiWebhooksDescription'),
			image: '/img/api-webhooks.svg'
		},
		{
			title: I18n.t('homepage.teamCollaboration'),
			description: I18n.t('homepage.teamCollaborationDescription'),
			image: '/img/team-collaboration.svg'
		},
		{
			title: I18n.t('homepage.automation'),
			description: I18n.t('homepage.automationDescription'),
			image: '/img/automation.svg'
		},
		{
			title: I18n.t('homepage.migration'),
			description: I18n.t('homepage.migrationDescription'),
			image: '/img/migration.svg'
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
		margin: 50px auto;
		display: grid;
		grid-template-columns: repeat(2, minmax(300px, 1fr));
		gap: 45px;
	}

	.feature {
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		gap: 15px;
	}

	.feature-content {
		width: 70%;
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.title {
		font-size: 20px;
		font-weight: 600;
	}

	.desc {
		margin-top: 10px;
		font-size: 17px;
		color: var(--text-light);
		width: 100%;
	}

	@media (max-width: 992px) {
		.feature-wrapper {
			grid-template-columns: 1fr;
		}
	}
</style>
