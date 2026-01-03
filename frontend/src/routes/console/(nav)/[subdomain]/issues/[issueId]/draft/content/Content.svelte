<script lang="ts">
	import { Editor } from '@hyvor/richtext';
	import { draftIssueEditingStore } from '../draftStore';
	import { debouncedUpdateDraftIssue } from '../draftActions';
	import { getI18n } from '../../../../../../lib/i18n';
	import { newsletterStore } from '../../../../../../lib/stores/newsletterStore';
	import { uploadImage } from '../../../../../../lib/actions/mediaActions';
	import { onMount } from 'svelte';

	function onContentDocUpdate(doc: string) {
		$draftIssueEditingStore.content = doc;
		debouncedUpdateDraftIssue();
	}

	function onSubjectUpdate(e: Event) {
		const input = e.target as HTMLInputElement;
		$draftIssueEditingStore.subject = input.value;
		debouncedUpdateDraftIssue();
	}

	function handleKeyDown(e: KeyboardEvent) {
		if (e.key === 'Enter' || e.key === 'ArrowDown') {
			e.preventDefault();
			editorView.focus();
		}
	}

	function handleDomEvent(name: string, e: Event) {
		// focus the subject input back
		if (name === 'keydown' && ['ArrowUp', 'Backspace'].includes((e as KeyboardEvent).key)) {
			const selection = editorView.state.selection;
			if (selection.from === 1 && selection.to === 1) {
				e.preventDefault();
				subjectInput.focus();
			}
		}
	}

	interface EditorView {
		focus(): void;
	}

	let editorView: EditorView & any = $state({} as any);
	let subjectInput: HTMLInputElement = $state({} as HTMLInputElement);

	const I18n = getI18n();

	onMount(() => {
		subjectInput.focus();
	});
</script>

<div class="content-wrap" dir={$newsletterStore.is_rtl ? 'rtl' : 'ltr'}>
	<div class="content-inner">
		<input
			type="text"
			placeholder={I18n.t('console.issues.draft.subjectPlaceholder')}
			value={$draftIssueEditingStore.subject}
			onchange={onSubjectUpdate}
			onkeydown={handleKeyDown}
			bind:this={subjectInput}
		/>

		<Editor
			bind:editorView
			value={$draftIssueEditingStore.content}
			onvaluechange={onContentDocUpdate}
			ondomevent={handleDomEvent}
			config={{
				colorButtonBackground: $newsletterStore?.template_color_accent || '#5A8387',
				colorButtonText: $newsletterStore?.template_color_accent_text || '#ffffff',

				codeBlockEnabled: true,
				codeBlockConfig: {
					language: false,
					fileName: false,
					annotations: false,
					annotationsUrl: null
				},

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

				fileMaxSizeInMB: 10,
				fileUploader: async (file, name, type) => {
					if (type !== 'image') {
						return null;
					}
					const media = await uploadImage(file, 'issue_images');
					return {
						url: media.url
					};
				}
			}}
		/>
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