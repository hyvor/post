<script lang="ts">
	import {
		ColorPicker,
		Dropdown,
		SplitControl,
		ActionList,
		ActionListItem,
		Button,
		toast,
		confirm,
		Slider,
		TextInput
	} from '@hyvor/design/components';
	import { newsletterStore } from '../../../lib/stores/newsletterStore';
	import { updateNewsletter } from '../../../lib/actions/newsletterActions';
	import type { NewsletterMeta, Newsletter } from '../../../types';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import { getAppConfig } from '../../../lib/stores/consoleStore';
	import SaveDiscard from '../../@components/save/SaveDiscard.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import EditTemplateModal from './EditTemplateModal.svelte';

	let hasChanges = false;
	let showFontWeight = false;
	let showFontWeightHeading = false;
	let showEditTemplateModal = false;

	const newsletterDefaults = getAppConfig().newsletter_defaults;

	function getTemplateValues() {
		return {
			template_color_accent:
				$newsletterStore.template_color_accent ?? newsletterDefaults.TEMPLATE_COLOR_ACCENT,
			template_color_background:
				$newsletterStore.template_color_background ??
				newsletterDefaults.TEMPLATE_COLOR_BACKGROUND,
			template_color_box_background:
				$newsletterStore.template_color_box_background ??
				newsletterDefaults.TEMPLATE_COLOR_BOX_BACKGROUND,
			template_box_shadow:
				$newsletterStore.template_box_shadow ?? newsletterDefaults.TEMPLATE_BOX_SHADOW,
			template_box_border:
				$newsletterStore.template_box_border ?? newsletterDefaults.TEMPLATE_BOX_BORDER,
			template_font_size:
				$newsletterStore.template_font_size ?? newsletterDefaults.TEMPLATE_FONT_SIZE,
			template_font_family:
				$newsletterStore.template_font_family ?? newsletterDefaults.TEMPLATE_FONT_FAMILY,
			template_font_weight:
				$newsletterStore.template_font_weight ?? newsletterDefaults.TEMPLATE_FONT_WEIGHT,
			template_font_weight_heading:
				$newsletterStore.template_font_weight_heading ??
				newsletterDefaults.TEMPLATE_FONT_WEIGHT_HEADING,
			template_font_color_on_background:
				$newsletterStore.template_font_color_on_background ??
				newsletterDefaults.TEMPLATE_FONT_COLOR_ON_BACKGROUND,
			template_font_color_on_box:
				$newsletterStore.template_font_color_on_box ??
				newsletterDefaults.TEMPLATE_FONT_COLOR_ON_BOX,
			template_font_line_height:
				$newsletterStore.template_font_line_height ??
				newsletterDefaults.TEMPLATE_FONT_LINE_HEIGHT,
			template_box_radius:
				$newsletterStore.template_box_radius ?? newsletterDefaults.TEMPLATE_BOX_RADIUS
		};
	}

	// Create a reactive object with all template values
	let templateValues: Partial<NewsletterMeta> = getTemplateValues();

	// Object used to track changes
	let metaToSave: Partial<NewsletterMeta> = {};

	function handleChange(key: keyof NewsletterMeta, value: string) {
		metaToSave[key] = value as any;
		templateValues[key] = value as any;
		hasChanges = true;
	}

	async function saveChanges() {
		try {
			const currentNewsletter = $newsletterStore;
			const updateData = {
				...currentNewsletter,
				...metaToSave
			};
			const updatedNewsletter: Newsletter = await updateNewsletter(updateData);
			newsletterStore.set(updatedNewsletter);
			metaToSave = {};
			hasChanges = false;
			toast.success('Changes saved successfully!');
		} catch (error: any) {
			toast.error('Failed to save changes: ' + error.message);
		}
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

	const fontSizeValues = [12, 14, 16, 18, 20, 24, 28, 32];
	const fontSizeIndex = fontSizeValues.indexOf(
		parseInt(templateValues.template_font_size ?? newsletterDefaults.TEMPLATE_FONT_SIZE)
	);
	const fontWeights = [
		'normal',
		'bold',
		'100',
		'200',
		'300',
		'400',
		'500',
		'600',
		'700',
		'800',
		'900'
	];
	const boxRadiusValues = [0, 4, 8, 12, 16];
	const boxRadiusIndex = boxRadiusValues.indexOf(
		parseInt(templateValues.template_box_radius ?? newsletterDefaults.TEMPLATE_BOX_RADIUS)
	);
	const boxBorderValues = [0, 1, 2, 3, 4];
	const currentBorder = decodeBorderValue(
		templateValues.template_box_border ?? newsletterDefaults.TEMPLATE_BOX_BORDER
	);
	const boxBorderIndex = boxBorderValues.indexOf(currentBorder.width);
	const lineHeights = ['1.2', '1.4', '1.6', '1.8', '2.0'];

	function handleFontSizeChange(value: number) {
		const size = `${value}px`;
		handleChange('template_font_size', size);
	}

	function handleBoxRadiusChange(value: number) {
		const radius = `${value}px`;
		handleChange('template_box_radius', radius);
	}

	function decodeBorderValue(border: string) {
		const [width, , color] = border.split(' ');
		return {
			width: parseInt(width),
			color: color
		};
	}

	function encodeBorderValue(width: number, color: string) {
		return `${width}px solid ${color}`;
	}

	function handleBoxBorderChange(value: number) {
		const newBorder = encodeBorderValue(value, currentBorder.color);
		handleChange('template_box_border', newBorder);
	}

	function handleBoxBorderColorChange(color: string) {
		const newBorder = encodeBorderValue(currentBorder.width, color);
		handleChange('template_box_border', newBorder);
	}
</script>

<SettingsTop>
	<Button on:click={() => (showEditTemplateModal = true)}>Edit Template</Button>
</SettingsTop>
<div class="wrap">
	<SplitControl label="Colors">
		{#snippet nested()}
			<SplitControl label="Accent">
				<ColorPicker
					color={templateValues.template_color_accent ??
						newsletterDefaults.TEMPLATE_COLOR_ACCENT}
					on:input={(e: CustomEvent<string>) =>
						handleChange('template_color_accent', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Background">
				<ColorPicker
					color={templateValues.template_color_background ??
						newsletterDefaults.TEMPLATE_COLOR_BACKGROUND}
					on:input={(e: CustomEvent<string>) =>
						handleChange('template_color_background', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box background">
				<ColorPicker
					color={templateValues.template_color_box_background ??
						newsletterDefaults.TEMPLATE_COLOR_BOX_BACKGROUND}
					on:input={(e: CustomEvent<string>) =>
						handleChange('template_color_box_background', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box shadow">
				<ColorPicker
					color={templateValues.template_box_shadow ??
						newsletterDefaults.TEMPLATE_BOX_SHADOW}
					on:input={(e: CustomEvent<string>) =>
						handleChange('template_box_shadow', e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Box border">
				<div class="box-border-controls">
					<Slider
						min={0}
						max={4}
						step={1}
						value={boxBorderIndex !== -1 ? boxBorderValues[boxBorderIndex] : 0}
						on:input={(e: CustomEvent<number>) => handleBoxBorderChange(e.detail)}
					/>
					<div class="border-color-control">
						<span class="label">Border color</span>
						<ColorPicker
							color={currentBorder.color}
							on:input={(e: CustomEvent<string>) =>
								handleBoxBorderColorChange(e.detail)}
						/>
					</div>
				</div>
			</SplitControl>
			<SplitControl label="Box radius">
				<Slider
					min={0}
					max={16}
					step={4}
					value={boxRadiusIndex !== -1 ? boxRadiusValues[boxRadiusIndex] : 0}
					on:input={(e: CustomEvent<number>) => handleBoxRadiusChange(e.detail)}
				/>
			</SplitControl>
		{/snippet}
	</SplitControl>
	<SplitControl label="Fonts">
		{#snippet nested()}
			<SplitControl label="Font Size">
				<Slider
					min={12}
					max={32}
					step={2}
					value={fontSizeIndex !== -1 ? fontSizeValues[fontSizeIndex] : 16}
					on:input={(e: CustomEvent<number>) => handleFontSizeChange(e.detail)}
				/>
			</SplitControl>
			<SplitControl label="Font Family">
				<TextInput
					value={templateValues.template_font_family}
					on:change={(e) => handleChange('template_font_family', e.target.value)}
					placeholder="Enter font family"
				/>
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
							{#each fontWeights as weight}
								<ActionListItem
									on:click={() =>
										handleChange('template_font_weight_heading', weight)}
									selected={templateValues.template_font_weight_heading ===
										weight}
								>
									{weight}
								</ActionListItem>
							{/each}
						</ActionList>
					{/snippet}
				</Dropdown>
			</SplitControl>
		{/snippet}
	</SplitControl>
</div>

{#if hasChanges}
	<SaveDiscard onsave={saveChanges} ondiscard={discardChanges} />
{/if}

<EditTemplateModal bind:show={showEditTemplateModal} />

<style lang="scss">
	.wrap {
		padding: 25px 30px 60px;
		overflow: auto;
		flex: 1;
	}

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
