<script lang="ts">
	import IconDatabase from '@hyvor/icons/IconDatabase';
	import IconSend from '@hyvor/icons/IconSend';
	// import IconController from '@hyvor/icons/IconController';
	import Feature from './Feature.svelte';
	// import IconCardText from '@hyvor/icons/IconCardText';
	import IconLayers from '@hyvor/icons/IconLayers';
	// import migrationSupImg from '../../../../static/img/migration.svg';
</script>

<div class="above-fold" id="seo-section">
	<div class="title">
		<h1>Developer Friendly</h1>
		<h2>
			Everything you need to build custom solutions and integrate with your existing tools,
			especially headless blogging.
		</h2>
	</div>

	<div class="content">
		<div class="item-card">
			<!--            Data API-->
			<Feature
				icon={IconDatabase}
				title="API"
				description="Integrate with your existing systems using our API"
				color="accent"
			/>
		</div>

		<div class="item-card">
			<!--            Webhooks-->
			<Feature
				icon={IconSend}
				title="Migration Assistance"
				description="Busy to handle migration? We can help you migrate your existing subscribers"
				color="accent"
			/>
		</div>

		<div class="item-card">
			<!--            Delivery API-->
			<Feature
				icon={IconLayers}
				title="Webhooks"
				description="Get real-time notifications about important events via webhooks."
				color="accent"
			/>
		</div>
	</div>
</div>

<style lang="scss">
	:global(body) {
		overflow-x: hidden;
	}

	.above-fold {
		display: flex;
		padding-top: 75px;
		flex-direction: column;
		background-color: var(--accent);
		padding-right: 50px;
		padding-left: 50px;
	}

	.title {
		text-align: start;
		margin-bottom: 40px;
	}

	.title h1 {
		font-size: 60px !important;
		margin: 0 !important;
		color: rgb(0, 0, 0);
	}

	.title h2 {
		font-size: 20px !important;
		//margin: 0 !important;
		font-weight: normal;
		// color: var(--gray-light) !important;
	}

	.item-card {
		background: white;
		border: 1px solid var(--gray-light);
		border-radius: 20px;
		padding: 20px;
		width: 400px;
		max-width: 100%;
		box-shadow: var(--box-shadow);
		margin-bottom: 20px;
		color: black;
	}

	.content {
		color: var(--gray-light);
		padding-bottom: 100px;
		display: grid;
		grid-template-columns: repeat(6, 1fr); /* Use 6 columns for better control */
		gap: 20px;
		margin: auto;
	}

	/* First row - 3 cards spanning 2 columns each */
	.item-card:nth-child(1) {
		grid-column: 1 / 3;
	}
	.item-card:nth-child(2) {
		grid-column: 3 / 5;
	}
	.item-card:nth-child(3) {
		grid-column: 5 / 7;
	}

	/* Second row - 2 cards centered */
	.item-card:nth-child(4) {
		grid-column: 2 / 4;
	} /* Console API */
	.item-card:nth-child(5) {
		grid-column: 4 / 6;
	} /* Headless CMS */

	/* Mobile responsiveness */
	@media (max-width: 768px) {
		.above-fold {
			padding-right: 20px;
			padding-left: 20px;
			padding-top: 40px;
		}

		.title {
			text-align: center;
			margin-bottom: 30px;
		}

		.title h1 {
			font-size: 40px !important;
		}

		.title h2 {
			font-size: 18px !important;
			padding: 0 10px;
		}

		.content {
			display: flex;
			flex-direction: column;
			gap: 15px;
			padding-bottom: 60px;
		}

		.item-card {
			width: 100%;
			max-width: none;
			margin-bottom: 0;
			padding: 15px;
		}

		/* Reset all grid positioning for mobile */
		.item-card:nth-child(1),
		.item-card:nth-child(2),
		.item-card:nth-child(3),
		.item-card:nth-child(4),
		.item-card:nth-child(5) {
			grid-column: auto;
		}
	}

	/* Tablet breakpoint for better responsive design */
	@media (max-width: 1024px) and (min-width: 769px) {
		.above-fold {
			padding-right: 30px;
			padding-left: 30px;
		}

		.content {
			grid-template-columns: repeat(4, 1fr);
			gap: 15px;
		}

		.item-card {
			width: auto;
		}

		/* Adjust grid layout for tablet */
		.item-card:nth-child(1) {
			grid-column: 1 / 3;
		}
		.item-card:nth-child(2) {
			grid-column: 3 / 5;
		}
		.item-card:nth-child(3) {
			grid-column: 1 / 3;
		}
		.item-card:nth-child(4) {
			grid-column: 3 / 5;
		}
		.item-card:nth-child(5) {
			grid-column: 2 / 4;
		}
	}

	/* Small mobile devices */
	@media (max-width: 480px) {
		.above-fold {
			padding-right: 15px;
			padding-left: 15px;
			padding-top: 30px;
		}

		.title h1 {
			font-size: 32px !important;
		}

		.title h2 {
			font-size: 16px !important;
		}

		.item-card {
			padding: 12px;
			border-radius: 15px;
		}

		.content {
			gap: 12px;
			padding-bottom: 40px;
		}
	}
</style>
