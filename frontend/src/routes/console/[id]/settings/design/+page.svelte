<script lang="ts">
	import { Caption, Label, SplitControl, Switch } from "@hyvor/design/components";
	import CodemirrorEditor from "../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";

    let useCustomDesign = false;
    let template = '';
    let variables = '';

	console.log(useCustomDesign)

</script>

<div class="design-settings">
	<SplitControl>
        {#snippet label()}
            <Label>
                Custom Design
            </Label>
        {/snippet}
        {#snippet caption()}
            <Caption>
                Use custom design for your emails. You can use Twig and JSON to create a custom design.
                <br />
                You can use <a href="http://post.hyvor.com/design" target="_blank">http://post.hyvor.com/design</a> to preview your design.
            </Caption>
        {/snippet}
		<Switch
			bind:checked={useCustomDesign}
            
		/>
	</SplitControl>
    {#if useCustomDesign}

        <div class="content">
            <div class="code-editor">
                template.twig
				<CodemirrorEditor
					ext="twig"
					bind:value={template}
					change={(e) => (template = e)}
					id={template}
				/>
			</div>

            <div class="code-editor">
                variables.json
				<CodemirrorEditor
					ext="json"
					bind:value={variables}
					change={(e) => (variables = e)}
					id={template}
				/>
			</div>
        </div>
    {/if}
</div>

<style lang="scss">
	.design-settings {
		padding: 15px 30px;
	}

	.content {
		color: var(--text-light);
		font-size: 14px;
	}

    :global(.CodeMirror) {
			background-color: transparent !important;
			min-height: 100%;
			:global(.CodeMirror-gutters) {
				background-color: transparent !important;
			}
	}

    .code-editor {
        margin: 10px 0;
        padding: 10px;
        height: 100%;
    }
</style>
