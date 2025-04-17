<script lang="ts">
	import { onMount } from 'svelte';
	import CodemirrorEditor from '../../console/lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { getDefaultTemplate, previewTemplateFromVariable } from './lib/actions/templateActions';
	import { Button, IconButton } from '@hyvor/design/components';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import EditContentModal from './EditContentModal.svelte';

	let defaultsLoaded = $state(false);
	let template = $state('');
	let variables = $state('{}');
	let previewHtml = $state('');

	let showEditContentModal = $state(false);

	function getDefault() {
		getDefaultTemplate()
			.then((res) => {
				template = res.template;
				variables = JSON.stringify(res.variables, null, 2);
			})
			.catch((err) => {
				console.error(err);
			})
			.finally(() => {
				defaultsLoaded = true;
			});
	}

	function handleContentUpdate(newContent: string) {
		try {
			const variablesObj = JSON.parse(variables);
			variablesObj.content = newContent;
			variables = JSON.stringify(variablesObj, null, 2);
		} catch (err) {
			console.error('Error updating variables:', err);
			// Initialize with empty object if parsing fails
			variables = JSON.stringify({ content: newContent }, null, 2);
		}
	}

	function getContentFromVariables(): string {
		try {
			const variablesObj = JSON.parse(variables);
			console.log(variablesObj)
			return variablesObj?.content || '';
		} catch (err) {
			return '';
		}
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
		getDefault();
	});
</script>

<EditContentModal
	bind:show={showEditContentModal}
	content={getContentFromVariables()}
	onContentUpdate={handleContentUpdate}
/>

<div class="demo-view">
	<div class="column">
		<div class="column-title">template.twig</div>
		<div class="column-content">
			<div class="hds-box">
				{#if defaultsLoaded}
					<CodemirrorEditor
						ext="twig"
						bind:value={template}
						change={(e) => (template = e)}
					/>
				{/if}
			</div>
		</div>
	</div>

	<div class="column">
		<div class="column-title">
			variables.json
			<Button on:click={() => (showEditContentModal = true)}>Edit content</Button>
		</div>
		<div class="column-content">
			<div class="hds-box">
				{#if defaultsLoaded}
					<CodemirrorEditor
						value={variables}
						ext="json"
						change={(e) => (variables = e)}
						id={0}
					/>
				{/if}
			</div>
		</div>
	</div>

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

<style lang="scss">
	.demo-view {
		display: flex;
		height: 100vh;

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
		}

		.hds-box {
			height: 100%;
		}

		.user-interface-wrap {
			overflow: auto;
			.hds-box {
				padding: 30px;
			}
		}

		:global(.CodeMirror) {
			background-color: transparent !important;
			min-height: 100%;
			:global(.CodeMirror-gutters) {
				background-color: transparent !important;
			}
		}
	}
</style>
