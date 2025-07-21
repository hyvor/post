<script lang="ts">
	import Editor from '../../Editor/Editor.svelte';
	import { draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';

	function onContentDocUpdate(doc: string) {
		$draftIssueEditingStore.content = doc;
		debouncedUpdateDraftIssue();
	}
</script>

<div class="content-wrap">
	<div class="content-inner">
		<input type="text" placeholder="Subject..." />
		<Editor content={$draftIssueEditingStore.content} ondocupdate={onContentDocUpdate} />
	</div>
</div>

<style>
	.content-wrap {
		display: flex;
		flex-direction: column;
		flex: 1;
		overflow: auto;
	}

	.content-inner {
		width: 700px;
		max-width: 100%;
		margin: 0 auto;
	}

	input {
		display: block;
		width: 100%;
		resize: none;
		font-size: 26px;
		font-weight: 600;
		line-height: 1.5;
		overflow: hidden;
		border: none;
		background: transparent;
		outline: none;
		font-family: inherit;
		padding: 25px 30px 0;
	}
</style>
