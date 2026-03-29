<script lang="ts">
	import {
		Button,
		CodeBlock,
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
	import type { NewsletterStats, Newsletter } from '../../types';
	import FriendlyDate from '../../../console/@components/utils/FriendlyDate.svelte';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';

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

			<CodeBlock code={JSON.stringify(stats, null, 2)} language="json" />
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
