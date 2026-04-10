<script lang="ts">
	import {
		Button,
		CodeBlock,
		Divider,
		IconMessage,
		Loader,
		SplitControl,
		Tag,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { getIssue } from '../../lib/actions/issueActions';
	import type { Issue } from '../../types';
	import FriendlyDate from '../../../console/@components/utils/FriendlyDate.svelte';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';

	let loading = $state(true);
	let error: string | null = $state(null);
	let issue: Issue | null = $state(null);

	const statusColors: Record<string, 'default' | 'blue' | 'orange' | 'green'> = {
		draft: 'default',
		scheduled: 'blue',
		sending: 'orange',
		sent: 'green'
	};

	onMount(() => {
		const id = Number($page.params.id);

		getIssue(id)
			.then((data) => {
				issue = data;
			})
			.catch((e) => {
				error = e.message;
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

{#if loading}
	<Loader full />
{:else if error}
	<IconMessage error message={error} />
{:else if issue}
	<div class="detail">
		<div class="header">
			<Button as="a" href="/sudo/issues" size="small" color="input">
				{#snippet start()}
					<IconArrowLeft size={14} />
				{/snippet}
				Back
			</Button>
			<h2>{issue.subject || 'No subject'}</h2>
		</div>

		<div class="content">
			<SplitControl label="ID">
				<TextInput value={String(issue.id)} disabled block />
			</SplitControl>

			<SplitControl label="UUID">
				<TextInput value={issue.uuid} disabled block />
			</SplitControl>

			<SplitControl label="Subject">
				<TextInput value={issue.subject ?? '—'} disabled block />
			</SplitControl>

			<SplitControl label="Status">
				<Tag size="small" color={statusColors[issue.status] ?? 'default'}>
					{issue.status}
				</Tag>
			</SplitControl>

			<SplitControl label="Newsletter">
				<Button
					as="a"
					href={`/sudo/newsletters/${issue.newsletter.id}`}
					size="small"
					color="input"
				>
					{issue.newsletter.subdomain}
				</Button>
			</SplitControl>

			<Divider margin={20} />

			<SplitControl label="Created at">
				<FriendlyDate time={issue.created_at} />
			</SplitControl>

			{#if issue.scheduled_at}
				<SplitControl label="Scheduled at">
					<FriendlyDate time={issue.scheduled_at} />
				</SplitControl>
			{/if}

			{#if issue.sending_at}
				<SplitControl label="Sending at">
					<FriendlyDate time={issue.sending_at} />
				</SplitControl>
			{/if}

			{#if issue.sent_at}
				<SplitControl label="Sent at">
					<FriendlyDate time={issue.sent_at} />
				</SplitControl>
			{/if}

			<Divider margin={20} />

			<SplitControl label="Total sendable">
				<span>{issue.total_sendable}</span>
			</SplitControl>

			<h3>Issue Object</h3>
			<CodeBlock code={JSON.stringify(issue, null, 2)} language="json" />
		</div>
	</div>
{/if}

<style>
	.detail {
		padding: 20px 30px;
		overflow: auto;
		flex: 1;
	}

	.header {
		display: flex;
		align-items: center;
		gap: 15px;
		margin-bottom: 20px;
	}

	.header h2 {
		margin: 0;
	}

	.content h3 {
		margin: 10px 0;
	}
</style>
