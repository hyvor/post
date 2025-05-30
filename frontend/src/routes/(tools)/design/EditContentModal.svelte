<script lang="ts">
	import { Modal } from '@hyvor/design/components';
	import Editor from '../../console/[id]/issues/Editor/Editor.svelte';
	import { retrieveContentHtml } from './lib/actions/templateActions';

	interface Props {
		content: string;
		show: boolean;
		updateContent: (s: string) => void;
	}

	let { content, show = $bindable(), updateContent }: Props = $props();

	function getContentHtml() {
		retrieveContentHtml(content)
			.then((res) => {
				updateContent(res['html']);
			})
			.catch((err) => {
				console.error(err);
			});
	}

	function submitModal() {
		getContentHtml();
	}
</script>

<Modal
	title="Edit content"
	bind:show
	footer={{
		cancel: {
			text: 'Cancel'
		},
		confirm: {
			text: 'Update content'
		}
	}}
	on:cancel={() => {
		show = false;
	}}
	on:confirm={() => {
		show = false;
		submitModal();
	}}
>
	<Editor {content} ondocupdate={(s: string) => (content = s)} />
</Modal>
