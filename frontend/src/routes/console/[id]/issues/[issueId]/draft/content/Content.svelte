<script lang="ts">
    import Editor from '../../../Editor/Editor.svelte';
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

    const I18n = getI18n();
</script>

<div class="content-wrap">
    <div class="content-inner">
        <input
            type="text"
            placeholder={I18n.t('console.issues.draft.subjectPlaceholder')}
            value={$draftIssueEditingStore.subject}
            onchange={onSubjectUpdate}
        />
        <Editor content={$draftIssueEditingStore.content} onDocUpdate={onContentDocUpdate}/>
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
