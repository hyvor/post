<!-- <script lang="ts">
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
</style> -->

<!-- <script lang="ts">
	import { onMount } from 'svelte';
	import { ButtonGroup, Label, Button, Slider } from '@hyvor/design/components';
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

	const sliderOptions: VolumeKey[] = ['25k', '100k', '1M'];

	// --- State ---
	let sliderVal: 0 | 1 | 2 = 0;
	let selectedVolume: VolumeKey = sliderOptions[sliderVal];

	let prices: PriceItem[] = priceData[selectedVolume];
	let maxPrice = Math.max(...prices.map((p) => p.price));

	let animatedPrices = prices.map(() => 0);
	let animatedWidths = prices.map(() => 0);

	// --- Easing ---
	function easeOutCubic(t: number) {
		return 1 - Math.pow(1 - t, 3);
	}

	// --- Animate graph ---
	function animateGraph(newPrices: PriceItem[]) {
		prices = newPrices;
		maxPrice = Math.max(...prices.map((p) => p.price));

		animatedPrices = prices.map(() => 0);
		animatedWidths = prices.map(() => 0);

		prices.forEach((p, i) => {
			const duration = 1200;
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
	}

	// --- Slider change handler ---
	function onSliderChange(event: any) {
		sliderVal = event.detail as 0 | 1 | 2;
		selectedVolume = sliderOptions[sliderVal];
		animateGraph(priceData[selectedVolume]);
	}

	// --- Init ---
	onMount(() => {
		animateGraph(prices);
	});
</script>

<div class="wrap hds-container">
	<FeatureSectionHead
		title={I18n.t('pricing.priceCompare')}
		wrapStyle="margin-top:50px; padding-bottom: 20px;"
	/>

	
	<div class="slider-wrap">
		<div class="label-wrap" style="margin: 0 0 12px 0; text-align: center;">
			<Label for="emailSlider">Emails per Month</Label>
		</div>
		<Slider min={0} max={2} step={1} value={sliderVal} on:change={onSliderChange} />
		<div class="slider-labels">
			{#each sliderOptions as option, idx}
				<span class={sliderVal === idx ? 'active' : ''}>{option}</span>
			{/each}
		</div>
		<ButtonGroup>
			<Button on:click={() => (sliderVal = 0)}>25k</Button>
			<Button on:click={() => (sliderVal = 1)}>100k</Button>
			<Button on:click={() => (sliderVal = 2)}>1M</Button>
		</ButtonGroup>
	</div>

	
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

	<div class="disclaimers">
		<div>{I18n.t('pricing.pricesAsOfJuly2025')}</div>
	</div>
</div>

<style lang="scss">
	.wrap {
		padding-top: 100px;
	}

	/* Slider styles */
	.slider-wrap {
		display: flex;
		flex-direction: column;
		gap: 0.5rem;
		width: 400px;
		margin: 2rem auto;
	}
	.slider-labels {
		display: flex;
		justify-content: space-between;
		font-size: 14px;
		margin-top: 4px;
	}
	.slider-labels .active {
		font-weight: bold;
		color: #729290;
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
</style> -->

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
	function animateGraph(newPrices: PriceItem[]) {
		prices = newPrices;
		maxPrice = Math.max(...prices.map((p) => p.price));

		animatedPrices = prices.map(() => 0);
		animatedWidths = prices.map(() => 0);

		prices.forEach((p, i) => {
			const duration = 1200;
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
