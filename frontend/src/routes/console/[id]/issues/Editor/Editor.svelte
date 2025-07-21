<script lang="ts">
	import { Editor } from '@hyvor/richtext';

	interface Props {
		content?: string | null;
		ondocupdate: (doc: string) => void;
	}

	let { content = null, ondocupdate }: Props = $props();
</script>

<Editor />
