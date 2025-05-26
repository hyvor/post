<script lang="ts">
	import { FormControl, SplitControl, TextInput, Validation } from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';
	import { draftErrorsStore, draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';
	import { EMAIL_REGEX } from '../../../../lib/regex';

	const I18n = getI18n();

	let replyToEmail = $state($draftIssueEditingStore.reply_to_email);

	function handleUpdate() {
		$draftErrorsStore.reply_to_email = '';

		if (replyToEmail && !EMAIL_REGEX.test(replyToEmail)) {
			$draftErrorsStore.reply_to_email = 'Invalid email address';
			return;
		}

		$draftIssueEditingStore.reply_to_email = replyToEmail;
		debouncedUpdateDraftIssue();
	}
</script>

<SplitControl label={I18n.t('console.issues.draft.replyTo')}>
	<FormControl>
		<TextInput
			block
			bind:value={replyToEmail}
			maxlength={255}
			on:blur={handleUpdate}
			state={$draftErrorsStore.reply_to_email ? 'error' : undefined}
		/>

		{#if $draftErrorsStore.reply_to_email}
			<Validation state="error">{$draftErrorsStore.reply_to_email}</Validation>
		{/if}
	</FormControl>
</SplitControl>
