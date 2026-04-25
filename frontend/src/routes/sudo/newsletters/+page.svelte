<script lang="ts">
	import {
		ActionList,
		ActionListItem,
		Button,
		Dropdown,
		IconButton,
		IconMessage,
		LoadButton,
		Loader,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { newsletterStore } from '../lib/stores/sudoStore';
	import NewsletterRow from './NewsletterRow.svelte';
	import type { Newsletter } from '../types';
	import IconX from '@hyvor/icons/IconX';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import { ITEMS_PER_PAGE } from '../lib/generalActions';
	import {
		getNewsletters,
		getNewslettersBatchStats,
		type NewsletterRowStats,
		type Organization
	} from '../lib/actions/newsletterActions';
	import { goto } from '$app/navigation';

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	let error: string | null = $state(null);

	let orgsMap = $state<Record<number, Organization>>({});
	let statsMap = $state<Record<number, NewsletterRowStats>>({});

	let search: string | undefined = $state(undefined);
	let searchValue: string | undefined = $state(undefined);

	let organizationId: string | undefined = $state(undefined);
	let organizationIdValue: string | undefined = $state(undefined);

	const SORT_OPTIONS: Record<string, string> = {
		id_desc: 'ID DESC',
		id_asc: 'ID ASC',
		most_issues: 'Most issues',
		most_subscribers: 'Most subscribers'
	};
	type SortKey = keyof typeof SORT_OPTIONS;
	let sortBy: SortKey = $state('id_desc');
	let sortDropdownShow = $state(false);

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getNewsletters(
			search ?? null,
			organizationIdValue ? parseInt(organizationIdValue) : null,
			ITEMS_PER_PAGE,
			more ? $newsletterStore.length : 0,
			sortBy
		)
			.then((data) => {
				const newOrgs: Record<number, Organization> = {};
				for (const org of data.orgs) {
					newOrgs[org.id] = org;
				}

				if (more) {
					newsletterStore.update((newsletters) => [...newsletters, ...data.newsletters]);
					orgsMap = { ...orgsMap, ...newOrgs };
				} else {
					newsletterStore.set(data.newsletters);
					orgsMap = newOrgs;
					statsMap = {};
				}
				hasMore = data.newsletters.length === ITEMS_PER_PAGE;

				const ids = data.newsletters.map((n) => n.id);
				if (ids.length > 0) {
					getNewslettersBatchStats(ids)
						.then((res) => {
							statsMap = { ...statsMap, ...res.stats };
						})
						.catch(() => {
							// stats are best-effort; the list itself is already rendered
						});
				}
			})
			.catch((e) => {
				error = e.message;
			})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	function handleSelect(newsletter: Newsletter) {
		goto(`/sudo/newsletters/${newsletter.id}`);
	}

	function handleSearchKeyup(e: any) {
		if (e.key === 'Enter') {
			searchValue = search;
			load();
		}

		if (e.key === 'Escape') {
			handleSearchClear();
		}
	}

	function handleSearchClear() {
		search = undefined;
		searchValue = undefined;
		load();
	}

	function handleOrgIdKeyup(e: any) {
		if (e.key === 'Enter') {
			organizationIdValue = organizationId;
			load();
		}

		if (e.key === 'Escape') {
			handleOrgIdClear();
		}
	}

	function handleOrgIdClear() {
		organizationId = undefined;
		organizationIdValue = undefined;
		load();
	}

	onMount(() => {
		load();
	});
</script>

{#if loading}
	<Loader full />
{:else if error}
	<IconMessage error message={error} />
{:else}
	<div class="top">
		<TextInput bind:value={search} on:keyup={handleSearchKeyup} size="small" block={false}>
			{#snippet start()}
				Name or subdomain
			{/snippet}
			{#snippet end()}
				<div class="search-icons">
					<span class="enter">
						{#if search !== searchValue}
							&nbsp; ⏎
						{/if}
					</span>
					<span class="clear">
						{#if searchValue}
							<IconButton size={16} on:click={handleSearchClear}>
								<IconX size={12} />
							</IconButton>
						{/if}
					</span>
				</div>
			{/snippet}
		</TextInput>
		<TextInput
			bind:value={organizationId}
			on:keyup={handleOrgIdKeyup}
			size="small"
			block={false}
		>
			{#snippet start()}
				Org ID
			{/snippet}
			{#snippet end()}
				<div class="search-icons">
					<span class="enter">
						{#if organizationId !== organizationIdValue}
							&nbsp; ⏎
						{/if}
					</span>
					<span class="clear">
						{#if organizationIdValue}
							<IconButton size={16} on:click={handleOrgIdClear}>
								<IconX size={12} />
							</IconButton>
						{/if}
					</span>
				</div>
			{/snippet}
		</TextInput>
		<Dropdown bind:show={sortDropdownShow}>
			{#snippet trigger()}
				<Button color="input" size="small">
					{#snippet start()}
						Sort
					{/snippet}
					<div class="dropdown-label">
						{SORT_OPTIONS[sortBy]}
					</div>
					{#snippet end()}
						<IconCaretDown size={14} />
					{/snippet}
				</Button>
			{/snippet}
			{#snippet content()}
				<ActionList>
					{#each Object.entries(SORT_OPTIONS) as [key, value]}
						<ActionListItem
							on:select={() => {
								sortBy = key;
								sortDropdownShow = false;
								load();
							}}>{value}</ActionListItem
						>
					{/each}
				</ActionList>
			{/snippet}
		</Dropdown>
	</div>

	{#if $newsletterStore.length === 0}
		<IconMessage empty message="No newsletters found" />
	{:else}
		<div class="list">
			<div class="header-row">
				<div class="col-newsletter">Newsletter</div>
				<div class="col-org">Organization</div>
				<div class="col-stats">Stats</div>
			</div>
			{#each $newsletterStore as newsletter (newsletter.id)}
				<NewsletterRow
					{newsletter}
					{handleSelect}
					org={newsletter.organization_id
						? orgsMap[newsletter.organization_id]
						: undefined}
					stats={statsMap[newsletter.id]}
				/>
			{/each}
			<LoadButton
				text="Load More"
				loading={loadingMore}
				show={hasMore}
				on:click={() => load(true)}
			/>
		</div>
	{/if}
{/if}

<style>
	.top {
		padding: 20px 0 0 30px;
	}

	.list {
		flex: 1;
		overflow: auto;
		padding: 20px 30px;
	}

	.header-row {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0 15px 8px 15px;
		font-weight: 600;
		font-size: 13px;
		color: var(--text-light);
		text-transform: uppercase;
		letter-spacing: 0.04em;
	}

	.header-row .col-newsletter {
		width: 35%;
	}

	.header-row .col-org {
		width: 35%;
	}

	.header-row .col-stats {
		width: 30%;
	}

	.dropdown-label {
		font-weight: normal;
	}

	.search-icons {
		display: flex;
		gap: 5px;
		align-items: center;

		.enter,
		.clear {
			width: 20px;
		}

		.enter {
			margin-top: 3px;
		}
	}
</style>
