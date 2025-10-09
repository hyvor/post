<script lang="ts">
    import { Editor } from '@hyvor/richtext';
    import {draftIssueEditingStore} from '../draftStore';
    import {debouncedUpdateDraftIssue} from '../draftActions';
    import {getI18n} from '../../../../../lib/i18n';

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
        if (name === 'keydown' && (e as KeyboardEvent).key === 'ArrowUp') {
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

    let editorView: EditorView&any = $state({} as any);
    let subjectInput: HTMLInputElement = $state({} as HTMLInputElement);

    const I18n = getI18n();
</script>

<div class="content-wrap">
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
            bind:editorView={editorView}
            value={$draftIssueEditingStore.content}
            onvaluechange={onContentDocUpdate}
            ondomevent={handleDomEvent}
            config={{
                // colorButtonText:
                // colorButtonBackground:

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

                fileUploader: async (file, name, type) => {
                    // TODO: upload to server
                    return {
                        url: URL.createObjectURL(file)
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
