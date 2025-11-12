<script lang="ts">
	import { FormControl, SplitControl, TextInput, Validation } from '@hyvor/design/components';
	import { getI18n } from '../../../../../lib/i18n';
	import { draftErrorsStore, draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';
	import { onMount } from 'svelte';

	const I18n = getI18n();

	let inputEl = $state({} as HTMLInputElement);

	onMount(() => {
		if (!$draftIssueEditingStore.subject) {
			inputEl.focus();
		}
	});
</script>

<SplitControl label={I18n.t('console.issues.draft.subject')}>
	<FormControl>
		<TextInput
			block
			bind:value={$draftIssueEditingStore.subject}
			maxlength={255}
			on:input={debouncedUpdateDraftIssue}
			state={$draftErrorsStore.subject ? 'error' : undefined}
			bind:input={inputEl}
			placeholder={I18n.t('console.issues.draft.subject') + '...'}
		/>
		{#if $draftErrorsStore.subject}
			<Validation state="error">{$draftErrorsStore.subject}</Validation>
		{/if}
	</FormControl>
</SplitControl>
