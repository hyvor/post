<script>
	import IconSend from '@hyvor/icons/IconSend';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { issueStore } from '../../../lib/stores/newsletterStore.ts';
	import CreateIssueButton from './CreateIssueButton.svelte';
	import SingleBox from '../../../@components/content/SingleBox.svelte';
	import IssueRow from './IssueRow.svelte';
	import { onMount } from 'svelte';
	import { getIssues } from '../../../lib/actions/issueActions.ts';
	import { LoadButton, Loader } from '@hyvor/design/components';
	import { getI18n } from '../../../lib/i18n.ts';

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	const ISSUES_PER_PAGE = 25;

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getIssues(ISSUES_PER_PAGE, more ? $issueStore.length : 0)
			.then((data) => {
				$issueStore = more ? [...$issueStore, ...data] : data;
				hasMore = data.length === ISSUES_PER_PAGE;
			})
			.catch((e) => {})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	const I18n = getI18n();

	onMount(() => {
		load();
	});
</script>

<SingleBox style="overflow-auto">
	<div class="issues">
		{#if loading}
			<Loader full />
		{:else if $issueStore.length}
			<div class="issues-title">
				{I18n.t('console.nav.issues')}
				<CreateIssueButton
					text={I18n.t('console.issues.newIssue')}
					icon={IconPlus}
					size="small"
				/>
			</div>

			<div class="issues-list">
				{#each $issueStore as issue}
					<IssueRow {issue} />
				{/each}
				<LoadButton
					text={'Load more'}
					loading={loadingMore}
					show={hasMore}
					on:click={() => load(true)}
				/>
			</div>
		{:else}
			<div class="create-first">
				<IconSend size={60} />
				<div class="message">
					{I18n.t('console.issues.noIssuesSent')}
				</div>
				<CreateIssueButton
					size="large"
					text={I18n.t('console.issues.createFirstIssue')}
					icon={IconSend}
				/>
			</div>
		{/if}
	</div>
</SingleBox>

<style>
	.create-first {
		text-align: center;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		height: 100%;
	}
	.create-first .message {
		margin-top: 20px;
		margin-bottom: 20px;
		color: var(--text-light);
	}
	.issues {
		height: 100%;
		overflow: auto;
		display: flex;
		flex-direction: column;
	}
	.issues-title {
		font-weight: 600;
		padding: 20px 30px;
		border-bottom: 1px solid var(--border);
		font-size: 22px;
		margin-bottom: 15px;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 10px;
	}

	.issues-list {
		padding: 0 30px;
		overflow: auto;
		flex: 1;
		padding-bottom: 20px;
	}
</style>
