<script lang="ts">
	import { ColorPicker, Dropdown, SplitControl, ActionList, ActionListItem, Button, toast, confirm } from "@hyvor/design/components";
	import { appConfig } from "../../../lib/stores/consoleStore";
	import { projectStore } from "../../../lib/stores/projectStore";
	import { updateProjectMeta } from "../../../lib/actions/projectActions";
	import type { ProjectMeta, Project, AppConfig } from "../../../types";
	import IconCaretDown from "@hyvor/icons/IconCaretDown";

	let hasChanges = false;
	let showFontSize = false;
	let showFontFamily = false;
	let showFontWeight = false;
	let showFontWeightHeading = false;
	let showBoxRadius = false;

	function getTemplateValues() {
		return {
			template_color_accent: $projectStore.template_color_accent ?? $appConfig.template_defaults.COLOR_ACCENT,
			template_color_background: $projectStore.template_color_background ?? $appConfig.template_defaults.COLOR_BACKGROUND,
			template_color_box_background: $projectStore.template_color_box_background ?? $appConfig.template_defaults.COLOR_BOX_BACKGROUND,
			template_box_shadow: $projectStore.template_box_shadow ?? $appConfig.template_defaults.BOX_SHADOW,
			template_box_border: $projectStore.template_box_border ?? $appConfig.template_defaults.BOX_BORDER,
			template_font_size: $projectStore.template_font_size ?? $appConfig.template_defaults.FONT_SIZE,
			template_font_family: $projectStore.template_font_family ?? $appConfig.template_defaults.FONT_FAMILY,
			template_font_weight: $projectStore.template_font_weight ?? $appConfig.template_defaults.FONT_WEIGHT,
			template_font_weight_heading: $projectStore.template_font_weight_heading ?? $appConfig.template_defaults.FONT_WEIGHT_HEADING,
			template_font_color_on_background: $projectStore.template_font_color_on_background ?? $appConfig.template_defaults.FONT_COLOR_ON_BACKGROUND,
			template_font_color_on_box: $projectStore.template_font_color_on_box ?? $appConfig.template_defaults.FONT_COLOR_ON_BOX,
			template_font_line_height: $projectStore.template_font_line_height ?? $appConfig.template_defaults.FONT_LINE_HEIGHT,
			template_box_radius: $projectStore.template_box_radius ?? $appConfig.template_defaults.BOX_RADIUS,
		};
	}

	// Create a reactive object with all template values
	let templateValues: Record<keyof ProjectMeta, string> = getTemplateValues();

	// Object used to track changes
	let metaToSave: Partial<ProjectMeta> = {};

	function handleChange(key: keyof ProjectMeta, value: string) {
		metaToSave[key] = value;
		templateValues[key] = value;
		hasChanges = true;
	}

	function saveChanges() {
		updateProjectMeta(metaToSave as ProjectMeta)
			.then((updatedProject: ProjectMeta) => {
				projectStore.set(updatedProject as Project);
				metaToSave = {};
				hasChanges = false;
				toast.success('Changes saved successfully!');
			})
			.catch((error) => {
				toast.error('Failed to save changes: ' + error.message);
			});
	}

	async function discardChanges() {
		const confirmation = await confirm({
			title: 'Discard changes',
			content: 'Are you sure you want to discard the changes ?',
			confirmText: 'Discard',
			cancelText: 'Cancel',
			danger: true
		});

		if (!confirmation) return;

		metaToSave = {};
		hasChanges = false;
		// Reset template values to their original state
		templateValues = getTemplateValues();
	}

	const fontSizes = ['12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px'];
	const fontWeights = ['light', 'normal', 'bold'];
	const fontWeightHeadings = ['light', 'normal', 'bold'];
	const boxRadius = ['0px', '4px', '8px', '12px', '16px'];
	const lineHeights = ['1.2', '1.4', '1.6', '1.8', '2.0'];
	const fontFamilies = ['Arial', 'Courier New', 'Georgia', 'Times New Roman', 'Verdana'];

	function selectFontSize(size: string) {
		handleChange('template_font_size', size);
		showFontSize = false;
	}
</script>

