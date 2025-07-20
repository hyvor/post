<script lang="ts">
	import type { Snippet, Component } from 'svelte';
	interface Props {
		summary: string | Snippet;
		content: string | Snippet;
		icon?: Component;
	}

	let { summary, content, icon }: Props = $props();

	const Icon = icon;

	let open = $state(false);
</script>

<details bind:open>
	<summary>
		{#if icon}
			<span class="icon">
				<Icon size={15} />
			</span>
		{/if}
		<span
			>{#if typeof summary === 'string'}
				{summary}
			{:else}
				{@render summary()}
			{/if}</span
		>
	</summary>

	<div class="content">
		{#if typeof content === 'string'}
			{content}
		{:else}
			{@render content()}
		{/if}
	</div>
</details>

<style>
	details {
		border: 1px solid var(--accent-light);
		border-radius: 20px;
		overflow: hidden;
		background-color: var(--accent-lightest);
	}
	summary {
		padding: 15px 20px;
		border-radius: 20px 20px 0 0;
		cursor: pointer;
		width: 100%;
		-webkit-appearance: none;
	}
	details[open] summary {
		background-color: var(--accent-light);
		border-bottom: 1px solid var(--accent-light);
	}
	summary:before {
		content: '';
		border-width: 0.4rem;
		border-style: solid;
		/* border-color: transparent transparent transparent #fff; */
		position: absolute;
		top: 1.3rem;
		left: 1rem;
		transform: rotate(0);
		transform-origin: 0.2rem 50%;
		transition: 0.25s transform ease;
	}
	details div {
		padding: 20px;
	}
	details > summary {
		list-style: none;
	}
	summary::-webkit-details-marker {
		display: none;
	}

	summary::after {
		content: ' ►';
		font-size: 12px;
		float: right;
	}
	details[open] summary:after {
		/* content: '▼'; */
		transform: rotate(90deg);
		transition: 0.25s transform ease-in-out;
	}
	details summary:after {
		transition: 0.25s transform ease-in-out;
	}

	.icon {
		display: inline-block;
		margin-right: 10px;
		vertical-align: middle;
	}
</style>
