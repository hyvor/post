<script lang="ts">
	import { Button, toast } from '@hyvor/design/components';
	import { createList } from '../../lib/actions/listActions';
	import ListEditionModal from './ListEditionModal.svelte';
	import { listStore } from '../../lib/stores/newsletterStore';

	let modalOpen: boolean = false;
	let listName: string = '';
	let listDescription: string | null = null;

	// TODO: Check list name availabilty of list name
	function submitList() {
		const toastId = toast.loading('Creating list...');

		createList(listName, listDescription)
			.then((res) => {
				toast.success('List created', { id: toastId });
				listStore.update((lists) => [...lists, res]);
			})
			.catch(() => {
				toast.error('Failed to create list', { id: toastId });
			});
	}
</script>

<Button color="accent" on:click={() => (modalOpen = true)} size="small">Create List</Button>

<ListEditionModal bind:modalOpen bind:listName bind:listDescription submitModal={submitList} />
