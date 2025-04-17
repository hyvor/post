<script lang="ts">
	import { Modal } from "@hyvor/design/components";
	import Editor from "../../console/[id]/issues/Editor/Editor.svelte";

    export let content: string;
    export let show: boolean;
    export let onContentUpdate: (s: string) => void;
</script>

<Modal
    title="Edit content"
    bind:show={show}
>
    <Editor
        content={content}
        docupdate={onContentUpdate}
    />
</Modal>