<script lang="ts">
	import { onMount } from 'svelte';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';

	const prices = [
		{ name: 'Hyvor Post', price: 35, color: '#729290' },
		{ name: 'Brevo', price: 68, color: '#767676' },
		{ name: 'Beehiv', price: 109, color: '#767676' },
		{ name: 'Kit', price: 119, color: '#767676' },
		{ name: 'Mailchimp', price: 266, color: '#767676' }
	];

	const maxPrice = Math.max(...prices.map((p) => p.price));
	const I18n = getMarketingI18n();

	let animatedPrices = prices.map(() => 20);
	let animatedWidths = prices.map(() => 20);

	function easeOutCubic(t: number) {
		return 1 - Math.pow(1 - t, 3);
	}

	onMount(() => {
		prices.forEach((p, i) => {
			const duration = 1200; // slightly slower for smoother effect
			const start = performance.now();

			function animate(now: number) {
				const rawProgress = Math.min((now - start) / duration, 1);
				const progress = easeOutCubic(rawProgress);

				animatedPrices[i] = Math.floor(progress * p.price);
				animatedWidths[i] = progress * ((p.price / maxPrice) * 100 + 10);

				if (rawProgress < 1) requestAnimationFrame(animate);
			}

			requestAnimationFrame(animate);
		});
	});
</script>

<div class="wrap hds-container">
	<FeatureSectionHead
		title={I18n.t('pricing.priceCompare')}
		wrapStyle="margin-top:50px; padding-bottom: 20px;"
		subtitle={I18n.t('pricing.priceCompareDescription')}
	/>

	<div class="hds-container">
		<div class="graph-container">
			{#each prices as { name, price, color }, i}
				<div class="row">
					<div class="name">{name}</div>
					<div class="bar-container">
						<div
							class="bar"
							style="width: {animatedWidths[i]}%; background-color: {color};"
						>
							<div class="price">€{animatedPrices[i]}</div>
						</div>
					</div>
				</div>
			{/each}
		</div>
	</div>

	<div class="disclaimers">
		<div>{I18n.t('pricing.pricesAsOfJuly2025')}</div>
	</div>
</div>

<style lang="scss">
	.wrap {
		padding-top: 40px;
	}
	.graph-container {
		max-width: 640px;
		margin: 2rem auto;
		display: flex;
		flex-direction: column;
		gap: 1.5rem;
		font-family: system-ui, sans-serif;
		padding: 0 1rem;
	}

	.row {
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	.name {
		width: 160px;
		display: flex;
		align-items: center;
		gap: 0.5rem;
		font-weight: 600;
		font-size: 1.3rem;
	}

	.bar-container {
		flex: 1;
		border-radius: 20px;
		overflow: hidden;
		height: 40px;
		position: relative;
		background-color: #f0f0f000;
	}

	.bar {
		height: 100%;
		border-radius: 20px;
		display: flex;
		align-items: center;
		padding-left: 12px;
		color: white;
		font-weight: 600;
		font-size: 0.9rem;
		transition: width 0.3s ease-out;
	}

	.price {
		min-width: 40px;
	}

	.disclaimers {
		text-align: center;
		color: var(--text-light);
		font-size: 0.9rem;
		margin-top: 1rem;
	}

	.wrap :global(h2) {
		font-size: 30px;
	}

	@media (max-width: 640px) {
		.row {
			flex-direction: column;
			align-items: flex-start;
		}

		.name {
			width: auto;
			font-size: 0.95rem;
			margin-bottom: 0.5rem;
		}

		.bar-container {
			width: 100%;
			border-radius: 20px !important;
		}

		.bar {
			height: 36px;
			border-radius: 20px !important;
		}
	}
</style>
