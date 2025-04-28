<script>
	import { Button, SplitControl, TextInput } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import { getI18n } from '../../../lib/i18n';
	import ProjectSaveDiscard from '../../@components/save/ProjectSaveDiscard.svelte';
	import { projectEditingStore } from '../../../lib/stores/projectStore';

	const i18n = getI18n();
</script>

<SettingsTop>
	<Button color="input" as="a" href="/docs/form" target="_blank">
		Signup Form Docs
		{#snippet end()}
			<IconBoxArrowUpRight size={12} />
		{/snippet}
	</Button>
</SettingsTop>

<SettingsBody>
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
</SettingsBody>

<ProjectSaveDiscard
	keys={[
		'form_title',
		'form_description',
		'form_footer_text',
		'form_button_text',
		'form_success_message'
	]}
/>
