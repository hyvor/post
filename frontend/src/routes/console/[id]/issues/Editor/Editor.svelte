<script lang="ts">
	import { Editor } from '@hyvor/richtext';
	import type { EditorView } from 'prosemirror-view';

	interface Props {
		content?: string | null;
		ondocupdate: (doc: string) => void;
		view: EditorView;
	}

	let { content = null, ondocupdate }: Props = $props();
</script>

<Editor />
