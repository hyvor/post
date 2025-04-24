<script>
	import { Button, SplitControl, TextInput } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import { getI18n } from '../../../lib/i18n';

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
	<SplitControl label={i18n.t('console.settings.form.texts')}>
		{#snippet nested()}
			<SplitControl
				label={i18n.t('console.settings.form.title')}
				caption={i18n.t('console.settings.form.titleCaption')}
			>
				<TextInput block />
			</SplitControl>

			<SplitControl
				label={i18n.t('console.settings.form.description')}
				caption={i18n.t('console.settings.form.descriptionCaption')}
			>
				<TextInput block />
			</SplitControl>

			<SplitControl
				label={i18n.t('console.settings.form.subscribeButtonText')}
				caption={i18n.t('console.settings.form.subscribeButtonTextCaption')}
			>
				<TextInput block />
			</SplitControl>

			<SplitControl
				label={i18n.t('console.settings.form.successMessage')}
				caption={i18n.t('console.settings.form.successMessageCaption')}
			>
				<TextInput block />
			</SplitControl>
		{/snippet}
	</SplitControl>
</SettingsBody>
