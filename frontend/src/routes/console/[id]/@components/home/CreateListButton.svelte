<script lang="ts">
	import { Button, FormControl, Label, Modal, TextInput, toast } from "@hyvor/design/components";
	import { createList } from '../../../lib/actions/listActions'

    let modalOpen = false;
	let showDropdown = false;
	let isCreatingList = false;
	let listName: string = '';
	let listDescription: string|null = null;

	// TODO: Check list name availabilty of list name
	function submitList() {
		const toastId = toast.loading('Creating list...');
		isCreatingList = true;

		createList(listName, listDescription)
			.then((res) => {
				toast.success('List created', { id: toastId });
			})
			.catch(() => {
				toast.error('Failed to create list', { id: toastId });
			});
	}

</script>

<Button
	color="accent"
	on:click={() => modalOpen = true}
	size="small"
>
	Create List
</Button>

<Modal
	title="Create List"
	bind:show={modalOpen}
	size="large"
	footer={{
        cancel: {
            text: 'Cancel',
        },
        confirm: {
            text: 'Create',
        }
    }}
    on:cancel={() => {
		modalOpen = false;
	}}
    on:confirm={() => {
        modalOpen = false;
		submitList();
    }}
>

	<div class="modal-inner">
		<FormControl>
			<Label>
				Name
			</Label>
			<TextInput 
				maxlength={255}
				placeholder="Enter list name" 
				bind:value={listName}
			/>
			<Label>
				Description
			</Label>
			<TextInput 
				placeholder="Enter list description" 
				bind:value={listDescription}
			/>
		</FormControl>

	</div>




</Modal>
