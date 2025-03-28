<script lang="ts">
	import ListRow from './ListRow.svelte';
	import CreateListButton from './CreateListButton.svelte';
	import { listStore } from '../../../lib/stores/projectStore';

	$: {
		$listStore;
	}
</script>

<div id="list-view">
	<div class="list-header">
		Lists
		<CreateListButton />
	</div>
	<div class="rows">
		{#each $listStore as list}
			<ListRow {list} />
		{/each}
	</div>
</div>

<style>
	.list-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20px;
		padding-left: 35px;
		padding-right: 35px;
		font-size: 1.5rem;
		font-weight: 600;
	}
	.rows {
		padding: 10px 35px;
	}
</style>
