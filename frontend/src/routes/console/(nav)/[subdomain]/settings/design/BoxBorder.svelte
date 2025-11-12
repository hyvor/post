<script lang="ts">
	import { ColorPicker, Slider } from '@hyvor/design/components';
	import { newsletterEditingStore } from '../../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../../lib/stores/consoleStore';

	const newsletterDefaults = getAppConfig().newsletter_defaults;

	const currentState = $state(
		decodeBorderValue(
			$newsletterEditingStore.template_box_border ?? newsletterDefaults.TEMPLATE_BOX_BORDER
		)
	);

	function decodeBorderValue(border: string) {
		const [width, , color] = border.split(' ');
		return {
			width: parseInt(width),
			color: color
		};
	}

	function handleBoxBorderColorChange(color: string) {
		currentState.color = color;
		updateBoxBorder();
	}
	function handleBoxBorderWidthChange(width: number) {
		currentState.width = width;
		updateBoxBorder();
	}

	function updateBoxBorder() {
		const border = `${currentState.width}px solid ${currentState.color}`;
		$newsletterEditingStore.template_box_border = border;
	}
</script>

<div class="box-border-controls">
	<Slider
		min={0}
		max={10}
		step={1}
		value={currentState.width}
		valueFormat={(value: number) => `${value}px`}
		onchange={(val: number) => handleBoxBorderWidthChange(val)}
	/>
	<div class="border-color-control">
		<span class="label">Border color</span>
		<ColorPicker
			color={currentState.color}
			on:input={(e: CustomEvent<string>) => handleBoxBorderColorChange(e.detail)}
		/>
	</div>
</div>

<style>
	.box-border-controls {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	.border-color-control {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.label {
		font-size: 14px;
		color: var(--text-secondary);
		min-width: 80px;
	}
</style>
