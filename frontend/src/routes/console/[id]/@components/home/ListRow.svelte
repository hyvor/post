<script lang="ts">
	import { projectStore } from '../../../lib/stores/projectStore';
	import type { List } from '../../../types';

	let { list }: { list: List } = $props();

	function truncateDescription(description: string | null): string {
		if (!description)
		 return '(No description)';
		if (description.length > 50) {
			return description.slice(0, 50) + '...';
		}
		return description;
	}
</script>

<a class="list-item" href={`/console/${$projectStore.id}/subscribers?list=${list.name}`}>
	<div class="list-title">
		{list.name || '(Untitled)'}
		<div class="list-description">
			{truncateDescription(list.description)}
		</div>
	</div>
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

	.list-description {
		margin-top: 5px;
		font-weight: 100;
		font-size: 12px;
		color: var(--text-light);
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
