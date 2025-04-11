<script lang="ts">
	import { ColorPicker, Dropdown, SplitControl, ActionList, ActionListItem, Button, toast, confirm } from "@hyvor/design/components";
	import { appConfig } from "../../../lib/stores/consoleStore";
	import { projectStore } from "../../../lib/stores/projectStore";
	import { updateProjectMeta } from "../../../lib/actions/projectActions";
	import type { ProjectMeta, Project, AppConfig } from "../../../types";

	let hasChanges = false;
	let showFontSize = false;

	// Create a reactive object with all template values
	let templateValues: Record<keyof ProjectMeta, string> = {
		templateColorAccent: $projectStore.templateColorAccent ?? $appConfig.template_defaults.COLOR_ACCENT,
		templateColorBackground: $projectStore.templateColorBackground ?? $appConfig.template_defaults.COLOR_BACKGROUND,
		templateColorBoxBackground: $projectStore.templateColorBoxBackground ?? $appConfig.template_defaults.COLOR_BOX_BACKGROUND,
		templateColorBoxShadow: $projectStore.templateColorBoxShadow ?? $appConfig.template_defaults.COLOR_BOX_SHADOW,
		templateColorBoxBorder: $projectStore.templateColorBoxBorder ?? $appConfig.template_defaults.COLOR_BOX_BORDER,
		templateFontSize: $projectStore.templateFontSize ?? $appConfig.template_defaults.FONT_SIZE,
		templateFontFamily: $projectStore.templateFontFamily ?? $appConfig.template_defaults.FONT_FAMILY,
		templateFontWeight: $projectStore.templateFontWeight ?? $appConfig.template_defaults.FONT_WEIGHT,
		templateFontWeightHeading: $projectStore.templateFontWeightHeading ?? $appConfig.template_defaults.FONT_WEIGHT_HEADING,
		templateFontColorOnBackground: $projectStore.templateFontColorOnBackground ?? $appConfig.template_defaults.FONT_COLOR_ON_BACKGROUND,
		templateFontColorOnBox: $projectStore.templateFontColorOnBox ?? $appConfig.template_defaults.FONT_COLOR_ON_BOX,
		templateFontLineHeight: $projectStore.templateFontLineHeight ?? $appConfig.template_defaults.FONT_LINE_HEIGHT,
		templateBoxRadius: $projectStore.templateBoxRadius ?? $appConfig.template_defaults.BOX_RADIUS,
	};

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
		templateValues = {
			templateColorAccent: $projectStore.templateColorAccent ?? $appConfig.template_defaults.COLOR_ACCENT,
			templateColorBackground: $projectStore.templateColorBackground ?? $appConfig.template_defaults.COLOR_BACKGROUND,
			templateColorBoxBackground: $projectStore.templateColorBoxBackground ?? $appConfig.template_defaults.COLOR_BOX_BACKGROUND,
			templateColorBoxShadow: $projectStore.templateColorBoxShadow ?? $appConfig.template_defaults.COLOR_BOX_SHADOW,
			templateColorBoxBorder: $projectStore.templateColorBoxBorder ?? $appConfig.template_defaults.COLOR_BOX_BORDER,
			templateFontSize: $projectStore.templateFontSize ?? $appConfig.template_defaults.FONT_SIZE,
			templateFontFamily: $projectStore.templateFontFamily ?? $appConfig.template_defaults.FONT_FAMILY,
			templateFontWeight: $projectStore.templateFontWeight ?? $appConfig.template_defaults.FONT_WEIGHT,
			templateFontWeightHeading: $projectStore.templateFontWeightHeading ?? $appConfig.template_defaults.FONT_WEIGHT_HEADING,
			templateFontColorOnBackground: $projectStore.templateFontColorOnBackground ?? $appConfig.template_defaults.FONT_COLOR_ON_BACKGROUND,
			templateFontColorOnBox: $projectStore.templateFontColorOnBox ?? $appConfig.template_defaults.FONT_COLOR_ON_BOX,
			templateFontLineHeight: $projectStore.templateFontLineHeight ?? $appConfig.template_defaults.FONT_LINE_HEIGHT,
			templateBoxRadius: $projectStore.templateBoxRadius ?? $appConfig.template_defaults.BOX_RADIUS,
		};
	}

	const fontSizes = ['12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px'];

	function selectFontSize(size: string) {
		handleChange('templateFontSize', size);
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
					color={templateValues.templateColorAccent}
					on:input={(e: CustomEvent<string>) => handleChange('templateColorAccent', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Background">
				<ColorPicker 
					color={templateValues.templateColorBackground}
					on:input={(e: CustomEvent<string>) => handleChange('templateColorBackground', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box background">
				<ColorPicker 
					color={templateValues.templateColorBoxBackground}
					on:input={(e: CustomEvent<string>) => handleChange('templateColorBoxBackground', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box shadow">
				<ColorPicker 
					color={templateValues.templateColorBoxShadow}
					on:input={(e: CustomEvent<string>) => handleChange('templateColorBoxShadow', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box border">
				<ColorPicker 
					color={templateValues.templateColorBoxBorder}
					on:input={(e: CustomEvent<string>) => handleChange('templateColorBoxBorder', e.detail)}
				/>
			</SplitControl>
		{/snippet}
	</SplitControl>
	<SplitControl label="Fonts">
		{#snippet nested()}
			<SplitControl label="Font Size">
				<Dropdown bind:show={showFontSize} width={200}>
					{#snippet trigger()}
						{templateValues.templateFontSize}
					{/snippet}
					{#snippet content()}
						<ActionList selection="single" selectionAlign="end">
							{#each fontSizes as size}
								<ActionListItem
									on:click={() => selectFontSize(size)}
									selected={templateValues.templateFontSize === size}
								>
									{size}
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