<div class="wrap">
	<div class="actions">
		<Button 
			color="accent" 
			on:click={saveChanges}
			disabled={!hasChanges}
		>
			Save Changes
		</Button>
		<Button 
			variant="outline" 
			on:click={discardChanges}
			disabled={!hasChanges}
		>
			Discard Changes
		</Button>
	</div>

	<SplitControl label="Colors">
		{#snippet nested()}
			<SplitControl label="Accent">
				<ColorPicker 
					color={templateValues.template_color_accent}
					on:input={(e: CustomEvent<string>) => handleChange('template_color_accent', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Background">
				<ColorPicker 
					color={templateValues.template_color_background}
					on:input={(e: CustomEvent<string>) => handleChange('template_color_background', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box background">
				<ColorPicker 
					color={templateValues.template_color_box_background}
					on:input={(e: CustomEvent<string>) => handleChange('template_color_box_background', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box shadow">
				<ColorPicker 
					color={templateValues.template_box_shadow}
					on:input={(e: CustomEvent<string>) => handleChange('template_box_shadow', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box border">
				<ColorPicker 
					color={templateValues.template_box_border}
					on:input={(e: CustomEvent<string>) => handleChange('template_box_border', e.detail)}
				/>
			</SplitControl>
		{/snippet}
	</SplitControl>
	<SplitControl label="Fonts">
		{#snippet nested()}
			<SplitControl label="Font Size">
				<Dropdown bind:show={showFontSize} width={200}>
					{#snippet trigger()}
						{templateValues.template_font_size}
						<IconCaretDown />
					{/snippet}
					{#snippet content()}
						<ActionList selection="single" selectionAlign="end">
							{#each fontSizes as size}
								<ActionListItem
									on:click={() => selectFontSize(size)}
									selected={templateValues.template_font_size === size}
								>
									{size}
								</ActionListItem>
							{/each}
						</ActionList>
					{/snippet}
				</Dropdown>
			</SplitControl>
			<SplitControl label="Font Family">
				<Dropdown bind:show={showFontFamily} width={200}>
					{#snippet trigger()}
						{templateValues.template_font_family}
						<IconCaretDown />
					{/snippet}
					{#snippet content()}
						<ActionList selection="single" selectionAlign="end">
							{#each fontFamilies as family}
								<ActionListItem
									on:click={() => handleChange('template_font_family', family)}
									selected={templateValues.template_font_family === family}
								>
									{family}
								</ActionListItem>
							{/each}
						</ActionList>
					{/snippet}
				</Dropdown>
			</SplitControl>
			<SplitControl label="Font Weight">
				<Dropdown bind:show={showFontWeight} width={200}>
					{#snippet trigger()}
						{templateValues.template_font_weight}
						<IconCaretDown />
					{/snippet}
					{#snippet content()}
						<ActionList selection="single" selectionAlign="end">
							{#each fontWeights as weight}
								<ActionListItem
									on:click={() => handleChange('template_font_weight', weight)}
									selected={templateValues.template_font_weight === weight}
								>
									{weight}
								</ActionListItem>
							{/each}
						</ActionList>
					{/snippet}
				</Dropdown>
			</SplitControl>

			<SplitControl label="Font Weight Heading">
				<Dropdown bind:show={showFontWeightHeading} width={200}>
					{#snippet trigger()}
						{templateValues.template_font_weight_heading}
						<IconCaretDown />
					{/snippet}
					{#snippet content()}
						<ActionList selection="single" selectionAlign="end">
							{#each fontWeightHeadings as weight}
								<ActionListItem
									on:click={() => handleChange('template_font_weight_heading', weight)}
									selected={templateValues.template_font_weight_heading === weight}
								>
									{weight}
								</ActionListItem>
							{/each}
						</ActionList>
					{/snippet}
				</Dropdown>
			</SplitControl>
			<SplitControl label="Box radius">
				<Dropdown bind:show={showBoxRadius} width={200}>
					{#snippet trigger()}
						{templateValues.template_box_radius}
						<IconCaretDown />
					{/snippet}
					{#snippet content()}
						<ActionList selection="single" selectionAlign="end">
							{#each boxRadius as radius}
								<ActionListItem
									on:click={() => handleChange('template_box_radius', radius)}
									selected={templateValues.template_box_radius === radius}
								>
									{radius}
								</ActionListItem>
							{/each}
						</ActionList>
					{/snippet}
				</Dropdown>
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

	.actions {
		display: flex;
		gap: 10px;
		margin-bottom: 20px;
	}
</style>
