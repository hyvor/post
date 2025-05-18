<script lang="ts">
	// import { currentWebsite } from "../../stores/websitesStore";
	import type { User, UserMini } from '../../types';

	export let user: UserMini;
	export let size: number = 32;

	const sizePx = size + 'px';

	let src = user.picture_url;
	let custom = !src;

	function handleError() {
		custom = true;

		// TODO: Use default picture
		// $currentWebsite?.default_picture;
	}
</script>

{#if custom || !src}
	<span style:width={sizePx} style:height={sizePx}>
		{user.name.substring(0, 1)}
	</span>
{:else}
	<img
		{src}
		alt={user.name}
		loading="lazy"
		on:error={handleError}
		style:width={sizePx}
		style:height={sizePx}
	/>
{/if}

<style>
	img {
		border-radius: 50%;
	}

	span {
		display: inline-flex;
		justify-content: center;
		align-items: center;
		opacity: 0.6;
		background-color: var(--accent);
		font-size: 0.8rem;
		border-radius: 50%;
	}
</style>
