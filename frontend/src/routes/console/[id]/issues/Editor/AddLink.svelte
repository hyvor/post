<script lang="ts">
	import { Modal, TextInput } from '@hyvor/design/components';
	import { onMount } from 'svelte';

	export let show = false;
    export let add: (url: string) => void;

	let url = '';
	let textInput: HTMLInputElement;

	function confirm() {
		add(url);
	}

	function handleKeydown(event: KeyboardEvent) {
		if (event.key === 'Enter') {
			confirm();
		}
		if (event.key === 'Escape') {
			show = false;
		}
	}

	onMount(() => {
		setTimeout(() => textInput.focus(), 0);
	});
</script>

<Modal
	bind:show
	title="Add Link"
	footer={{
		confirm: {
			text: 'Add Link'
		},
		cancel: {
			text: 'Close'
		}
	}}
	on:close={() => (show = false)}
	on:confirm={confirm}
>
	<TextInput
		bind:value={url}
		on:keydown={handleKeydown}
		bind:input={textInput}
		block
		placeholder="https://example.org"
	/>
</Modal>
