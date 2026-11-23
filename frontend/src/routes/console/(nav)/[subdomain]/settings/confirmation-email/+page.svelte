<script lang="ts">
	import { Button, Callout, SplitControl, TextInput } from '@hyvor/design/components';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconArrowCounterclockwise from '@hyvor/icons/IconArrowCounterclockwise';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import NewsletterSaveDiscard from '../../@components/save/NewsletterSaveDiscard.svelte';
	import ConfirmationEmailPreview from './ConfirmationEmailPreview.svelte';
	import { newsletterEditingStore } from '../../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../../lib/stores/consoleStore';
	import { consoleUrlWithNewsletter } from '../../../../lib/consoleUrl';

	const newsletterDefaults = getAppConfig().newsletter_defaults;

	let isCustomized = $derived(
		!!$newsletterEditingStore.confirmation_email_subject ||
			$newsletterEditingStore.confirmation_email_content !== null
	);

	function resetToDefault() {
		$newsletterEditingStore.confirmation_email_subject = null;
		$newsletterEditingStore.confirmation_email_content = null;
	}
</script>

<SettingsTop>
	<div class="button-wrap">
		<Button as="a" href={consoleUrlWithNewsletter('/settings/confirmation-email/edit')}>
			Edit Content
			{#snippet end()}
				<IconPencil size={12} />
			{/snippet}
		</Button>
		{#if isCustomized}
			<Button color="input" on:click={resetToDefault}>
				Reset to Default
				{#snippet end()}
					<IconArrowCounterclockwise size={12} />
				{/snippet}
			</Button>
		{/if}
	</div>
</SettingsTop>

<SettingsBody>
	<Callout type="info">
		This email is sent to new subscribers to confirm their subscription (double opt-in). Use <code
			>{'{{newsletter_name}}'}</code
		>
		for the newsletter name and
		<code>{'{{confirm_url}}'}</code> as the link of the confirmation button.
	</Callout>

	<SplitControl label="Subject" caption="Leave empty to use the default subject.">
		<TextInput
			block
			bind:value={$newsletterEditingStore.confirmation_email_subject}
			placeholder={newsletterDefaults.CONFIRMATION_EMAIL_SUBJECT}
			maxlength={255}
		/>
	</SplitControl>

	<SplitControl label="Preview" column>
		<div class="preview-wrap">
			<ConfirmationEmailPreview
				subject={$newsletterEditingStore.confirmation_email_subject || null}
				content={$newsletterEditingStore.confirmation_email_content}
			/>
		</div>
	</SplitControl>
</SettingsBody>

<NewsletterSaveDiscard keys={['confirmation_email_subject', 'confirmation_email_content']} />

<style>
	.button-wrap {
		display: flex;
		gap: 6px;
	}

	.preview-wrap {
		border: 1px solid var(--border);
		border-radius: 20px;
		overflow: hidden;
	}
</style>
