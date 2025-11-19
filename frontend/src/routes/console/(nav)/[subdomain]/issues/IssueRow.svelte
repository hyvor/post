<script lang="ts">
	import { confirm, IconButton, toast } from '@hyvor/design/components';
	import FriendlyDate from '../../../@components/utils/FriendlyDate.svelte';
	import { consoleUrlWithNewsletter } from '../../../lib/consoleUrl';
	import type { Issue } from '../../../types';
	import IssueStatusTag from './IssueStatusTag.svelte';
	import SentStat from './SentStat.svelte';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deleteIssue } from '../../../lib/actions/issueActions';
	import { issueStore } from '../../../lib/stores/newsletterStore';

	interface Props {
		issue: Issue;
	}

	let { issue }: Props = $props();

	async function handleDelete(e: Event) {
		e.preventDefault();
		e.stopPropagation();

		const confirmed = await confirm({
			title: 'Confirm to delete',
			content:
				'Are you sure that you want to delete this draft issue? This action cannot be undone.',
			confirmText: 'Delete',
			danger: true
		});

		if (!confirmed) {
			return;
		}

		deleteIssue(issue.id)
			.then(() => {
				toast.success('Issue deleted successfully');
				issueStore.update((issues) => {
					return issues.filter((i) => i.id !== issue.id);
				});
			})
			.catch(() => {
				toast.error('Failed to delete the issue');
			});
	}
</script>

<a class="issue" href={consoleUrlWithNewsletter(`/issues/${issue.id}`)}>
	<div class="subject">
		{issue.subject || '(Subject not set)'}
	</div>
	<div class="status">
		<IssueStatusTag status={issue.status} />
		{#if issue.status === 'sent' && issue.sent_at}
			<span class="sent-time">
				<FriendlyDate time={issue.sent_at} />
			</span>
		{/if}
	</div>
	<div class="end">
		{#if issue.status === 'sent'}
			<SentStat value={issue.total_sends.toLocaleString()} name="Total Sent" />
		{:else if issue.status === 'draft'}
			<IconButton color="red" variant="fill-light" size="small" on:click={handleDelete}>
				<IconTrash size={12} />
			</IconButton>
		{/if}
	</div>
</a>

<style>
	.subject {
		font-weight: 600;
		flex: 2;
	}

	.status {
		flex: 1;
	}

	.issue {
		padding: 15px 25px;
		border-radius: var(--box-radius);
		cursor: pointer;
		display: flex;
		align-items: center;
		text-align: left;
		width: 100%;
	}

	.issue:hover {
		background-color: var(--hover);
	}

	.status {
		min-width: 60px;
	}

	.end {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 10px;
	}

	.sent-time {
		color: var(--text-light);
		font-size: 12px;
		margin-left: 5px;
	}

	@media (max-width: 992px) {
		.issue {
			padding: 15px;
			flex-direction: column;
			gap: 10px;
		}
	}
</style>
