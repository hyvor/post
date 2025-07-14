<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	const features = [
		{
			title: I18n.t('customizableTemplates'),
			description: I18n.t('customizableTemplatesDescription'),
			image: '/img/custom-templates.svg'
		},
		{
			title: I18n.t('embeddableNewsletterForm'),
			description: I18n.t('embeddableNewsletterFormDescription'),
			image: '/img/embed-newsletter-form.svg'
		},
		{
			title: I18n.t('listMngmt'),
			description: I18n.t('listMngmtDescription'),
			image: '/img/multiple-segments.svg'
		},
		{
			title: I18n.t('customSendingDomain'),
			description: I18n.t('customSendingDomainDescription'),
			image: '/img/custom-domain.svg'
		},
		{
			title: I18n.t('apiWebhooks'),
			description: I18n.t('apiWebhooksDescription'),
			image: '/img/api-webhooks.svg'
		},
		{
			title: I18n.t('teamCollaboration'),
			description: I18n.t('teamCollaborationDescription'),
			image: '/img/team-collaboration.svg'
		},
		{
			title: I18n.t('automation'),
			description: I18n.t('automationDescription'),
			image: '/img/automation.svg'
		},
		{
			title: I18n.t('migration'),
			description: I18n.t('migrationDescription'),
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
