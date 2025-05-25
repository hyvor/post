<script lang="ts">
	import { FormControl, SplitControl, TextInput } from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';
	import { draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';
	import { newsletterStore } from '../../../../lib/stores/newsletterStore';

	const I18n = getI18n();
</script>

<SplitControl label={I18n.t('console.issues.draft.fromName')}>
	<FormControl>
		<TextInput
			block
			placeholder={$newsletterStore.name}
			bind:value={$draftIssueEditingStore.from_name}
			maxlength={255}
			on:input={debouncedUpdateDraftIssue}
			on:blur={debouncedUpdateDraftIssue}
		/>
	</FormControl>
</SplitControl>
