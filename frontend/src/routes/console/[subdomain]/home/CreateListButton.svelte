<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import ListEditionModal from './ListEditionModal.svelte';

	let modalOpen: boolean = false;
</script>

<Button color="accent" on:click={() => (modalOpen = true)} size="small">Create List</Button>

<ListEditionModal bind:modalOpen />
