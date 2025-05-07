<script lang="ts">
	import { ColorPicker, FormControl, Radio, SplitControl } from '@hyvor/design/components';
	import { getI18n } from '../../../lib/i18n';
	import { projectEditingStore, projectStore } from '../../../lib/stores/projectStore';
	import { getAppConfig } from '../../../lib/stores/consoleStore';

	let { palette }: { palette: 'light' | 'dark' } = $props();

	const i18n = getI18n();

	let textValue: 'inherit' | 'custom' = $state(
		$projectEditingStore.form_color_light_text === null ? 'inherit' : 'custom'
	);

	const projectDefaults = getAppConfig().project_defaults;

	$effect(() => {
		console.log('textValue', textValue);
		if (textValue === 'inherit') {
			$projectEditingStore.form_color_light_text = null;
		} else {
			$projectEditingStore.form_color_light_text =
				$projectStore.form_color_light_text ||
				(palette === 'light' ? '#000000' : '#ffffff');
		}
	});
</script>

<SplitControl
	label={i18n.t('console.settings.form.textColor')}
	caption={i18n.t('console.settings.form.textColorCaption')}
>
	<FormControl>
		<Radio name="color-text-inherit" bind:group={textValue} value="inherit">
			{i18n.t('console.settings.form.textColorInherit')}
		</Radio>
		<Radio name="color-text-inherit" bind:group={textValue} value="custom">
			{i18n.t('console.settings.form.textColorCustom')}
		</Radio>

		{#if textValue === 'custom'}
			<ColorPicker
				color={$projectEditingStore.form_color_light_text ||
					(palette === 'light' ? '#000000' : '#ffffff')}
				on:input={(e) => {
					$projectEditingStore.form_color_light_text = e.detail;
				}}
			/>
		{/if}
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
	{/snippet}
</SplitControl>
