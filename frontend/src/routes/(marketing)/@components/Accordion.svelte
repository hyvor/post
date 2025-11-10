<script lang="ts">
	import type { Component } from 'svelte';
	import IconCaretDownFill from '@hyvor/icons/IconCaretDownFill';

	interface Props {
		title?: string;
		content?: string; // HTML string SHOWS
		isOpen?: boolean;
		icon?: Component;
	}

	let { title, content, isOpen = false, icon }: Props = $props();
	const Icon = icon;

	function handleClick() {
		isOpen = !isOpen; // toggle state
	}
</script>

<div class="accordion-item">
	<button class="accordion-header" class:open={isOpen} onclick={handleClick}>
		{#if icon}
			<span class="icon">
				<Icon size={20} />
			</span>
		{/if}
		<span class="title">{title}</span>
		<span class="chevron" class:rotated={isOpen}>
			<IconCaretDownFill />
		</span>
	</button>

	{#if isOpen}
		<div class="accordion-content">
			<div class="content-text">
				{@html content}
			</div>
		</div>
	{/if}
</div>

<style>
	.accordion-item {
		border-radius: 20px;
		overflow: hidden;
		background: var(--accent-light-mid);
	}

	.accordion-header {
		width: 100%;
		padding: 16px 20px;
		background: none;
		border: none;
		display: flex;
		justify-content: space-between;
		align-items: center;
		cursor: pointer;
		font-size: 16px;
	}

	.accordion-header:hover {
		background-color: var(--accent-light-mid);
	}

	.accordion-header.open {
		background-color: var(--accent-light);
	}

	.title {
		font-weight: 500;
		text-align: left;
	}

	.chevron {
		display: flex;
		align-items: center;
		transition: transform 0.3s ease;
		color: #6b7280;
	}

	.chevron.rotated {
		transform: rotate(180deg);
	}

	.accordion-content {
		animation: slideDown 0.3s ease-out;
	}

	.content-text {
		padding: 20px;
		color: #4b5563;
		line-height: 1.6;
		border-top: 1px solid #f3f4f6;
		background-color: var(--accent-light-mid);
	}

	/* .content-text a {
		color: var(--accent);
		text-decoration: underline;
	} */

	.icon {
		display: inline-block;
		margin-right: 10px;
		vertical-align: middle;
	}

	@keyframes slideDown {
		from {
			opacity: 0;
			transform: translateY(-10px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}
</style>
