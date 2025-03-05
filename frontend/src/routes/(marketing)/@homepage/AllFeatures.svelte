<script lang="ts">
	// import customizeImg from '$lib/img/features/customizable.svg';
	// import embedImg from '$lib/img/features/embed.svg';
	// import moderationImg from '$lib/img/features/moderation.svg';
	// import privacyImg from '$lib/img/features/privacy i.svg';
	// import ssoImg from '$lib/img/features/sso.svg';
	// import dataImg from '$lib/img/features/data ownership.svg';
	import easyInstallImg from '$lib/img/easy-install.svg';
	import embedNewsletterImg from '$lib/img/embed-newsletter-form.svg';
	import multipleSegmentsImg from '$lib/img/multiple-segments.svg';
	import comprehensiveAnalyticsImg from '$lib/img/comprehensive-analytics.svg';
	import customDomainImg from '$lib/img/custom-domain.svg';
	import customTemplatesImg from '$lib/img/custom-templates.svg';

	const features = [
		{
			title: 'Easy Installation',
			description:
				'Seamlessly integrate embeds on any CMS or static site with our plugins or simple installation code.',
			image: easyInstallImg
		},

		{
			title: 'Embeddable Newsletter Form',
			description:
				'Embeddable, customizable newsletter form to collect emails from your users on your website.',
			image: embedNewsletterImg
		},

		{
			title: 'Multiple Segments',
			description:
				'Create multiple segments to organize your subscribers or create multiple lists. Ex: Monthly, Weekly, etc.',
			image: multipleSegmentsImg
		},

		{
			title: 'Comprehensive Analytics',
			description: 'Send engaging newsletters to your subscribers. Track opens, clicks, and more.',
			image: comprehensiveAnalyticsImg
		},
		{
			title: 'Custom Email Domain',
			description:
				'Boost credibility and brand recognition by sending newsletters from your own domain.',
			image: customDomainImg
		},

		{
			title: 'Customizable Templates',
			description: 'Choose from a variety of templates and customize them to match your brand.',
			image: customTemplatesImg
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

<style lang="scss">
	.feature-wrapper {
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
	}

	// media queries
	@media (max-width: 992px) {
		.feature-wrapper {
			grid-template-columns: 1fr;
		}
	}
</style>
