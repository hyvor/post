<script lang="ts">
	import ListRow from './ListRow.svelte';
	import CreateListButton from './CreateListButton.svelte';
	import { listStore } from '../../../lib/stores/projectStore';

	let showCreateListModal = false;
	const lists = $listStore;
</script>

<div id="list-view">
	<div class="list-header">
		Lists
		<CreateListButton />
	</div>
	{#each lists as list}
		<ListRow {list} />
	{/each}
</div>

<style>
	.list-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20px;
		padding-left: 50px;
		padding-right: 50px;
		font-size: 1.5rem;
		font-weight: 600;
	}
</style>
