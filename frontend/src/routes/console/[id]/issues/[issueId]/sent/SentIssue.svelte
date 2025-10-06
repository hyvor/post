<script lang="ts">
	import { onMount } from 'svelte';
	import type { Issue } from '../../../../types';
	import { getIssueReport, type IssueCounts } from '../../../../lib/actions/issueActions';
	import { Callout, IconButton, Loader, TextInput, toast, Tooltip } from '@hyvor/design/components';
    import IconExclamationTriangle from '@hyvor/icons/IconExclamationTriangle';
    import IconCopy from '@hyvor/icons/IconCopy';
    import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import Sends from './Sends.svelte';
	import SentStat from './SentStat.svelte';
	import FriendlyDate from '../../../../@components/utils/FriendlyDate.svelte';
	import { copyAndToast } from '$lib/helpers/copy';
	import { getNewsletterArchiveUrlFromSubdomain } from '../../../../lib/archive';
	import { newsletterStore } from '../../../../lib/stores/newsletterStore';

	export let issue: Issue;

	const webUrl = getNewsletterArchiveUrlFromSubdomain($newsletterStore.subdomain) + '/issue/' + issue.uuid;

	let loading = true;

	let counts: IssueCounts;

	onMount(() => {
		getIssueReport(issue.id)
			.then((res) => {
				counts = res.counts;
			})
			.catch((err) => {
				toast.error('Failed to load issue report: ' + err.message);
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<div class="wrap">
	{#if issue.status === 'failed'}
		<Callout type="danger" style="margin-bottom:20px;">
			<IconExclamationTriangle />
			There were some issues while sending this issue. Please contact support for more information.
		</Callout>
	{/if}
	<div class="top">
		<div class="left">
			<div class="title">
				{issue.subject}
			</div>
			{#if issue.sent_at}
				<div class="date">
					Sent <FriendlyDate time={issue.sent_at} />
				</div>
			{/if}
		</div>
		<div>
			<Tooltip text="This is a shareable link" position="bottom">
				<a class="web-url" href={webUrl} target="_blank" rel="noopener noreferrer">
					<span>Web version</span>
					<IconBoxArrowUpRight size={12} />
				</a>
			</Tooltip>
			<IconButton
				size="small"
				color="input"
				style="margin-left:4px;"
				on:click={() => copyAndToast(webUrl, 'Web URL copied')}
			>
				<IconCopy size={12} />
			</IconButton>
		</div>
	</div>
	<div class="content">
		{#if loading}
			<Loader full />
		{:else}
			<div class="stats">
				<SentStat title="Total Sent" value={counts.total} />
				<SentStat title="Unsubscribed" value={counts.unsubscribed} total={counts.total} />
				<SentStat title="Bounced" value={counts.bounced} total={counts.total} />
				<SentStat title="Complaints" value={counts.complained} total={counts.total} />
			</div>
			<Sends {issue} />
		{/if}
	</div>
</div>

<style>
	.wrap {
		padding: 0 15px;
		display: flex;
		flex-direction: column;
		height: 100%;
	}
	.top {
		display: flex;
		align-items: center;
	}
	.top .left {
		flex: 1;
	}
	.title {
		font-size: 24px;
		font-weight: 600;
	}
	.content {
		flex: 1;
	}
	.stats {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
		margin: 20px -40px;
		padding: 15px 40px;
	}
	.web-url {
		display: inline-block;
		align-items: center;
	}
	.web-url span {
		text-decoration: underline;
	}
</style>
