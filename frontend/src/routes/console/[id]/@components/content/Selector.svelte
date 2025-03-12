<script lang="ts">
	import { Button, Dropdown, IconButton } from '@hyvor/design/components';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
    import IconX from '@hyvor/icons/IconX';
	import { createEventDispatcher } from 'svelte';

	export let name: string;
	export let value: string | undefined = undefined;

	export let show = false;
	export let isSelected = false;
	export let disabled = false;

	export let align: 'start' | 'center' | 'end' = 'start';

	export let width = 400;

	const dispatcher = createEventDispatcher();

	function handleTriggerClick() {
		dispatcher('open');
	}

	function handleDeselectClick() {
		dispatcher('deselect');
	}
</script>

<Dropdown bind:show width={width} align={align}>
	{#snippet trigger()}
	<Button size="small" variant="invisible" color="gray" on:click={handleTriggerClick}>
		<span class="name" slot="start">{name}</span>

		<span class="value">
			{#if $$slots.value}
				<slot name="value" />
			{:else}
				{value}
			{/if}
		</span>

		{#if isSelected}
			<IconButton
				size={14}
				style="margin-left:4px;"
				color="gray"
				on:click={(e) => {
					e.stopPropagation();
					handleDeselectClick();
				}}
			>
				<IconX size={10} />
			</IconButton>
		{/if}

		<IconCaretDown slot="end" size={12} />
	</Button>
	{/snippet}

	{#snippet content()}
		<slot />
	{/snippet}
</Dropdown>

<style>
	.value {
		font-weight: normal;
		font-size: 13px;
	}
</style>
