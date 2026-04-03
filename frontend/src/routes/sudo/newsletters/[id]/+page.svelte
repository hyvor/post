<script lang="ts">
	import {
		Button,
		CodeBlock,
		Divider,
		IconMessage,
		Loader,
		toast
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { getNewsletter } from '../../lib/actions/newsletterActions';
	import type { NewsletterStats, Newsletter } from '../../types';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';
	import IconEnvelope from '@hyvor/icons/IconEnvelope';

	let loading = $state(true);
	let error: string | null = $state(null);
	let newsletter: Newsletter | null = $state(null);
	let stats: NewsletterStats | null = $state(null);

	onMount(() => {
		const id = Number($page.params.id);

		getNewsletter(id)
			.then((data) => {
				newsletter = data.newsletter;
				stats = data.stats;
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
{:else if newsletter && stats}
	<div class="detail">
		<div class="header">
			<Button as="a" href="/sudo/newsletters" size="small" color="input">
				{#snippet start()}
					<IconArrowLeft size={14} />
				{/snippet}
				Back
			</Button>
			<h2>{newsletter.name}</h2>
			<Button as="a" href={`/sudo/issues?newsletter_id=${newsletter.id}`} size="small" color="input">
				{#snippet start()}
					<IconEnvelope size={14} />
				{/snippet}
				Issues
			</Button>
		</div>

		<div class="stats-grid">
			<div class="stat">
				<div class="stat-title">Subscribers</div>
				<div class="stat-value-wrap">
					<span class="stat-value">{stats.subscribers.total.toLocaleString()}</span>
					<span class="stat-change" class:positive={stats.subscribers.last_30_days >= 0} class:negative={stats.subscribers.last_30_days < 0}>
						{stats.subscribers.last_30_days >= 0 ? '+' : ''}{stats.subscribers.last_30_days.toLocaleString()}
						<span class="last-30d">30d</span>
					</span>
				</div>
			</div>
			<div class="stat">
				<div class="stat-title">Issues</div>
				<div class="stat-value-wrap">
					<span class="stat-value">{stats.issues.total.toLocaleString()}</span>
					<span class="stat-change" class:positive={stats.issues.last_30_days >= 0} class:negative={stats.issues.last_30_days < 0}>
						{stats.issues.last_30_days >= 0 ? '+' : ''}{stats.issues.last_30_days.toLocaleString()}
						<span class="last-30d">30d</span>
					</span>
				</div>
			</div>
			<div class="stat">
				<div class="stat-title">Bounce Rate</div>
				<div class="stat-value-wrap">
					<span class="stat-value">{stats.bounced_rate.total}<span class="percent">%</span></span>
					<span class="stat-change" class:positive={stats.bounced_rate.last_30_days - stats.bounced_rate.total <= 0} class:negative={stats.bounced_rate.last_30_days - stats.bounced_rate.total > 0}>
						{stats.bounced_rate.last_30_days - stats.bounced_rate.total >= 0 ? '+' : ''}{(stats.bounced_rate.last_30_days - stats.bounced_rate.total).toFixed(2)}<span class="percent">%</span>
						<span class="last-30d">30d</span>
					</span>
				</div>
			</div>
			<div class="stat">
				<div class="stat-title">Complaint Rate</div>
				<div class="stat-value-wrap">
					<span class="stat-value">{stats.complained_rate.total}<span class="percent">%</span></span>
					<span class="stat-change" class:positive={stats.complained_rate.last_30_days - stats.complained_rate.total <= 0} class:negative={stats.complained_rate.last_30_days - stats.complained_rate.total > 0}>
						{stats.complained_rate.last_30_days - stats.complained_rate.total >= 0 ? '+' : ''}{(stats.complained_rate.last_30_days - stats.complained_rate.total).toFixed(2)}<span class="percent">%</span>
						<span class="last-30d">30d</span>
					</span>
				</div>
			</div>
			<div class="stat">
				<div class="stat-title">Lists</div>
				<div class="stat-value-wrap">
					<span class="stat-value">{stats.lists_count}</span>
				</div>
			</div>
			<div class="stat">
				<div class="stat-title">Sending Profiles</div>
				<div class="stat-value-wrap">
					<span class="stat-value">{stats.sending_profiles_count}</span>
				</div>
			</div>
		</div>

		<Divider margin={20} />

		<div class="content">
			<h3>Newsletter Object</h3>
			<CodeBlock code={JSON.stringify(newsletter, null, 2)} language="json" />
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

	.stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
		gap: 15px;
	}

	.stat {
		padding: 15px;
		background: var(--bg-light);
		border-radius: var(--box-radius);
	}

	.stat-title {
		margin-bottom: 5px;
		color: var(--text-light);
	}

	.stat-value {
		font-size: 35px;
	}

	.stat-change {
		margin-left: 5px;
		font-size: 14px;
	}

	.stat-change.positive {
		color: var(--green);
	}

	.stat-change.negative {
		color: var(--red);
	}

	.last-30d {
		font-size: 12px;
		color: var(--text-light);
	}

	.percent {
		font-size: 0.6em;
		opacity: 0.5;
	}

	.content h3 {
		margin: 10px 0;
	}
</style>
