<script lang="ts">
	import {
		Button,
		CodeBlock,
		Divider,
		IconMessage,
		Loader,
		SplitControl,
		TabNav,
		TabNavItem,
		Tag,
		TextInput
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { getIssue } from '../../lib/actions/issueActions';
	import type { Issue } from '../../types';
	import FriendlyDate from '../../../console/@components/utils/FriendlyDate.svelte';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';
	import IconArrowRight from '@hyvor/icons/IconArrowRight';
	import SudoSends from './sends/SudoSends.svelte';

	let loading = $state(true);
	let error: string | null = $state(null);
	let issue: Issue | null = $state(null);
	let tab = $state('details');

	const issueDump = $derived.by(() => {
		if (!issue) return '';
		const { newsletter: _newsletter, ...rest } = issue;
		return JSON.stringify(rest, null, 2);
	});

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

		<TabNav active={tab}>
			<TabNavItem
				name="details"
				active={tab === 'details'}
				onclick={() => (tab = 'details')}
			>
				Details
			</TabNavItem>
			<TabNavItem name="html" active={tab === 'html'} onclick={() => (tab = 'html')}>
				HTML
			</TabNavItem>
			<TabNavItem name="sends" active={tab === 'sends'} onclick={() => (tab = 'sends')}>
				Sends
			</TabNavItem>
		</TabNav>

		<div class="content">
			{#if tab === 'details'}
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
						{#snippet end()}
							<IconArrowRight size={12} />
						{/snippet}
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
				<CodeBlock code={issueDump} language="json" />
			{:else if tab === 'html'}
				<iframe
					class="html-preview"
					src={`/api/sudo/issues/${issue.id}/preview`}
					title="Email HTML preview"
				></iframe>
			{:else if tab === 'sends'}
				<SudoSends issueId={issue.id} />
			{/if}
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

	.content {
		margin-top: 20px;
	}

	.content h3 {
		margin: 10px 0;
	}

	.html-preview {
		width: 100%;
		height: calc(100vh - 220px);
		min-height: 500px;
		border: 1px solid var(--border);
		border-radius: 8px;
		background: white;
	}
</style>
