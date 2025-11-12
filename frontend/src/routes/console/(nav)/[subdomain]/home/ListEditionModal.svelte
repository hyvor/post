<script lang="ts">
	import {
		FormControl,
		Modal,
		SplitControl,
		TextInput,
		toast,
		Validation
	} from '@hyvor/design/components';
	import type { List } from '../../../types';
	import { createList, updateList } from '../../../lib/actions/listActions';
	import { listStore } from '../../../lib/stores/newsletterStore';

	interface Props {
		modalOpen: boolean;
		list?: List;
	}

	let { modalOpen = $bindable(false), list }: Props = $props();

	let listName = $state(list?.name ?? '');
	let listDescription = $state(list?.description ?? '');
	let nameInput = $state({} as HTMLInputElement);
	let loading = $state(false);
	let nameError = $state('');

	$effect(() => {
		if (modalOpen) {
			nameInput.focus();
		}
	});

	function handleCreate() {
		loading = true;

		createList(listName, listDescription)
			.then((res) => {
				toast.success('List created');
				listStore.update((lists) => [res, ...lists]);
				modalOpen = false;
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				loading = false;
			});
	}

	function handleEdit() {
		loading = true;

		updateList(list!.id, listName, listDescription)
			.then((res) => {
				toast.success('List updated');
				listStore.update((lists) => {
					const index = lists.findIndex((l) => l.id === list!.id);
					if (index !== -1) {
						lists[index] = { ...lists[index], ...res };
					}
					return lists;
				});
				modalOpen = false;
			})
			.catch(() => {
				toast.error('Failed to update list');
			})
			.finally(() => {
				loading = false;
			});
	}

	function handleInput() {
		nameError = '';
		if (nameInput.value.includes(',')) {
			nameError = 'List name cannot contain commas.';
		}
	}

	function validateInput() {
		if (listName.trim().length === 0) {
			nameError = 'List name is required.';
			return;
		}
	}
</script>

<Modal
	title={!list ? 'Create List' : 'Edit List'}
	bind:show={modalOpen}
	size="medium"
	footer={{
		cancel: {
			text: 'Cancel'
		},
		confirm: {
			text: !list ? 'Create' : 'Save'
		}
	}}
	on:cancel={() => {
		modalOpen = false;
	}}
	on:confirm={() => {
		validateInput();
		if (nameError) {
			return;
		}

		if (list) {
			handleEdit();
		} else {
			handleCreate();
		}
	}}
	{loading}
>
	<div class="modal-inner">
		<FormControl>
			<SplitControl label="Name" caption="The name of the list.">
				<FormControl>
					<TextInput
						maxlength={255}
						placeholder="Enter list name"
						bind:value={listName}
						bind:input={nameInput!}
						oninput={handleInput}
						block
					/>

					{#if nameError}
						<Validation type="error">
							{nameError}
						</Validation>
					{/if}
				</FormControl>
			</SplitControl>
			<SplitControl label="Description" caption="The description of the list.">
				<TextInput
					placeholder="Enter list description"
					bind:value={listDescription}
					block
				/>
			</SplitControl>
		</FormControl>
	</div>
</Modal>

<style>
</style>
