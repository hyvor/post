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
	import { issueStore } from '../lib/stores/sudoStore';
	import IssueRow from './IssueRow.svelte';
	import type { IssueStatus, Issue } from '../types';
	import IconX from '@hyvor/icons/IconX';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import { ITEMS_PER_PAGE } from '../lib/generalActions';
	import { getIssues, ISSUE_STATUS_FILTERS } from '../lib/actions/issueActions';
	import { goto } from '$app/navigation';

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	let error: string | null = $state(null);

	let search: string | undefined = $state(undefined);
	let searchValue: string | undefined = $state(undefined);

	let statusFilter: IssueStatus | undefined = $state(undefined);
	let statusDropdownShow = $state(false);

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getIssues(
			search ?? null,
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

	function handleSelect(issue: Issue) {
		goto(`/sudo/issues/${issue.id}`);
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

	function onStatusClick(val: any) {
		statusFilter = val;
		load();
		statusDropdownShow = false;
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
		<TextInput bind:value={search} on:keyup={handleSearchKeyup} size="small">
			{#snippet start()}
				Subdomain
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
						<ActionListItem on:select={() => onStatusClick(key)}>{value}</ActionListItem
						>
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
	}

	.list {
		flex: 1;
		overflow: auto;
		padding: 20px 30px;
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

	.dropdown-label {
		font-weight: normal;
	}
</style>
