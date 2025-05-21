<script lang="ts">
	import { onMount } from 'svelte';
	import { Modal, TextInput, Button } from '@hyvor/design/components';

	export let show = false;
	export let add: (text: string, href: string) => void;
	export let initialText = '';
	export let initialHref = '';
	export let isEditing = false;

	let text = initialText;
	let href = initialHref;

	onMount(() => {
		text = initialText;
		href = initialHref;
	});

	function handleSubmit() {
		add(text, href);
		show = false;
	}
</script>

<Modal
 	bind:show 
	title={isEditing ? "Edit Button" : "Add Button"}
	footer={{
		confirm: {
			text: isEditing ? 'Save Changes' : 'Add Button'
		},
		cancel: {
			text: 'Cancel'
		}
	}}
	on:close={() => (show = false)}
	on:confirm={handleSubmit}
>
	<div class="form">
		<TextInput 
			label="Button Text"
			placeholder="Enter button text"
			bind:value={text}
		/>
		<TextInput
			label="Button URL"
			placeholder="https://example.org"
			bind:value={href}
		/>
	</div>
</Modal>

<style lang="scss">
	.form {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}
</style> 