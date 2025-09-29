<script lang="ts">
	import type { Component } from 'svelte';
	interface Props {
		icon: Component;
		number: number | string;
		title: string;
		description: string;
		topLine?: boolean;
	} 

	let { icon, number, title, description, topLine = true }: Props = $props();

	const SvelteComponent = $derived(icon);
</script>

<div class="step">
	<div class="top">
		<div class="icon">
			<SvelteComponent this={icon} size={50} />
		</div>
		{#if topLine}
			<div class="top-line"></div>
		{/if}
		<div class="number">{number}</div>
	</div>

	<div class="title">{title}</div>
	<div class="description">{description}</div>
</div>

<style>
	.step {
		display: flex;
		flex-direction: column;
		flex: 1;
		padding: 15px;
		position: relative;
	}
	.number {
		font-size: 60px;
		font-weight: 600;
		opacity: 0.25;
		color: var(--accent);
	}
	.top {
		display: flex;
		align-items: center;
		gap: 15px;
	}
	.icon {
		color: var(--accent);
		position: relative;
	}
	.top-line {
		height: 2px;
		background-color: var(--accent);
		opacity: 0.25;
		flex: 1;
	}
	.title {
		font-size: 20px;
		font-weight: 600;
	}
	.description {
		margin-top: 10px;
		line-height: 22px;
	}
</style>
