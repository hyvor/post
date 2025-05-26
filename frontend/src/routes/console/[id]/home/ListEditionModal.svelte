<script lang="ts">
	import { FormControl, Modal, SplitControl, TextInput } from '@hyvor/design/components';

	export let modalOpen = false;
	export let listName: string = '';
	export let listDescription: string | null = null;

	export let submitModal: () => void;

	let nameInput: HTMLInputElement | null = null;

	$: if (modalOpen && nameInput) {
		nameInput.focus();
	}
</script>

<Modal
	title={listName === '' ? 'Create List' : 'Edit List'}
	bind:show={modalOpen}
	size="medium"
	footer={{
		cancel: {
			text: 'Cancel'
		},
		confirm: {
			text: listName === '' ? 'Create' : 'Save'
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
			<SplitControl label="Name" caption="The name of the list.">
				<TextInput
					maxlength={255}
					placeholder="Enter list name"
					bind:value={listName}
					bind:input={nameInput!}
				/>
			</SplitControl>
			<SplitControl label="Description" caption="The description of the list.">
				<TextInput placeholder="Enter list description" bind:value={listDescription} />
			</SplitControl>
		</FormControl>
	</div>
</Modal>

<style>
</style>
