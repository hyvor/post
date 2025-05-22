<script lang="ts">
	import {
		SplitControl,
		TabNav,
		TabNavItem,
		Textarea,
		TextInput
	} from '@hyvor/design/components';
	import { getI18n } from '../../../lib/i18n';
	import { projectEditingStore } from '../../../lib/stores/newsletterStore';
	import ProjectSaveDiscard from '../../@components/save/NewsletterSaveDiscard.svelte';
	import FormColors from './FormColors.svelte';

	const i18n = getI18n();

	let palette: 'light' | 'dark' = $state('light');
</script>

<SplitControl
	label={i18n.t('console.settings.form.texts')}
	caption={i18n.t('console.settings.form.textsHtmlAllowed')}
>
	{#snippet nested()}
		<SplitControl
			label={i18n.t('console.settings.form.title')}
			caption={i18n.t('console.settings.form.titleCaption')}
		>
			<TextInput block bind:value={$projectEditingStore.form_title} />
		</SplitControl>

		<SplitControl
			label={i18n.t('console.settings.form.description')}
			caption={i18n.t('console.settings.form.descriptionCaption')}
		>
			<TextInput block bind:value={$projectEditingStore.form_description} />
		</SplitControl>

		<SplitControl
			label={i18n.t('console.settings.form.footerText')}
			caption={i18n.t('console.settings.form.footerTextCaption')}
		>
			<TextInput block bind:value={$projectEditingStore.form_footer_text} />
		</SplitControl>

		<SplitControl
			label={i18n.t('console.settings.form.subscribeButtonText')}
			caption={i18n.t('console.settings.form.subscribeButtonTextCaption')}
		>
			<TextInput
				block
				bind:value={$projectEditingStore.form_button_text}
				placeholder="Subscribe"
			/>
		</SplitControl>
		<SplitControl
			label={i18n.t('console.settings.form.successMessage')}
			caption={i18n.t('console.settings.form.successMessageCaption')}
		>
			<TextInput
				block
				bind:value={$projectEditingStore.form_success_message}
				placeholder="Thank you for subscribing!"
			/>
		</SplitControl>
	{/snippet}
</SplitControl>

<SplitControl label={i18n.t('console.settings.form.colorsUi')}>
	{#snippet nested()}
		<TabNav active={palette}>
			<TabNavItem name="light">{i18n.t('console.settings.form.paletteLight')}</TabNavItem>
			<TabNavItem name="dark">{i18n.t('console.settings.form.paletteDark')}</TabNavItem>
		</TabNav>

		<FormColors {palette} />
	{/snippet}
</SplitControl>

<SplitControl label={i18n.t('console.settings.form.customCss')} column>
	<Textarea bind:value={$projectEditingStore.form_custom_css} block />
</SplitControl>

<ProjectSaveDiscard
	keys={[
		'form_title',
		'form_description',
		'form_footer_text',
		'form_button_text',
		'form_success_message',
		'form_custom_css',

		'form_color_light_text',
		'form_color_light_text_light',
		'form_color_light_accent',
		'form_color_light_accent_text',
		'form_color_light_input',
		'form_color_light_input_text',
		'form_light_input_box_shadow',
		'form_light_input_border',
		'form_light_border_radius',

		'form_color_dark_text',
		'form_color_dark_text_light',
		'form_color_dark_accent',
		'form_color_dark_accent_text',
		'form_color_dark_input',
		'form_color_dark_input_text',
		'form_dark_input_box_shadow',
		'form_dark_input_border',
		'form_dark_border_radius'
	]}
/>
