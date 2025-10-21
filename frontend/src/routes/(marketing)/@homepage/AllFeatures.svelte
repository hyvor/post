<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	const features = [
		{
			title: I18n.t('homepage.listMngmt'),
			description: I18n.t('homepage.listMngmtDescription'),
			image: '/img/multiple-segments.svg'
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
			title: I18n.t('homepage.migration'),
			description: I18n.t('homepage.migrationDescription'),
			image: '/img/migration.svg'
		}
	];
</script>

<div class="outer-box">
	<div class="feature-wrapper hds-container-max">
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
</div>

<style>
	.outer-box {
		background-color: var(--accent-light);
		/* padding: 60px 0; */
	}
	.feature-wrapper {
		margin: 70px 40px;
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 2rem;
		/* width: auto; */
		max-width: 100%;
	}

	.feature {
		padding: 2rem 1.5rem;
		transition: all 0.3s ease;
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		position: relative;
		overflow: hidden;
	}

	.feature:hover {
		transform: translateY(-8px);
		box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
		border-color: rgba(102, 126, 234, 0.2);
	}

	.feature:nth-child(2)::before {
		background: var(--red);
	}

	.feature:nth-child(3)::before {
		background: var(--orange);
	}

	.feature:nth-child(4)::before {
		background: var(--blue);
	}

	.feature-image {
		width: 140px;
		height: 140px;
		margin-bottom: 1.5rem;
		display: flex;
		align-items: center;
		justify-content: center;
		transition: transform 0.3s ease;
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

	/* for larger screens */
	@media (min-width: 1200px) {
		.outer-box {
			padding: 100px 0;
		}
		.feature-wrapper {
			margin: auto;
		}
	}
</style>
