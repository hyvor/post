<script>
    import {Modal} from '@hyvor/design/components';
    import FormSettings from './FormSettings.svelte';
    import FormPreview from './form/FormPreview.svelte';
    import {newsletterStore} from "../../../../lib/stores/newsletterStore.js";

    let {preview = $bindable(false)} = $props();
</script>

<div class="modal-wrap">
    <Modal bind:show={preview} title="Signup Form Preview" size="large" role="dialog">
        <div class="content">
            <div class="settings">
                <FormSettings/>
            </div>

            <div class="preview">
                <FormPreview id={$newsletterStore.id}/>
            </div>
        </div>
    </Modal>
</div>

<style>
    .modal-wrap :global(.inner[role='dialog']) {
        width: 100%;
        height: 100%;

        display: flex;
        flex-direction: column;
    }

    .modal-wrap :global(.inner[role='dialog'] > .content) {
        flex: 1;
        overflow: hidden;
    }

    .content {
        display: flex;
        flex-direction: row;
        height: 100%;
    }

    .settings {
        width: 50%;
        height: 100%;
        border-right: 1px solid var(--border);
        overflow: auto;
        padding-right: 20px;
    }

    .preview {
        width: 50%;
        padding: 1rem;
    }
</style>
