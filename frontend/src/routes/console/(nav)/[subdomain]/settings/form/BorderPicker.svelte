<script lang="ts">
	import { ColorPicker, Slider } from '@hyvor/design/components';

	interface Props {
		value: string;
		oninput?: (value: string) => void;
	}

	let { value = $bindable(), oninput }: Props = $props();

	let borderState = $state(parseValue(value));

	function parseValue(value: string) {
		const values = value.split(' ');
		return {
			width: parseInt(values[0]),
			style: values[1],
			color: values[2]
		};
	}

	function handleInput() {
		const newValue = `${borderState.width}px ${borderState.style} ${borderState.color}`;
		value = newValue;
		oninput?.(newValue);
	}
</script>

<div class="wrap">
	<Slider
		min={0}
		max={10}
		step={1}
		bind:value={borderState.width}
		onchange={handleInput}
		valueFormat={(val) => `${val}px`}
	></Slider>

	<ColorPicker
		color={borderState.color}
		oninput={(val) => {
			borderState.color = val;
			handleInput();
		}}
	></ColorPicker>
</div>

<style>
	.wrap {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}
</style>
