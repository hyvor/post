<script lang="ts">
	import { Modal } from '@hyvor/design/components';
	import ConfigComponent from '../ConfigComponent.svelte';
	import Preview from './Preview.svelte';

	export let show = false;
</script>

<div class="modal-wrap">
	<Modal
		bind:show
		title="Email Design"
		size="large"
		role="dialog"
		footer={{
			cancel: {
				text: 'Cancel'
			},
			confirm: {
				text: 'Save'
			}
		}}
		on:cancel={() => (show = false)}
	>
		<div class="content">
			<div class="left">
				<ConfigComponent />
			</div>
			<div class="preview">
				<Preview />
			</div>
		</div>
	</Modal>
</div>

<style>
	.modal-wrap :global(.inner[role='dialog']) {
		width: 100%;
		height: 100%;

		display: flex;
		flex-direction: column;
	}
	.modal-wrap :global(.inner[role='dialog'] > .content) {
		flex: 1;
		overflow: hidden;
	}

	.content {
		display: flex;
		flex-direction: row;
		height: 100%;
	}

	.preview {
		width: 50%;
		padding: 1rem;
	}

	.left {
		width: 50%;
		border-right: 1px solid var(--border);
		overflow: auto;
	}
</style>
