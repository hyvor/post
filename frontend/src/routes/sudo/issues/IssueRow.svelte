<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { Issue } from '../types';
	import RelativeTime from '../../console/@components/utils/RelativeTime.svelte';

	interface Props {
		issue: Issue;
		handleSelect: (issue: Issue) => void;
	}

	let { issue, handleSelect }: Props = $props();

	const statusColors: Record<string, 'default' | 'blue' | 'orange' | 'green'> = {
		draft: 'default',
		scheduled: 'blue',
		sending: 'orange',
		sent: 'green'
	};
</script>

<button class="row" onclick={() => handleSelect(issue)}>
	<div class="subject">
		{issue.subject || 'No subject'}
	</div>

	<div class="status">
		<Tag size="small" color={statusColors[issue.status] ?? 'default'}>
			{issue.status}
		</Tag>
	</div>

	<div class="newsletter">
		{issue.newsletter_subdomain}
	</div>

	<div class="created-at">
		<RelativeTime unix={issue.created_at} />
	</div>

	<div class="sent-at">
		{#if issue.sent_at}
			<RelativeTime unix={issue.sent_at} />
		{:else}
			—
		{/if}
	</div>
</button>

<style>
	.row {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 15px;
		background-color: var(--bg-light);
		border-radius: var(--box-radius);
		text-align: left;
		width: 100%;
	}

	.row:hover {
		background: var(--hover);
	}

	.subject {
		width: 30%;
	}

	.status {
		width: 15%;
	}

	.newsletter {
		width: 20%;
	}

	.created-at {
		width: 17%;
	}

	.sent-at {
		width: 18%;
	}
</style>
