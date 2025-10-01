<script lang="ts">
    import FriendlyDate from '../../@components/utils/FriendlyDate.svelte';
    import {consoleUrlWithNewsletter} from '../../lib/consoleUrl';
    import type {Issue} from '../../types';
    import IssueStatusTag from './IssueStatusTag.svelte';
    import SentStat from './SentStat.svelte';

    interface Props {
        issue: Issue;
    }

    let {issue}: Props = $props();

    function percentage(value: number, total: number): string {
        if (total === 0) return '0%';
        return `${Math.round((value / total) * 100)}%`;
    }
</script>

<a class="issue" href={consoleUrlWithNewsletter(`/issues/${issue.id}`)}>
    <div class="subject">
        {issue.subject || '(Subject not set)'}
    </div>
    <div class="status">
        <IssueStatusTag status={issue.status}/>
        {#if issue.status === 'sent' && issue.sent_at}
			<span class="sent-time">
				<FriendlyDate time={issue.sent_at}/>
			</span>
        {/if}
    </div>
    <div class="results">
        {#if issue.status === 'sent'}
            <SentStat value={issue.total_sends.toLocaleString()} name="Total Sent"/>
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

    .results {
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
