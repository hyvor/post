<script lang="ts">

	const features = [
		{
			title: 'Easy Installation',
			description:
				'Seamlessly integrate embeds on any CMS or static site with our plugins or simple installation code.',
			image: '/img/easy-install.svg'
		},

		{
			title: 'Embeddable Newsletter Form',
			description:
				'Embeddable, customizable newsletter form to collect emails from your users on your website.',
			image: '/img/embed-newsletter-form.svg'
		},

		{
			title: 'Multiple Lists',
			description:
				'Create multiple lists to organize your subscribers.',
			image: '/img/multiple-segments.svg'
		},

		{
			title: 'Comprehensive Analytics',
			description: 'Send engaging newsletters to your subscribers. Track opens, clicks, and more.',
			image: '/img/comprehensive-analytics.svg'
		},
		{
			title: 'Custom Email Domain',
			description:
				'Boost credibility and brand recognition by sending newsletters from your own domain.',
			image: '/img/custom-domain.svg'
		},

		{
			title: 'Customizable Templates',
			description: 'Choose from a variety of templates and customize them to match your brand.',
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
