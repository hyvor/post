<script lang="ts">
	import { consoleUrlWithNewsletter } from '../../lib/consoleUrl';
	import type { Issue } from '../../types';
	import IssueStatusTag from './IssueStatusTag.svelte';

	export let issue: Issue;
</script>

<a class="issue" href={consoleUrlWithNewsletter(`/issues/${issue.id}`)}>
	<div class="subject">
		{issue.subject || '(Subject not set)'}
	</div>
	<div class="status">
		<IssueStatusTag status={issue.status} />
	</div>
	<div class="results"></div>
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
		text-align: left;
		width: 100%;
	}
	.issue:hover {
		background-color: var(--hover);
	}
	.status {
		min-width: 60px;
	}
	.results {
		flex: 1;
	}

	@media (max-width: 992px) {
		.issue {
			padding: 15px;
			flex-direction: column;
			gap: 10px;
		}
	}
</style>
