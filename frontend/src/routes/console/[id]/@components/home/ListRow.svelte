<script lang="ts">
	import type { List } from '../../../types';

	let { list }: { list: List } = $props();
</script>

<a class="list-item" href={`/console/lists/${list.id}`}>
	<div class="list-title">{list.name || '(Untitled)'}</div>
	<div class="list-subscribers">
		<div class="count">
			{list.subscribers_count} Subscribers
		</div>
		<div
			class="count-diff"
			class:positive={list.subscribers_count_last_30d >= 0}
			class:negative={list.subscribers_count_last_30d < 0}
		>
			{list.subscribers_count_last_30d >= 0
				? '+'
				: ''}{list.subscribers_count_last_30d.toLocaleString()}

			<span class="last-30d-tag">30d</span>
		</div>
	</div>
</a>

<style>
	.list-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px;
		padding-left: 15px;
		padding-right: 15px;
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

	.count {
		font-weight: 600;
	}
	.count-diff {
		font-size: 14px;
	}

	.count-diff.positive {
		color: var(--green);
	}
	.count-diff.negative {
		color: var(--red);
	}
	.last-30d-tag {
		font-size: 12px;
		color: var(--text-light);
	}
</style>
