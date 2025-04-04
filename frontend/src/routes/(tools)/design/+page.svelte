<script>
	import { onMount } from 'svelte';
	import CodemirrorEditor from '../../console/lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { getDefaultTemplate } from './lib/actions/templateActions';

	let template = '';
	let variables = '';

	function getDefault() {
		getDefaultTemplate()
			.then((res) => {
				if (res) {
					template = res.template;
					variables = res.variables;
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

<div class="demo-view">
	<div class="column">
		<div class="column-title">template.twig</div>
		<div class="column-content">
			<div class="hds-box">
				<CodemirrorEditor
					ext="twig"
					bind:value={template}
					change={(e) => (template = e)}
					id={template}
				/>
			</div>
		</div>
	</div>

	<div class="column">
		<div class="column-title">variables.json</div>
		<div class="column-content">
			<div class="hds-box">
				<CodemirrorEditor
					value={variables}
					ext="json"
					change={(e) => (variables = e)}
					id={variables}
				/>
			</div>
		</div>
	</div>

	<div class="column">
		<div class="column-title">Preview</div>
		<div class="column-content user-interface-wrap">
			<div class="hds-box"></div>
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
