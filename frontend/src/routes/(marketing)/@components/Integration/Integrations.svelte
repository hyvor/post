<script lang="ts">
	import { categories } from './integrations';
</script>

<div class="items-wrap hds-container">
	<div class="items">
		{#each categories as category}
			<!-- <div role="heading" aria-level="2" class="category-name">{category.title}</div> -->
			<div class="cards-wrap">
				{#each category.integrations as integration}
					<a class="integration hds-box" href={integration.url}>
						<div class="overlay" style:background-color={integration.color}></div>
						<img
							src={integration.logo}
							style="max-width: 50px; max-height:50px;"
							alt={integration.name}
						/>
						<div class="name-wrapper">
							<div role="heading" aria-level="3" class="name">{integration.name}</div>
							{#if integration.subname}
								<div class="subname" role="heading" aria-level="4">
									{integration.subname}
								</div>
							{/if}
						</div>
					</a>
				{/each}
			</div>
		{/each}
	</div>
</div>

<style lang="scss">
	.items-wrap {
		margin-top: 50px;
		display: flex;
		gap: 10px;
	}

	.items {
		flex: 1;
		overflow: auto;
	}

	.category-name {
		font-weight: 600;
		font-size: 18px;
		margin-bottom: 15px;
	}

	.cards-wrap {
		display: grid;

		grid-template-columns: repeat(5, 1fr);
		gap: 15px;
		margin-bottom: 30px;
	}

	.integration {
		padding: 20px 15px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		gap: 12px;
		text-align: center;
		box-shadow: none;
		cursor: pointer;
		transition: 0.3s transform;
		background-color: transparent;
		position: relative;
		overflow: hidden;

		.name {
			font-size: 14px;
		}
		&:hover {
			transform: translateY(-5px);
		}

		.subname {
			font-size: 13px;
			color: var(--text-light);
			// margin-top: -10px;
		}

		.overlay {
			position: absolute;
			width: 100%;
			height: 100%;
			top: 0;
			left: 0;
			z-index: -1;
			opacity: 0.3;
		}
	}

	// media queries
	@media (max-width: 992px) {
		.cards-wrap {
			grid-template-columns: repeat(4, 1fr);
		}
	}

	@media (max-width: 768px) {
		.cards-wrap {
			grid-template-columns: repeat(3, 1fr);
		}
	}

	@media (max-width: 576px) {
		.cards-wrap {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	@media (max-width: 400px) {
		.cards-wrap {
			grid-template-columns: repeat(1, 1fr);
		}
	}
</style>
