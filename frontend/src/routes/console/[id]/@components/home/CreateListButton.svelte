<script lang="ts">
	import { ActionList, ActionListItem, Button, Caption, Dropdown, FormControl, IconButton, Label, Modal, Radio, TextInput, toast } from "@hyvor/design/components";

    let modalOpen = false;
	let showDropdown = false;

</script>

<Button
	color="accent"
	on:click={() => modalOpen = true}
	size="small"
>
	Create List
</Button>

<Modal
	title="Publish Post"
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
    }}
>

	<div class="modal-inner">
		<FormControl>
			<Label>Name</Label>
			<TextInput placeholder="Enter list name" />
		</FormControl>
		<FormControl>
			<Label>Project</Label>
		</FormControl>
	</div>




</Modal>