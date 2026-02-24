<script lang="ts">
	import {
		Button,
		Divider,
		IconMessage,
		Loader,
		SplitControl,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { getNewsletter } from '../../lib/actions/newsletterActions';
	import type { NewsletterStats, SudoNewsletter } from '../../types';
	import FriendlyDate from '../../../console/@components/utils/FriendlyDate.svelte';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';

	let loading = $state(true);
	let error: string | null = $state(null);
	let newsletter: SudoNewsletter | null = $state(null);
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
		</div>

		<div class="content">
			<SplitControl label="ID">
				<TextInput value={String(newsletter.id)} disabled block />
			</SplitControl>

			<SplitControl label="Name">
				<TextInput value={newsletter.name} disabled block />
			</SplitControl>

			<SplitControl label="Subdomain">
				<TextInput value={newsletter.subdomain} disabled block />
			</SplitControl>

			<SplitControl label="User ID">
				<TextInput value={String(newsletter.user_id)} disabled block />
			</SplitControl>

			<SplitControl label="Organization ID">
				<TextInput
					value={newsletter.organization_id != null
						? String(newsletter.organization_id)
						: '—'}
					disabled
					block
				/>
			</SplitControl>

			<SplitControl label="Language">
				<TextInput value={newsletter.language_code ?? '—'} disabled block />
			</SplitControl>

			<SplitControl label="RTL">
				<TextInput value={newsletter.is_rtl ? 'Yes' : 'No'} disabled block />
			</SplitControl>

			<SplitControl label="Created at">
				<FriendlyDate time={newsletter.created_at} />
			</SplitControl>

			<Divider margin={20} />

			<h3>Stats</h3>

			<SplitControl label="Subscribers">
				<span
					>{stats.subscribers.total} total / {stats.subscribers.last_30_days} last 30d</span
				>
			</SplitControl>

			<SplitControl label="Issues (sent)">
				<span>{stats.issues.total} total / {stats.issues.last_30_days} last 30d</span>
			</SplitControl>

			<SplitControl label="Bounce rate">
				<span
					>{stats.bounced_rate.total}% total / {stats.bounced_rate.last_30_days}% last 30d</span
				>
			</SplitControl>

			<SplitControl label="Complaint rate">
				<span
					>{stats.complained_rate.total}% total / {stats.complained_rate.last_30_days}%
					last 30d</span
				>
			</SplitControl>

			<SplitControl label="Lists">
				<span>{stats.lists_count}</span>
			</SplitControl>

			<SplitControl label="Sending profiles">
				<span>{stats.sending_profiles_count}</span>
			</SplitControl>
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
