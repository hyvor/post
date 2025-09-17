<script lang="ts">
    import {Editor} from '@hyvor/richtext';
    import type {EditorView} from 'prosemirror-view';

    interface Props {
        content?: string | null;
        onDocUpdate: (doc: string) => void;
    }

    let {content = null, onDocUpdate}: Props = $props();
</script>

<Editor value={content} onvaluechange={onDocUpdate}/>
