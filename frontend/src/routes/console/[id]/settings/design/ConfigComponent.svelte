<script lang="ts">
	import {
		ColorPicker,
		SplitControl,
		TextInput,
		BoxShadowPicker,
		Switch
	} from '@hyvor/design/components';
	import { newsletterEditingStore } from '../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../lib/stores/consoleStore';
	import NewsletterSaveDiscard from '../../@components/save/NewsletterSaveDiscard.svelte';
	import BoxBorder from './BoxBorder.svelte';
	import BoxRadius from './BoxRadius.svelte';
	import FontSize from './FontSize.svelte';
	import FontWeight from './FontWeight.svelte';
	import { setContext } from 'svelte';
	import { saveDiscardBoxClassContextName } from '../../@components/save/save';

	let {
		onsave
	}: {
		onsave?: () => void;
	} = $props();

	const newsletterDefaults = getAppConfig().newsletter_defaults;

	setContext(saveDiscardBoxClassContextName, 'config-component-wrap');
</script>

<div class="wrap config-component-wrap">
	<SplitControl label="Colors">
		{#snippet nested()}
			<SplitControl label="Accent">
				<ColorPicker
					color={$newsletterEditingStore.template_color_accent ??
						newsletterDefaults.TEMPLATE_COLOR_ACCENT}
					oninput={(val) => ($newsletterEditingStore.template_color_accent = val)}
				/>
			</SplitControl>
			<SplitControl label="Accent Text">
				<ColorPicker
					color={$newsletterEditingStore.template_color_accent_text ??
						newsletterDefaults.TEMPLATE_COLOR_ACCENT_TEXT}
					oninput={(val) => ($newsletterEditingStore.template_color_accent_text = val)}
				/>
			</SplitControl>

			<SplitControl label="Background">
				<ColorPicker
					color={$newsletterEditingStore.template_color_background ??
						newsletterDefaults.TEMPLATE_COLOR_BACKGROUND}
					oninput={(val) => ($newsletterEditingStore.template_color_background = val)}
				/>
			</SplitControl>

			<SplitControl label="Background Text">
				<ColorPicker
					color={$newsletterEditingStore.template_color_background_text ??
						newsletterDefaults.TEMPLATE_COLOR_BACKGROUND_TEXT}
					oninput={(val) =>
						($newsletterEditingStore.template_color_background_text = val)}
				/>
			</SplitControl>

			<SplitControl label="Box">
				<ColorPicker
					color={$newsletterEditingStore.template_color_box ??
						newsletterDefaults.TEMPLATE_COLOR_BOX}
					oninput={(val) => ($newsletterEditingStore.template_color_box = val)}
				/>
			</SplitControl>
			<SplitControl label="Box Text">
				<ColorPicker
					color={$newsletterEditingStore.template_color_box_text ??
						newsletterDefaults.TEMPLATE_COLOR_BOX_TEXT}
					oninput={(val) => ($newsletterEditingStore.template_color_box_text = val)}
				/>
			</SplitControl>
			<SplitControl label="Box shadow">
				<BoxShadowPicker
					value={$newsletterEditingStore.template_box_shadow ??
						newsletterDefaults.TEMPLATE_BOX_SHADOW}
					oninput={(val) => ($newsletterEditingStore.template_box_shadow = val)}
				/>
			</SplitControl>
			<SplitControl label="Box border">
				<BoxBorder />
			</SplitControl>
			<SplitControl label="Box radius">
				<BoxRadius />
			</SplitControl>
		{/snippet}
	</SplitControl>
	<SplitControl label="Fonts">
		{#snippet nested()}
			<SplitControl label="Font Size">
				<FontSize />
			</SplitControl>
			<SplitControl label="Font Family">
				<TextInput
					value={$newsletterEditingStore.template_font_family ??
						newsletterDefaults.TEMPLATE_FONT_FAMILY}
					on:input={(e) =>
						($newsletterEditingStore.template_font_family = e.target.value)}
					block
				/>
			</SplitControl>
			<SplitControl label="Font Weight">
				<FontWeight />
			</SplitControl>

			<SplitControl label="Font Weight Heading">
				<FontWeight heading />
			</SplitControl>

			<SplitControl label="Font Line Height">
				<TextInput
					value={$newsletterEditingStore.template_font_line_height ??
						newsletterDefaults.TEMPLATE_FONT_LINE_HEIGHT}
					on:input={(e) =>
						($newsletterEditingStore.template_font_line_height = e.target.value)}
					block
				/>
			</SplitControl>
		{/snippet}
	</SplitControl>

	<SplitControl
		label="Branding"
		caption="Show 'Sent Privately via Hyvor Post' at the bottom of the newsletter."
	>
		<Switch
			checked={$newsletterEditingStore.branding}
			on:change={(e) => ($newsletterEditingStore.branding = e.target.checked)}
		/>
	</SplitControl>

	<NewsletterSaveDiscard
		keys={[
			'template_color_accent',
			'template_color_accent_text',
			'template_color_background',
			'template_color_background_text',
			'template_color_box',
			'template_color_box_text',
			'template_box_shadow',
			'template_box_border',
			'template_font_size',
			'template_font_family',
			'template_font_weight',
			'template_font_weight_heading',
			'template_font_line_height',
			'template_box_radius',
			'branding'
		]}
		{onsave}
	/>
</div>
