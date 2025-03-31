<script lang="ts">
	import { IconButton, toast } from '@hyvor/design/components';
	import { listStore, projectStore } from '../../../lib/stores/projectStore';
	import type { List } from '../../../types';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';
	import ListEditionModal from './ListEditionModal.svelte';
	import { updateList } from '../../../lib/actions/listActions';
	import EditListButton from './EditListButton.svelte';

	let { list }: { list: List } = $props();
	let listName: string = list.name;
	let listDescription: string | null = list.description;
	let modalOpen = false;


	function truncateDescription(description: string | null): string {
		if (!description)
		 return '(No description)';
		if (description.length > 50) {
			return description.slice(0, 50) + '...';
		}
		return description;
	}

	function onEdit(event: Event) {
		event.stopPropagation();
		event.preventDefault();
		console.log('Opening modal...');
		modalOpen = true;
	}

	function onDelete(event: Event) {
		event.stopPropagation();
		event.preventDefault();
		console.log('Delete list', list.name);
	}

	function submitEdit() {
		const toastId = toast.loading('Updating list...');

		updateList(list.id, listName, listDescription)
			.then((res) => {
				toast.success('List updated', { id: toastId });
				listStore.update((lists) => {
					const index = lists.findIndex((l) => l.id === list.id);
					if (index !== -1) {
						lists[index] = { ...lists[index], ...res };
					}
					return lists;
				});
			})
			.catch(() => {
				toast.error('Failed to update list', { id: toastId });
			});
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
	<div class="actions">
		<div class="actions">
			<EditListButton
				bind:listName={listName}
				bind:listDescription={listDescription}
				onEdit={onEdit}
				submitList={submitEdit}
			/>
			<IconButton color="red" variant="fill-light" size="small" on:click={onDelete}>
				<IconTrash size={12} />
			</IconButton>
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
