<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { Editor } from '@hyvor/richtext';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';
	import SettingsTop from '../../@components/SettingsTop.svelte';
	import NewsletterSaveDiscard from '../../../@components/save/NewsletterSaveDiscard.svelte';
	import ConfirmationEmailPreview from '../ConfirmationEmailPreview.svelte';
	import {
		newsletterEditingStore,
		newsletterStore
	} from '../../../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../../../lib/stores/consoleStore';
	import { consoleUrlWithNewsletter } from '../../../../../lib/consoleUrl';

	const newsletterDefaults = getAppConfig().newsletter_defaults;
	const defaultContent = newsletterDefaults.CONFIRMATION_EMAIL_CONTENT;

	// read once: the editor manages its own state after initialization
	const initialValue = $newsletterEditingStore.confirmation_email_content ?? defaultContent;

	function onValueChange(doc: string) {
		if ($newsletterEditingStore.confirmation_email_content === null && doc === defaultContent) {
			return;
		}
		$newsletterEditingStore.confirmation_email_content = doc;
	}
</script>

<SettingsTop>
	<div class="top">
		<Button
			color="input"
			size="small"
			as="a"
			href={consoleUrlWithNewsletter('/settings/confirmation-email')}
		>
			{#snippet start()}
				<IconArrowLeft size={12} />
			{/snippet}
			Back
		</Button>
		<div class="hint">
			Use <code>{'{{confirm_url}}'}</code> as the link of a button or link, and
			<code>{'{{newsletter_name}}'}</code> for the newsletter name.
		</div>
	</div>
</SettingsTop>

<div class="split">
	<div class="editor" dir={$newsletterStore.is_rtl ? 'rtl' : 'ltr'}>
		<Editor
			value={initialValue}
			onvaluechange={onValueChange}
			config={{
				colorButtonBackground:
					$newsletterStore.template_color_accent ?? newsletterDefaults.TEMPLATE_COLOR_ACCENT,
				colorButtonText:
					$newsletterStore.template_color_accent_text ??
					newsletterDefaults.TEMPLATE_COLOR_ACCENT_TEXT,
				buttonEnabled: true,
				codeBlockEnabled: false,
				customHtmlEnabled: false,
				imageEnabled: false,
				tableEnabled: false,
				bookmarkEnabled: false,
				tocEnabled: false,
				audioEnabled: false,
				embedEnabled: false
			}}
		/>
	</div>
	<div class="preview">
		<ConfirmationEmailPreview
			subject={$newsletterEditingStore.confirmation_email_subject || null}
			content={$newsletterEditingStore.confirmation_email_content}
		/>
	</div>
</div>

<NewsletterSaveDiscard keys={['confirmation_email_subject', 'confirmation_email_content']} />

<style>
	.top {
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.hint {
		font-size: 13px;
		color: var(--text-light);
	}

	.split {
		display: flex;
		flex: 1;
		min-height: 0;
		overflow: hidden;
	}

	.editor {
		flex: 1;
		width: 50%;
		overflow: auto;
		padding: 10px 30px 60px;
	}

	.preview {
		flex: 1;
		width: 50%;
		overflow: auto;
		border-left: 1px solid var(--border);
	}
</style>
