<script lang="ts">
	import { Checkbox, Validation } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { listStore } from '../../../lib/stores/newsletterStore';

	let {
		selectedList = $bindable([] as number[]),
		setAllOnMount = false,
		allowZero = false
	} = $props();

	function onListChange(id: number) {
		if (selectedList.includes(id)) {
			selectedList = selectedList.filter((s) => s !== id);
		} else {
			selectedList = [...selectedList, id];
		}
	}

	onMount(() => {
		if (setAllOnMount) {
			selectedList = $listStore.map((list) => list.id);
		}
	});
</script>

{#each $listStore as list}
	<div class="list">
		<Checkbox checked={selectedList.includes(list.id)} on:change={() => onListChange(list.id)}>
			{list.name}
		</Checkbox>
	</div>
{/each}
{#if selectedList.length === 0 && !allowZero}
	<Validation type="error">Please select at least one list.</Validation>
{/if}

<style>
	.list {
		margin-bottom: 10px;
	}
</style>
