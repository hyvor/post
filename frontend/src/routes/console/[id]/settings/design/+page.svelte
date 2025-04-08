<script lang="ts">
	import { Caption, ColorPicker, Dropdown, FormControl, Label, SplitControl, Switch } from "@hyvor/design/components";
	import type { TemplateVariables } from "../../../../(tools)/design/types";

    let template = '';

	let templateVariables: TemplateVariables;

</script>

<div class="wrap">
	<SplitControl label="Colors">
		{#snippet nested()}
			<SplitControl label="Accent">
				<ColorPicker />
			</SplitControl>
			<SplitControl label="Background">
				<ColorPicker />
			</SplitControl>
			<SplitControl label="Box background">
				<ColorPicker />
			</SplitControl>
			<SplitControl label="Box background">
				<ColorPicker />
			</SplitControl>
			<SplitControl label="Box shadow">
				<ColorPicker />
			</SplitControl>
			<SplitControl label="Box border">
				<ColorPicker />
			</SplitControl>
		{/snippet}
	</SplitControl>
	<SplitControl label="Fonts">
		{#snippet nested()}
			<SplitControl label="Font Size">
				<Dropdown></Dropdown>
			</SplitControl>
		{/snippet}
	</SplitControl>
</div>

<style lang="scss">
	.wrap {
		padding: 25px 30px 60px;
		overflow: auto;
		flex: 1;
	}

</style>
