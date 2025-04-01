<script lang="ts">
	import { FormControl, Label, Modal, TextInput } from "@hyvor/design/components";

    export let modalOpen = false;
    export let listName: string = '';
    export let listDescription: string|null = null;

    export let submitModal: () => void;

</script>

<Modal
	title={listName === '' ? "Create List" : "Edit List"}
	bind:show={modalOpen}
	size="large"
	footer={{
        cancel: {
            text: 'Cancel',
        }, 
        confirm: {
            text: listName === '' ? 'Create' : 'Save',
        }
    }}
    on:cancel={() => {
		modalOpen = false;
	}}
    on:confirm={() => {
        modalOpen = false;
		submitModal();
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