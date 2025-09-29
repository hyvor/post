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

		tableEnabled: false, // add later
		tocEnabled: false,
		bookmarkEnabled: false, // add later

		imageEnabled: true,
		audioEnabled: false, // most email clients do not support audio

		fileUploader: async (file, name, type) => {
			// TODO: upload to server
			return {
				url: URL.createObjectURL(file)
			};
		}
	}}
/>
