<script lang="ts">
	import { ActionList, ActionListItem, Button, Caption, Dropdown, FormControl, IconButton, Label, Modal, Radio, TextInput, toast } from "@hyvor/design/components";
	import { createList } from '../../../lib/actions/listActions'

    let modalOpen = false;
	let showDropdown = false;
	let isCreatingList = false;
	let listName = '';

	// TODO: Check list name availabilty of list name
	function submitList() {
		const toastId = toast.loading('Creating list...');
		isCreatingList = true;

		createList(listName)
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
			<Label>Name</Label>
			<TextInput 
				maxlength={255}
				placeholder="Enter list name" 
				bind:value={listName}
			/>
		</FormControl>

	</div>




</Modal>