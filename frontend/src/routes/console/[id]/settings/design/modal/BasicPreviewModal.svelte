<script lang="ts">
	import { Callout, Modal } from '@hyvor/design/components';
	import ConfigComponent from '../ConfigComponent.svelte';
	import Preview from './Preview.svelte';

	export let show = false;

	let key = 0;
</script>

<div class="modal-wrap">
	<Modal
		bind:show
		title="Email Design"
		size="large"
		role="dialog"
		on:cancel={() => (show = false)}
	>
		<div class="content">
			<div class="left">
				<Callout type="info">
					{#snippet icon()}
						💡
					{/snippet}
					Change settings and save to see the changes in the preview.
				</Callout>
				<ConfigComponent onsave={() => (key += 1)} />
			</div>
			<div class="preview">
				{#key key}
					<Preview />
				{/key}
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
		padding-top: 0;
		padding-bottom: 0;
	}

	.content {
		display: flex;
		flex-direction: row;
		height: 100%;
	}

	.preview {
		width: 50%;
	}

	.left {
		width: 50%;
		border-right: 1px solid var(--border);
		overflow: auto;
		padding-right: 10px;
	}
</style>
