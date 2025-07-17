<script lang="ts">
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
</script>

<div class="wrap hds-container">
	<FeatureSectionHead
		title={I18n.t('pricing.priceCompare')}
		wrapStyle="margin-top:50px"
		subtitle={I18n.t('pricing.priceCompareDescription')}
	/>
	<div class="hds-container">
		<div class="graph-container">
			{#each prices as { name, price, color }}
				<div class="row">
					<div class="name">
						{name}
					</div>
					<div class="bar-container">
						<div
							class="bar"
							style="width:  {(price / maxPrice) * 100 +
								10}%; background-color: {color};"
						>
							<div class="price">€{price}</div>
						</div>
					</div>
				</div>
			{/each}
		</div>
	</div>
</div>

<style lang="scss">
	.wrap {
		padding-top: 40px;
		// padding-bottom: 40px;
	}
	.graph-container {
		max-width: 640px;
		margin: 2rem auto;
		display: flex;
		flex-direction: column;
		gap: 1rem;
		font-family: system-ui, sans-serif;
		padding: 0 1rem;
	}

	.row {
		display: flex;
		flex-direction: row;
		align-items: center;
		// gap: 1rem;
	}

	.name {
		width: 160px;
		display: flex;
		align-items: center;
		gap: 0.5rem;
		font-weight: 600;
		font-size: 1rem;
	}

	.bar-container {
		flex: 1;
		// background-color: #e5e7eb; /* light gray background to visualize bar space */
		border-radius: 20px;
		overflow: hidden;
		height: 30px;
		position: relative;
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
	}

	/* Mobile styles */
	@media (max-width: 640px) {
		.row {
			flex-direction: column;
			align-items: flex-start;
		}

		.name {
			width: auto;
			font-size: 0.95rem;
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
