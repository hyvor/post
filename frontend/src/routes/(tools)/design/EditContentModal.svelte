<script lang="ts">
	import { Modal } from '@hyvor/design/components';
	import Editor from '../../console/[id]/issues/Editor/Editor.svelte';

	interface Props {
		content: string;
		show: boolean;
		onContentUpdate: (s: string) => void;
	}

	let { content, show = $bindable(), onContentUpdate }: Props = $props();
</script>

<Modal title="Edit content" bind:show>
	<Editor {content} docupdate={onContentUpdate} />
</Modal>
