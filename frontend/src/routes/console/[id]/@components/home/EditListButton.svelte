<script lang="ts">
	import { Button, FormControl, IconButton, Label, Modal, TextInput, toast } from "@hyvor/design/components";
	import { createList } from '../../../lib/actions/listActions'
	import ListEditionModal from "./ListEditionModal.svelte";
	import { listStore } from "../../../lib/stores/projectStore";
	import IconPencil from "@hyvor/icons/IconPencil";

    let modalOpen: boolean = false;
	export let listName: string = '';
	export let listDescription: string|null = null;
    export let onEdit: (event: Event) => void;
    export let submitList: () => void;

</script>

<IconButton color="input" size="small" on:click={(event: Event) => {
    modalOpen = true;
    onEdit(event);
}}>
    <IconPencil size={12} />
</IconButton>

<ListEditionModal
	bind:modalOpen={modalOpen}
	bind:listName
	bind:listDescription
	submitModal={submitList}
/>
