<script lang="ts">
	import { SplitControl } from '@hyvor/design/components';
	import Editor from '../../Editor/Editor.svelte';
	import { draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';

	function onContentDocUpdate(doc: string) {
		$draftIssueEditingStore.content = doc;
		debouncedUpdateDraftIssue();
	}
</script>

<SplitControl label="Content" column>
	<Editor content={$draftIssueEditingStore.content} ondocupdate={onContentDocUpdate} />
</SplitControl>
