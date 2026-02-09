<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { platforms } from '$lib/install/platforms';

	interface Props {
		prefix: string;
		platform: keyof typeof platforms;
	}

	let { prefix, platform }: Props = $props();

	const codePlatforms = Object.keys(platforms).filter(
		(platform) => platforms[platform].code === true
	);
	const otherPlatforms = Object.keys(platforms).filter(
		(platform) => platforms[platform].code !== true
	);
</script>

<div class="platforms">
	{#each [codePlatforms, otherPlatforms] as pls, i}
		<div class="platform-type">
			<div class="heading">
				{i === 0 ? 'Code' : 'Platforms'}
			</div>
			<div class="button-wrap">
				{#each pls as pl}
					<Button
						href={pl === 'html' ? prefix : `${prefix}/${pl}`}
						as="a"
						color={pl === platform ? 'accent' : 'input'}
					>
						{platforms[pl].name}
					</Button>
				{/each}
			</div>
		</div>
	{/each}
	<div class="tip">
		Tip: You can use the <strong>HTML</strong> method to install on any website.
	</div>
</div>

<!-- href={`/install/${platform}/comments`} -->

<style lang="scss">
	.platforms {
		display: flex;
		margin-top: 2rem;
		flex-direction: column;
		gap: 15px;
	}
	.platforms :global(a) {
		text-decoration: none !important;
	}
	.platform-type {
		display: flex;
		flex-direction: column;
		gap: 10px;
		.heading {
			font-size: 1rem;
			font-weight: 600;
			width: 100px;
		}

		.button-wrap {
			display: flex;
			flex-wrap: wrap;
			gap: 7px;
			position: relative;
		}
	}
	.tip {
		margin-top: 5px;
		font-size: 12px;
		color: var(--text-light);
	}
</style>
