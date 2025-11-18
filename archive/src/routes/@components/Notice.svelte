<script lang="ts">
	import Button from './Button.svelte';
	import type { Component, Snippet } from 'svelte';

	interface Props {
		heading: string;
		message: string;
		icon?: Component;
		buttonText?: string;
		buttonLink?: string;
		children?: Snippet;
		button?: boolean;
	}

	let {
		heading,
		message,
		icon,
		buttonText,
		buttonLink,
		children,
		button = false
	}: Props = $props();

	const Icon = icon;
</script>

<div class="icon-message">
	{#if Icon}
		<div class="icon">
			<Icon size="50px" />
		</div>
	{/if}

	<div class="message">
		<h2>{heading}</h2>
		<p>
			{@html message}
		</p>
		{#if button}
			<Button as="a" href={buttonLink}>
				{buttonText}
			</Button>
		{/if}

		{@render children?.()}
	</div>
</div>

<style>
	.icon-message {
		width: calc(100% - 80px);
		height: calc(100% - 80px);
		padding: 40px;
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		position: relative;
		flex-direction: column;
		text-align: center;
	}

	.icon {
		display: inline-flex;
		align-items: flex-end;
		color: var(--hp-box-text);
	}

	.message {
		color: var(--hp-box-text);
		margin-top: 10px;
	}
</style>
