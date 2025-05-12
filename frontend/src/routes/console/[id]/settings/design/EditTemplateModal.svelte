<script lang="ts">
	import { IconButton, Modal } from "@hyvor/design/components";
	import { getTemplate, previewTemplateFromVariable } from "../../../../(tools)/design/lib/actions/templateActions";
	import CodemirrorEditor from "../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";
	import { onMount } from "svelte";
	import IconArrowClockwise from "@hyvor/icons/IconArrowClockwise";

    export let show: boolean;

    let previewHtml: string;
    let templateLoaded = false;
    let template = '';

    function fetchTemplate() {
        getTemplate()
			.then((res) => {
				template = res.template;
			})
			.catch((err) => {
				console.error(err);
			})
			.finally(() => {
				templateLoaded = true;
			});
    }

    function fetchPreview() {
		previewTemplateFromVariable(template, variables)
			.then((res) => {
				if (res) {
					previewHtml = res.html;
				}
			})
			.catch((err) => {
				console.error(err);
			});
	}

    onMount(() => {
        fetchTemplate();
    });
</script>

<Modal
    bind:show={show}
    title="Edit template"
    size="large"
    role="dialog"
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
                    change={(e) => (template = e)}
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
