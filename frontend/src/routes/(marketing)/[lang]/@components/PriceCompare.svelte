<script lang="ts">
    import FeatureSectionHead from "../@homepage/FeatureSectionHead.svelte";

	const prices = [
		{ name: 'Hyvor Post', price: 35, color: '#729290' },
    { name: 'Brevo', price: 68, color: '#df6c64' },
		{ name: 'Beehiv', price: 109, color: '#df6c64' },
    { name: 'Kit', price: 119, color: '#df6c64' },
		{ name: 'Mailchimp', price: 266, color: '#df6c64' }
	];

	const maxPrice = Math.max(...prices.map(p => p.price));
</script>


<div class="wrap hds-container">
  <FeatureSectionHead
    title="Hyvor Post vs Other Platforms"
    wrapStyle="margin-top:50px"
    subtitle="Price for 100,000 emails per month"
/>
<div class="hds-container">
  <!-- <div class="graph-container">
    {#each prices as { name, price, color }}
      <div class="row">
        <div class="name">{name}</div>
        <div class="bar-container">
          <div
            class="bar"
            style="width: {((price / maxPrice) * 100) + 10}%; background-color: {color};"
          >
            <div class="price">${price}</div>
          </div>
        </div>
        
      </div>
    {/each}
  </div> -->
  <div class="graph-container">
    {#each prices as { name, price, color }}
      <div class="row">
        <div class="name">
          <!-- <img class="logo" src={logo} alt={name + ' logo'} /> -->
          {name}
        </div>
        <div class="bar-container">
          <div
            class="bar"
            style="width:  {((price / maxPrice) * 100) + 10}%; background-color: {color};"
          >
            <div class="price">€{price}</div>
          </div>
        </div>
      </div>
    {/each}
  </div>
</div>
</div>


<!-- <style lang="scss">
	.graph-container {
		max-width: 640px;
		margin: 2rem auto;
		display: flex;
		flex-direction: column;
		gap: 1.25rem;
	}

	.row {
		display: flex;
		align-items: center;
		gap: 1rem;
	}

	.name {
		width: 120px;
		font-weight: 600;
	}

	.bar-container {
		flex: 1;
		position: relative;
		height: 36px;
	}

	.bar {
		height: 100%;
		border-radius: 5px;
		display: inline-block;
		position: absolute;
		left: 0;
		top: 0;
	}

	.price {
		position: relative;
		left: 12px;
		top: 50%; 
		transform: translateY(-50%);
		font-weight: 600;
		color: rgb(255, 255, 255);
	}

  /* mobile */
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
	}
}
</style> -->

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

// .logo {
// 	width: 24px;
// 	height: 24px;
// 	object-fit: contain;
// }

.bar-container {
	flex: 1;
	// background-color: #e5e7eb; /* light gray background to visualize bar space */
	border-radius: 20px;
	overflow: hidden;
	height: 36px;
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
	font-size: 0.95rem;
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