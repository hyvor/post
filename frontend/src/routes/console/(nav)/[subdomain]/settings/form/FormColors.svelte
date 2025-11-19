<script lang="ts">
	import {
		ColorPicker,
		FormControl,
		BoxShadowPicker,
		SplitControl,
		Slider
	} from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';
	import { newsletterEditingStore } from '../../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../../lib/stores/consoleStore';
	import BorderPicker from './BorderPicker.svelte';

	let { palette }: { palette: 'light' | 'dark' } = $props();

	const i18n = getI18n();
	const newsletterDefaults = getAppConfig().newsletter_defaults;
</script>

<SplitControl
	label={i18n.t('console.settings.form.textColor')}
	caption={i18n.t('console.settings.form.textColorCaption')}
>
	<FormControl>
		<ColorPicker
			color={palette === 'light'
				? $newsletterEditingStore.form_color_light_text ||
					newsletterDefaults.FORM_COLOR_LIGHT_TEXT
				: $newsletterEditingStore.form_color_dark_text ||
					newsletterDefaults.FORM_COLOR_DARK_TEXT}
			on:input={(e) => {
				palette === 'light'
					? ($newsletterEditingStore.form_color_light_text = e.detail)
					: ($newsletterEditingStore.form_color_dark_text = e.detail);
			}}
		/>
	</FormControl>
</SplitControl>

<SplitControl
	label={i18n.t('console.settings.form.accentColor')}
	caption={i18n.t('console.settings.form.accentColorCaption')}
>
	{#snippet nested()}
		<SplitControl label={i18n.t('console.settings.form.colorBackground')}>
			<ColorPicker
				color={palette === 'light'
					? $newsletterEditingStore.form_color_light_accent ||
						newsletterDefaults.FORM_COLOR_LIGHT_ACCENT
					: $newsletterEditingStore.form_color_dark_accent ||
						newsletterDefaults.FORM_COLOR_DARK_ACCENT}
				on:input={(e) => {
					palette === 'light'
						? ($newsletterEditingStore.form_color_light_accent = e.detail)
						: ($newsletterEditingStore.form_color_dark_accent = e.detail);
				}}
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.colorText')}>
			<ColorPicker
				color={palette === 'light'
					? $newsletterEditingStore.form_color_light_accent_text ||
						newsletterDefaults.FORM_COLOR_LIGHT_ACCENT_TEXT
					: $newsletterEditingStore.form_color_dark_accent_text ||
						newsletterDefaults.FORM_COLOR_DARK_ACCENT_TEXT}
				on:input={(e) => {
					palette === 'light'
						? ($newsletterEditingStore.form_color_light_accent_text = e.detail)
						: ($newsletterEditingStore.form_color_dark_accent_text = e.detail);
				}}
			/>
		</SplitControl>
	{/snippet}
</SplitControl>

<SplitControl
	label={i18n.t('console.settings.form.inputColor')}
	caption={i18n.t('console.settings.form.inputColorCaption')}
>
	{#snippet nested()}
		<SplitControl label={i18n.t('console.settings.form.colorBackground')}>
			<ColorPicker
				color={palette === 'light'
					? $newsletterEditingStore.form_color_light_input ||
						newsletterDefaults.FORM_COLOR_LIGHT_INPUT
					: $newsletterEditingStore.form_color_dark_input ||
						newsletterDefaults.FORM_COLOR_DARK_INPUT}
				on:input={(e) => {
					palette === 'light'
						? ($newsletterEditingStore.form_color_light_input = e.detail)
						: ($newsletterEditingStore.form_color_dark_input = e.detail);
				}}
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.colorText')}>
			<ColorPicker
				color={palette === 'light'
					? $newsletterEditingStore.form_color_light_input_text ||
						newsletterDefaults.FORM_COLOR_LIGHT_INPUT_TEXT
					: $newsletterEditingStore.form_color_dark_input_text ||
						newsletterDefaults.FORM_COLOR_DARK_INPUT_TEXT}
				on:input={(e) => {
					palette === 'light'
						? ($newsletterEditingStore.form_color_light_input_text = e.detail)
						: ($newsletterEditingStore.form_color_dark_input_text = e.detail);
				}}
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.boxShadow')}>
			<BoxShadowPicker
				value={palette === 'light'
					? $newsletterEditingStore.form_light_input_box_shadow ||
						newsletterDefaults.FORM_LIGHT_INPUT_BOX_SHADOW
					: $newsletterEditingStore.form_dark_input_box_shadow ||
						newsletterDefaults.FORM_DARK_INPUT_BOX_SHADOW}
				oninput={(value) => {
					palette === 'light'
						? ($newsletterEditingStore.form_light_input_box_shadow = value)
						: ($newsletterEditingStore.form_dark_input_box_shadow = value);
				}}
				position="top"
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.border')}>
			<BorderPicker
				value={palette === 'light'
					? $newsletterEditingStore.form_light_input_border ||
						newsletterDefaults.FORM_LIGHT_INPUT_BORDER
					: $newsletterEditingStore.form_dark_input_border ||
						newsletterDefaults.FORM_DARK_INPUT_BORDER}
				oninput={(value) => {
					palette === 'light'
						? ($newsletterEditingStore.form_light_input_border = value)
						: ($newsletterEditingStore.form_dark_input_border = value);
				}}
			/>
		</SplitControl>
	{/snippet}
</SplitControl>

<SplitControl
	label={i18n.t('console.settings.form.roundness')}
	caption={i18n.t('console.settings.form.roundnessCaption')}
>
	<Slider
		min={0}
		max={30}
		valueFormat={(value) => `${value}px`}
		value={parseInt(
			palette === 'light'
				? ($newsletterEditingStore.form_light_border_radius ??
						newsletterDefaults.FORM_LIGHT_BORDER_RADIUS)
				: ($newsletterEditingStore.form_dark_border_radius ??
						newsletterDefaults.FORM_DARK_BORDER_RADIUS)
		)}
		onchange={(value) => {
			palette === 'light'
				? ($newsletterEditingStore.form_light_border_radius = value.toString())
				: ($newsletterEditingStore.form_dark_border_radius = value.toString());
		}}
	/>
</SplitControl>
