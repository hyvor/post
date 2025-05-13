<script lang="ts">
	import { IconButton, Modal, toast } from "@hyvor/design/components";
	import { getTemplate, previewTemplate, previewTemplateFromVariable, updateTemplate } from "../../../../(tools)/design/lib/actions/templateActions";
	import CodemirrorEditor from "../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";
	import { onMount, onDestroy } from "svelte";
	import IconArrowClockwise from "@hyvor/icons/IconArrowClockwise";

    export let show: boolean;

    let previewHtml: string;
    let templateLoaded = false;
    let template = '';
    let typingTimeout: number | null | undefined = null;
    let previewInterval: number | null | undefined =  null;

    function fetchTemplate() {
        getTemplate()
            .then((res) => {
                template = res.template; // Accessing the `template` property from the custom type
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                templateLoaded = true;
            });
    }

    function fetchPreview() {
        previewTemplate(template)
            .then((res) => {
                if (res) {
                    previewHtml = res.html;
                }
            })
            .catch((err) => {
                console.error(err);
            });
    }

    function saveTemplate() {
        updateTemplate(template)
            .then((res) => {
                if (res) {
                    template = res.template;
                    show = false;
                    toast.success('Template successfully updated')
                }
            })
            .catch((err) => {
                console.error(err);
            });
    }

    function handleTemplateChange(value: string) {
        template = value;
        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }

        if (previewInterval) {
            clearInterval(previewInterval);
            previewInterval = null;
        }
        typingTimeout = setTimeout(() => {
            startPreviewInterval();
        }, 1000);
    }

    function startPreviewInterval() {
        if (!previewInterval) {
            previewInterval = setInterval(() => {
                fetchPreview();
            }, 3000);
        }
    }

    onMount(() => {
        fetchTemplate();
        fetchPreview();
    });

    $: if (show) {
        fetchTemplate();
        fetchPreview();
    } else {
        if (previewInterval) {
            clearInterval(previewInterval);
            previewInterval = null;
        }
    }

    onDestroy(() => {
        if (previewInterval) {
            clearInterval(previewInterval);
        }
    });
</script>

<div class="modal-wrap">
    <Modal
        bind:show={show}
        title="Edit template"
        size="large"
        role="dialog"
        footer={{
            cancel: {
                text: 'Cancel',
            }, 
            confirm: {
                text: 'Save'
            }
        }}
        on:cancel={() => show = false}
        on:confirm={() => saveTemplate()}
    >
        <div class="content">
            {#if templateLoaded}
                <div class="column">
                    <div class="column-title">
                        template.twig
                    </div>
                    <CodemirrorEditor
                        ext="twig"
                        bind:value={template}
                        change={(e) => (handleTemplateChange(e))}
                    />
                </div>
            {/if}
                <div class="preview">
                    <div class="column">
                        <div class="column-title">
                            Preview
                            <IconButton size="small" color="input" on:click={fetchPreview}>
                                <IconArrowClockwise size={12} />
                            </IconButton>
                        </div>
                        <div class="column-content user-interface-wrap hds-box">
                            {@html previewHtml}
                        </div>
                </div>
            </div>
        </div>
    </Modal>
</div>

<style lang="scss">
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

	.preview {
		width: 50%;
		padding: 1rem;
	}

    .column {
			flex: 2;
			display: flex;
			flex-direction: column;
			margin: 0 10px;
			flex-shrink: 0;
			min-width: 0;
		}
    .column:nth-child(3) {
        flex: 3;
    }

    .column-title {
        font-size: 16px;
        font-weight: 600;
        padding: 10px;
        text-align: center;
    }

    .column-content {
        flex: 1;
        overflow: auto;
        background-color: var(--hds-color-background);
    }
</style>
