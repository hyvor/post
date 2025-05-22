<script lang="ts">
	import {
		ColorPicker,
		FormControl,
		BoxShadowPicker,
		SplitControl,
		Slider
	} from '@hyvor/design/components';
	import { getI18n } from '../../../lib/i18n';
	import { projectEditingStore, projectStore } from '../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../lib/stores/consoleStore';
	import BorderPicker from './BorderPicker.svelte';

	let { palette }: { palette: 'light' | 'dark' } = $props();

	const i18n = getI18n();
	const projectDefaults = getAppConfig().newsletter_defaults;
</script>

<SplitControl
	label={i18n.t('console.settings.form.textColor')}
	caption={i18n.t('console.settings.form.textColorCaption')}
>
	<FormControl>
		<ColorPicker
			color={$projectEditingStore.form_color_light_text ||
				projectDefaults.FORM_COLOR_LIGHT_TEXT}
			on:input={(e) => {
				$projectEditingStore.form_color_light_text = e.detail;
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
				color={$projectEditingStore.form_color_light_accent ||
					projectDefaults.FORM_COLOR_LIGHT_ACCENT}
				on:input={(e) => {
					$projectEditingStore.form_color_light_accent = e.detail;
				}}
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.colorText')}>
			<ColorPicker
				color={$projectEditingStore.form_color_light_accent_text ||
					projectDefaults.FORM_COLOR_LIGHT_ACCENT_TEXT}
				on:input={(e) => {
					$projectEditingStore.form_color_light_accent_text = e.detail;
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
				color={$projectEditingStore.form_color_light_input ||
					projectDefaults.FORM_COLOR_LIGHT_INPUT}
				on:input={(e) => {
					$projectEditingStore.form_color_light_input = e.detail;
				}}
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.colorText')}>
			<ColorPicker
				color={$projectEditingStore.form_color_light_input_text ||
					projectDefaults.FORM_COLOR_LIGHT_INPUT_TEXT}
				on:input={(e) => {
					$projectEditingStore.form_color_light_input_text = e.detail;
				}}
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.boxShadow')}>
			<BoxShadowPicker
				value={$projectEditingStore.form_light_input_box_shadow ||
					projectDefaults.FORM_COLOR_LIGHT_INPUT_TEXT}
				oninput={(value) => {
					$projectEditingStore.form_light_input_box_shadow = value;
				}}
				position="top"
			/>
		</SplitControl>
		<SplitControl label={i18n.t('console.settings.form.border')}>
			<BorderPicker
				value={$projectEditingStore.form_light_input_border ||
					projectDefaults.FORM_LIGHT_INPUT_BORDER}
				oninput={(value) => {
					console.log(value);
					$projectEditingStore.form_light_input_border = value;
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
			$projectEditingStore.form_light_border_radius ??
				projectDefaults.FORM_LIGHT_BORDER_RADIUS
		)}
		onchange={(value) => {
			$projectEditingStore.form_light_border_radius = value.toString();
		}}
	/>
</SplitControl>
