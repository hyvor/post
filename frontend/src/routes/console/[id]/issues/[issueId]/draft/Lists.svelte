<script lang="ts">
	import { Checkbox, SplitControl, Validation } from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';
	import { draftErrorsStore, draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';
	import { listStore } from '../../../../lib/stores/newsletterStore';

	const I18n = getI18n();

	let inputEl = $state({} as HTMLInputElement);

	function onChange(id: number) {
		const currentLists = $draftIssueEditingStore.lists;
		if (currentLists.includes(id)) {
			$draftIssueEditingStore.lists = currentLists.filter((s) => s !== id);
		} else {
			$draftIssueEditingStore.lists = [...currentLists, id];
		}
		debouncedUpdateDraftIssue();
	}
</script>

<SplitControl label={I18n.t('console.issues.draft.lists')}>
	{#each $listStore as list}
		<div class="list">
			<Checkbox
				checked={$draftIssueEditingStore.lists.includes(list.id)}
				on:change={() => onChange(list.id)}
			>
				{list.name} ({list.subscribers_count} subscribers)
			</Checkbox>
		</div>
	{/each}
	{#if $draftErrorsStore.lists}
		<Validation state="error">{$draftErrorsStore.lists}</Validation>
	{/if}
</SplitControl>

<style>
	.list {
		margin-bottom: 10px;
	}
</style>
