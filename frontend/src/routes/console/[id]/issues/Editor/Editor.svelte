<script lang="ts">
	import { Editor } from '@hyvor/richtext';

	interface Props {
		content?: string | null;
		onDocUpdate: (doc: string) => void;
	}

	let { content = null, onDocUpdate }: Props = $props();
</script>

<Editor
	value={content}
	onvaluechange={onDocUpdate}
	config={{
		// colorButtonText:
		// colorButtonBackground:

		codeBlockEnabled: true,
		customHtmlEnabled: true,
		buttonEnabled: true,

		// to be added later
		tableEnabled: false,
		bookmarkEnabled: false,

		imageEnabled: true,

		// does not make sense for emails (or email clients do not support)
		tocEnabled: false,
		audioEnabled: false,
		embedEnabled: false,

		fileUploader: async (file, name, type) => {
			// TODO: upload to server
			return {
				url: URL.createObjectURL(file)
			};
		}
	}}
/>
