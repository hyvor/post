<script lang="ts">
	import { onMount } from 'svelte';
	import { ButtonGroup, Button } from '@hyvor/design/components';
	import FeatureSectionHead from '../@homepage/FeatureSectionHead.svelte';
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();

	// --- Types ---
	type VolumeKey = '25k' | '100k' | '1M';

	interface PriceItem {
		name: string;
		price: number;
		color: string;
	}

	// --- Price data per volume ---
	const priceData: Record<VolumeKey, PriceItem[]> = {
		'25k': [
			{ name: 'Hyvor Post', price: 10, color: '#729290' },
			{ name: 'EmailOctopus', price: 13, color: '#767676' },
			{ name: 'Sender', price: 16, color: '#767676' },
			{ name: 'Mailchimp', price: 39, color: '#767676' },
			{ name: 'Brevo', price: 43, color: '#767676' }
		],
		'100k': [
			{ name: 'Hyvor Post', price: 35, color: '#729290' },
			{ name: 'EmailOctopus', price: 36, color: '#767676' },
			{ name: 'Sender', price: 49, color: '#767676' },
			{ name: 'Mailchimp', price: 95, color: '#767676' },
			{ name: 'Brevo', price: 119, color: '#767676' }
		],
		'1M': [
			{ name: 'Hyvor Post', price: 225, color: '#729290' },
			{ name: 'EmailOctopus', price: 286, color: '#767676' },
			{ name: 'Sender', price: 316, color: '#767676' },
			{ name: 'Mailchimp', price: 547, color: '#767676' },
			{ name: 'Brevo', price: 629, color: '#767676' }
		]
	};

	const volumeOptions: VolumeKey[] = ['25k', '100k', '1M'];

	// price states
	let selectedVolume: VolumeKey = '25k';
	let prices: PriceItem[] = priceData[selectedVolume];
	let maxPrice = Math.max(...prices.map((p) => p.price));

	let animatedPrices = prices.map(() => 0);
	let animatedWidths = prices.map(() => 0);

	//animation
	function easeOutCubic(t: number) {
		return 1 - Math.pow(1 - t, 3);
	}

	// animate graph
	// function animateGraph(newPrices: PriceItem[]) {
	// 	prices = newPrices;
	// 	maxPrice = Math.max(...prices.map((p) => p.price));

	// 	animatedPrices = prices.map(() => 0);
	// 	animatedWidths = prices.map(() => 0);

	// 	prices.forEach((p, i) => {
	// 		const duration = 1200;
	// 		const start = performance.now();

	// 		function animate(now: number) {
	// 			const rawProgress = Math.min((now - start) / duration, 1);
	// 			const progress = easeOutCubic(rawProgress);

	// 			animatedPrices[i] = Math.floor(progress * p.price);
	// 			animatedWidths[i] = progress * ((p.price / maxPrice) * 100 + 10);

	// 			if (rawProgress < 1) requestAnimationFrame(animate);
	// 		}
	// 		requestAnimationFrame(animate);
	// 	});
	// }
	function animateGraph(newPrices: PriceItem[]) {
		prices = newPrices;
		maxPrice = Math.max(...prices.map((p) => p.price));

		// Keep current states instead of resetting to 0
		const startPrices = [...animatedPrices];
		const startWidths = [...animatedWidths];

		// If they don't exist yet (on first mount), set them to 0
		for (let i = 0; i < prices.length; i++) {
			if (isNaN(startPrices[i])) startPrices[i] = 0;
			if (isNaN(startWidths[i])) startWidths[i] = 0;
		}

		prices.forEach((p, i) => {
			const duration = 1200;
			const start = performance.now();

			// Calculate target widths for each bar
			const targetWidth = (p.price / maxPrice) * 100;

			function animate(now: number) {
				const rawProgress = Math.min((now - start) / duration, 1);
				const progress = easeOutCubic(rawProgress);

				// Interpolate from current to target value
				animatedPrices[i] = Math.floor(
					startPrices[i] + (p.price - startPrices[i]) * progress
				);

				animatedWidths[i] = startWidths[i] + (targetWidth - startWidths[i]) * progress;

				if (rawProgress < 1) requestAnimationFrame(animate);
			}
			requestAnimationFrame(animate);
		});
	}

	// Button click handling
	function selectVolume(volume: VolumeKey) {
		selectedVolume = volume;
		animateGraph(priceData[volume]);
	}

	// Init
	onMount(() => {
		animateGraph(prices);
	});
</script>

<div class="wrap hds-container">
	<FeatureSectionHead
		title={I18n.t('pricing.priceCompare')}
		subtitle="Price per {selectedVolume} emails/month"
		wrapStyle="margin-top:50px; padding-bottom: 20px;"
	/>

	<!-- Button Group -->
	<div class="button-wrap">
		<ButtonGroup>
			{#each volumeOptions as option}
				<Button
					variant={selectedVolume === option ? 'fill' : 'outline'}
					on:click={() => selectVolume(option)}
				>
					{option}
				</Button>
			{/each}
		</ButtonGroup>
	</div>

	<!-- Graph -->
	<div class="graph-container">
		{#each prices as { name, color }, i}
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

	<!-- Disclaimer -->
	<div class="disclaimers">
		<div>{I18n.t('pricing.pricesAsOfJuly2025')}</div>
	</div>
</div>

<style lang="scss">
	.wrap {
		padding-top: 80px;
	}

	.button-wrap {
		display: flex;
		justify-content: center;
		margin: 1.5rem 0 2rem 0;
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
		padding-inline-start: 2px;
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
</style>
