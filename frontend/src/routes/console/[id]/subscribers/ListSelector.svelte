<script lang="ts">
	import { Checkbox, Validation } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { listStore } from '../../lib/stores/projectStore';

	export let selectedList = [] as number[];

	function onSegmentChange(id: number) {
		if (selectedList.includes(id)) {
			selectedList = selectedList.filter((s) => s !== id);
		} else {
			selectedList = [...selectedList, id];
		}
	}

	onMount(() => {
		selectedList = $listStore.map((list) => list.id);
	});
</script>

{#each $listStore as list}
	<div class="list">
		<Checkbox
			checked={selectedList.includes(list.id)}
			on:change={() => onSegmentChange(list.id)}
		>
			{list.name}
		</Checkbox>
	</div>
{/each}
{#if selectedList.length === 0}
	<Validation type="error">Please select at least one list.</Validation>
{/if}

<style>
	.list {
		margin-bottom: 10px;
	}
</style>
