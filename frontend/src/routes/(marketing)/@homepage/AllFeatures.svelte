<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	const features = [
		// {
		// 	title: I18n.t('homepage.embeddableNewsletterForm'),
		// 	description: I18n.t('homepage.embeddableNewsletterFormDescription'),
		// 	image: '/img/embed-newsletter-form.svg'
		// },
		// {
		// 	title: I18n.t('homepage.customizableTemplates'),
		// 	description: I18n.t('homepage.customizableTemplatesDescription'),
		// 	image: '/img/custom-templates.svg'
		// },
		{
			title: I18n.t('homepage.listMngmt'),
			description: I18n.t('homepage.listMngmtDescription'),
			image: '/img/multiple-segments.svg'
		},
		// {
		// 	title: I18n.t('homepage.customSendingDomain'),
		// 	description: I18n.t('homepage.customSendingDomainDescription'),
		// 	image: '/img/custom-domain.svg'
		// },
		// {
		// 	title: I18n.t('homepage.apiWebhooks'),
		// 	description: I18n.t('homepage.apiWebhooksDescription'),
		// 	image: '/img/api-webhooks.svg'
		// },
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
		<div class="feature hds-box">
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

<!-- <style>
	.feature-wrapper {
		margin: 70px auto;
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

-->

<style>
	.feature-wrapper {
		margin: 70px 50px;
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 2rem;
		/* padding: 0 1rem; */
		width: auto;
	}

	.feature {
		background: white;
		border-radius: 16px;
		padding: 2rem 1.5rem;
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
		transition: all 0.3s ease;
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		border: 1px solid rgba(0, 0, 0, 0.05);
		position: relative;
		overflow: hidden;
	}

	.feature::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
	}

	.feature:hover {
		transform: translateY(-8px);
		box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
		border-color: rgba(102, 126, 234, 0.2);
	}

	.feature:nth-child(2)::before {
		background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
	}

	.feature:nth-child(3)::before {
		background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
	}

	.feature:nth-child(4)::before {
		background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
	}

	.feature-image {
		width: 140px;
		height: 140px;
		margin-bottom: 1.5rem;
		display: flex;
		align-items: center;
		justify-content: center;
		/* background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); */
		/* border-radius: 12px; */
		transition: transform 0.3s ease;
		/* padding: 1rem; */
	}

	.feature:hover .feature-image {
		transform: scale(1.05);
	}

	.feature-image img {
		max-width: 100%;
		max-height: 100%;
		object-fit: contain;
	}

	.feature-content {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		width: 100%;
	}

	.title {
		font-size: 1.25rem;
		font-weight: 600;
		margin-bottom: 1rem;
		color: #2c3e50;
		line-height: 1.3;
	}

	.desc {
		font-size: 1rem;
		color: var(--text-light, #666);
		line-height: 1.6;
		width: 100%;
		margin: 0;
	}

	@media (max-width: 768px) {
		.feature-wrapper {
			grid-template-columns: 1fr;
			gap: 1.5rem;
			margin: 2rem auto;
		}

		.feature {
			padding: 1.5rem 1rem;
		}

		.feature-image {
			width: 120px;
			height: 120px;
		}

		.title {
			font-size: 1.1rem;
		}

		.desc {
			font-size: 0.9rem;
		}
	}

	@media (max-width: 480px) {
		.feature-wrapper {
			padding: 0 0.5rem;
		}

		.feature {
			padding: 1.25rem 0.75rem;
		}

		.feature-image {
			width: 100px;
			height: 100px;
			margin-bottom: 1rem;
		}
	}
</style>
