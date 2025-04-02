<script lang="ts">
	import { IconButton, toast, confirm } from '@hyvor/design/components';
	import { listStore, projectStore } from '../../../lib/stores/projectStore';
	import type { List } from '../../../types';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deleteList, updateList } from '../../../lib/actions/listActions';
	import EditListButton from './EditListButton.svelte';

	let { list }: { list: List } = $props();
	let listName = $state(list.name);
	let listDescription = $state(list.description);
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
		modalOpen = true;
	}

	async function submitEdit() {
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

	async function onDelete(event: Event) {
		event.stopPropagation();
		event.preventDefault();
		
		const confirmation = await confirm({
			title: 'Delete List',
			content: 'Are you sure you want to delete this list?',
			confirmText: 'Delete',
			cancelText: 'Cancel',
			danger: true
		});

		if (!confirmation) return;

		confirmation.loading();

		deleteList(list.id)
			.then(() => {
				toast.success('List deleted successfully');
				listStore.update((lists) => {
					return lists.filter((l) => l.id !== list.id);
				});
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				confirmation.close();
			});
	}

</script>

<div class="list-item">
	<a class="list-content" href={`/console/${$projectStore.id}/subscribers?list=${list.id}`}>
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
	<div class="actions">
		<EditListButton
			bind:listName
			bind:listDescription
			onEdit={onEdit}
			submitList={submitEdit}
		/>
		<IconButton color="red" variant="fill-light" size="small" on:click={onDelete}>
			<IconTrash size={12} />
		</IconButton>
	</div>
</div>

<style>
	.list-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.actions {
		margin-left: 10px;
	}

	.list-content {
		flex: 1;
		padding: 10px;
		padding-left: 15px;
		padding-right: 15px;
		border-left: 3px solid transparent;
		position: relative;
		border-radius: 20px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		text-decoration: none;
		color: inherit;
	}

	.list-content:hover {
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
