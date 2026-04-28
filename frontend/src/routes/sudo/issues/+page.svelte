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
		TextInput
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { issueStore } from '../lib/stores/sudoStore';
	import IssueRow from './IssueRow.svelte';
	import type { IssueStatus, Issue, Newsletter } from '../types';
	import IconX from '@hyvor/icons/IconX';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import { ITEMS_PER_PAGE } from '../lib/generalActions';
	import { getIssues, ISSUE_STATUS_FILTERS } from '../lib/actions/issueActions';
	import { getNewsletter, getNewsletters } from '../lib/actions/newsletterActions';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	let error: string | null = $state(null);

	let statusFilter: IssueStatus | undefined = $state(undefined);
	let statusDropdownShow = $state(false);

	const NEWSLETTER_PAGE_SIZE = 20;
	let newsletters: Newsletter[] = $state([]);
	let newsletterFilter: number | undefined = $state(undefined);
	let selectedNewsletter: Newsletter | undefined = $state(undefined);
	let newsletterDropdownShow = $state(false);
	let newsletterSearch = $state('');
	let newsletterOffset = $state(0);
	let newsletterHasMore = $state(true);
	let newslettersLoading = $state(false);
	let newsletterSearchDebounce: ReturnType<typeof setTimeout> | null = null;

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getIssues(
			newsletterFilter ?? null,
			statusFilter ?? null,
			ITEMS_PER_PAGE,
			more ? $issueStore.length : 0
		)
			.then((data) => {
				if (more) {
					issueStore.update((issues) => [...issues, ...data]);
				} else {
					issueStore.set(data);
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

	function loadNewsletters(reset = false) {
		newslettersLoading = true;
		const offset = reset ? 0 : newsletterOffset;

		getNewsletters(newsletterSearch || null, null, NEWSLETTER_PAGE_SIZE, offset)
			.then((data) => {
				newsletters = reset ? data.newsletters : [...newsletters, ...data.newsletters];
				newsletterOffset = offset + data.newsletters.length;
				newsletterHasMore = data.newsletters.length === NEWSLETTER_PAGE_SIZE;
			})
			.finally(() => {
				newslettersLoading = false;
			});
	}

	function handleSelect(issue: Issue) {
		goto(`/sudo/issues/${issue.id}`);
	}

	function onStatusClick(val: any) {
		statusFilter = val;
		load();
		statusDropdownShow = false;
	}

	function onNewsletterClick(nl: Newsletter) {
		newsletterFilter = nl.id;
		selectedNewsletter = nl;
		load();
		newsletterDropdownShow = false;
	}

	function clearNewsletter(e: Event) {
		e.stopPropagation();
		newsletterFilter = undefined;
		selectedNewsletter = undefined;
		load();
	}

	function onNewsletterSearchInput() {
		if (newsletterSearchDebounce) clearTimeout(newsletterSearchDebounce);
		newsletterSearchDebounce = setTimeout(() => {
			loadNewsletters(true);
		}, 250);
	}

	$effect(() => {
		if (newsletterDropdownShow && newsletters.length === 0 && !newslettersLoading) {
			loadNewsletters(true);
		}
	});

	onMount(() => {
		const urlNewsletterIdParam = $page.url.searchParams.get('newsletter_id');
		if (urlNewsletterIdParam) {
			const id = Number(urlNewsletterIdParam);
			newsletterFilter = id;
			getNewsletter(id)
				.then((data) => {
					selectedNewsletter = data.newsletter;
				})
				.catch(() => {});
		}

		load();
	});
</script>

{#if loading}
	<Loader full />
{:else if error}
	<IconMessage error message={error} />
{:else}
	<div class="top">
		<Dropdown bind:show={newsletterDropdownShow}>
			{#snippet trigger()}
				<Button color="input" size="small">
					{#snippet start()}
						Newsletter
					{/snippet}
					<div class="dropdown-label">
						{selectedNewsletter?.name ?? 'All'}
					</div>
					{#if newsletterFilter}
						<IconButton size={14} style="margin-left:4px;" color="gray" on:click={clearNewsletter}>
							<IconX size={10} />
						</IconButton>
					{/if}
					{#snippet end()}
						<IconCaretDown size={14} />
					{/snippet}
				</Button>
			{/snippet}
			{#snippet content()}
				<div class="newsletter-dropdown">
					<div class="newsletter-search">
						<TextInput
							bind:value={newsletterSearch}
							on:input={onNewsletterSearchInput}
							placeholder="Search by name or subdomain"
							size="small"
							block
						/>
					</div>
					<div class="newsletter-list">
						{#if newslettersLoading && newsletters.length === 0}
							<div class="newsletter-loader"><Loader /></div>
						{:else if newsletters.length === 0}
							<IconMessage empty message="No newsletters" />
						{:else}
							<ActionList>
								{#each newsletters as nl (nl.id)}
									<ActionListItem on:select={() => onNewsletterClick(nl)}>
										<span class="newsletter-name">{nl.name}</span>
										<span class="newsletter-sub">{nl.subdomain}</span>
									</ActionListItem>
								{/each}
							</ActionList>
							<LoadButton
								text="Load More"
								loading={newslettersLoading}
								show={newsletterHasMore}
								on:click={() => loadNewsletters(false)}
							/>
						{/if}
					</div>
				</div>
			{/snippet}
		</Dropdown>
		<Dropdown bind:show={statusDropdownShow}>
			{#snippet trigger()}
				<Button color="input" size="small">
					{#snippet start()}
						Status
					{/snippet}
					<div class="dropdown-label">
						{statusFilter ? ISSUE_STATUS_FILTERS[statusFilter] : 'None'}
					</div>
					{#if statusFilter}
						<IconButton
							size={14}
							style="margin-left:4px;"
							color="gray"
							on:click={(e) => {
								e.stopPropagation();
								statusFilter = undefined;
								load();
							}}
						>
							<IconX size={10} />
						</IconButton>
					{/if}
					{#snippet end()}
						<IconCaretDown size={14} />
					{/snippet}
				</Button>
			{/snippet}
			{#snippet content()}
				<ActionList>
					{#each Object.entries(ISSUE_STATUS_FILTERS) as [key, value]}
						<ActionListItem on:select={() => onStatusClick(key)}>{value}</ActionListItem>
					{/each}
				</ActionList>
			{/snippet}
		</Dropdown>
	</div>

	{#if $issueStore.length === 0}
		<IconMessage empty message="No issues found" />
	{:else}
		<div class="list">
			{#each $issueStore as issue (issue.id)}
				<IssueRow {issue} {handleSelect} />
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
		display: flex;
		gap: 10px;
		align-items: center;
		flex-wrap: wrap;
	}

	.list {
		flex: 1;
		overflow: auto;
		padding: 20px 30px;
	}

	.dropdown-label {
		font-weight: normal;
	}

	.newsletter-dropdown {
		width: 320px;
		box-sizing: border-box;
		background-color: var(--box-background);
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
		overflow: hidden;
	}

	.newsletter-search {
		padding: 8px 8px 4px;
		box-sizing: border-box;
	}

	.newsletter-search :global(input) {
		box-sizing: border-box;
		width: 100%;
	}

	.newsletter-list {
		max-height: 320px;
		overflow-y: auto;
	}

	.newsletter-loader {
		padding: 16px;
		display: flex;
		justify-content: center;
	}

	.newsletter-name {
		font-weight: 500;
	}

	.newsletter-sub {
		color: var(--text-secondary, #6b7280);
		font-size: 0.85em;
		margin-left: 6px;
	}
</style>
