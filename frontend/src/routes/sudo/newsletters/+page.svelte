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
	import { getNewsletters } from '../lib/actions/newsletterActions';
	import { goto } from '$app/navigation';

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	let error: string | null = $state(null);

	let search: string | undefined = $state(undefined);
	let searchValue: string | undefined = $state(undefined);

	let organizationId: string | undefined = $state(undefined);
	let organizationIdValue: string | undefined = $state(undefined);

	const SORT_OPTIONS: Record<string, string> = {
		name: 'Name',
		created_at: 'Created'
	};
	type SortKey = keyof typeof SORT_OPTIONS;
	let sortBy: SortKey = $state('created_at');
	let sortDropdownShow = $state(false);

	let sortedNewsletters = $derived.by(() => {
		const items = [...$newsletterStore];
		items.sort((a, b) => {
			if (sortBy === 'name') {
				return a.name.localeCompare(b.name);
			}
			return b.created_at - a.created_at;
		});
		return items;
	});

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getNewsletters(
			search ?? null,
			organizationIdValue ? parseInt(organizationIdValue) : null,
			ITEMS_PER_PAGE,
			more ? $newsletterStore.length : 0
		)
			.then((data) => {
				if (more) {
					newsletterStore.update((newsletters) => [...newsletters, ...data]);
				} else {
					newsletterStore.set(data);
				}
				hasMore = data.length === ITEMS_PER_PAGE;
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
				Name
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
		<TextInput bind:value={organizationId} on:keyup={handleOrgIdKeyup} size="small" block={false}>
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
			{#each sortedNewsletters as newsletter (newsletter.id)}
				<NewsletterRow {newsletter} {handleSelect} />
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
