<script lang="ts">
	import Stat from './Stat.svelte';

	export let list;
</script>

<a class="list-item" href={`/console/lists/${list.id}`}>
	<div class="list-title">{list.name || '(Untitled)'}</div>
	<div class="list-subscribers">
		<Stat
			title="Subscribers"
			href={`/console/lists/${list.id}/subscribers`}
			counts={{
				total: list.subscribers_count.total,
				last_30d: list.subscribers_count.last_30d
			}}
		/>
	</div>
</a>

<style>
	.list-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 20px;
		padding-left: 50px;
		padding-right: 50px;
		border-left: 3px solid transparent;
		position: relative;
		border-radius: 20px;
		cursor: pointer;
	}

	.list-item:hover {
		background: var(--hover);
	}

	.list-title {
		width: 300px;
		font-weight: 600;
		word-break: break-all;
	}
</style>
