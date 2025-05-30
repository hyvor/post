<script lang="ts">
	import {
		Button,
		ButtonGroup,
		ActionList,
		ActionListItem,
		TextInput,
		IconButton
	} from '@hyvor/design/components';
	import Selector from '../../@components/content/Selector.svelte';
	import type { List, NewsletterSubscriberStatus } from '../../types';
	import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
	import IconPlus from '@hyvor/icons/IconPlus';
	import SingleBox from '../../@components/content/SingleBox.svelte';
	import AddSubscribers from './AddSubscribers.svelte';
	import SubscriberList from './SubscriberList.svelte';
	import { listStore } from '../../lib/stores/newsletterStore';
	import { onMount } from 'svelte';
	import IconX from '@hyvor/icons/IconX';
	import { getI18n } from '../../lib/i18n';
	import { consoleUrlWithNewsletter } from '../../lib/consoleUrl';

	let key = $state(1); // for re-rendering
	let status: NewsletterSubscriberStatus | null = $state(null);
	let statusKey = $derived.by(() =>
		status ? status.charAt(0).toUpperCase() + status.slice(1) : 'All'
	);

	let showStatus = $state(false);
	let showList = $state(false);

	let currentList: List | null = $state(null);

	let searchVal: string = $state('');
	let search: string = $state('');
	let addingManually = $state(false);

	function selectList(list: List) {
		showList = false;
		currentList = list;
	}

	function selectStatus(s: NewsletterSubscriberStatus | null) {
		showStatus = false;
		status = s;
	}

	const searchActions = {
		onKeydown: (e: KeyboardEvent) => {
			if (e.key === 'Enter') {
				search = searchVal.trim();
			}
		},
		onBlur: () => {
			if (search !== searchVal) {
				search = searchVal.trim();
			}
		},
		onClear: () => {
			searchVal = '';
			search = '';
		}
	};

	const I18n = getI18n();

	onMount(() => {
		const url = new URL(window.location.href);
		const listId = Number(url.searchParams.get('list'));
		if (listId) {
			const list = $listStore.find((l) => l.id === listId);
			if (list) {
				currentList = list;
			}
		}
	});
</script>

<SingleBox>
	<div class="top">
		<div class="left">
			<Selector
				name={I18n.t('console.subscribers.status.label')}
				bind:show={showStatus}
				value={statusKey}
				width={200}
			>
				<ActionList selection="single" selectionAlign="end">
					<ActionListItem on:click={() => selectStatus(null)} selected={status === null}>
						{I18n.t('console.subscribers.status.all')}
					</ActionListItem>
					<ActionListItem
						on:click={() => selectStatus('subscribed')}
						selected={status === 'subscribed'}
					>
						{I18n.t('console.subscribers.status.subscribed')}
					</ActionListItem>
					<ActionListItem
						on:click={() => selectStatus('unsubscribed')}
						selected={status === 'unsubscribed'}
					>
						{I18n.t('console.subscribers.status.unsubscribed')}
					</ActionListItem>
					<ActionListItem
						on:click={() => selectStatus('pending')}
						selected={status === 'pending'}
					>
						{I18n.t('console.subscribers.status.pending')}
					</ActionListItem>
				</ActionList>
			</Selector>
			<Selector
				name={I18n.t('console.lists.list')}
				bind:show={showList}
				value={currentList ? currentList.name : I18n.t('console.common.any')}
				width={200}
				isSelected={!!currentList}
				handleDeselectClick={() => (currentList = null)}
			>
				<ActionList>
					{#each $listStore as list}
						<ActionListItem
							on:click={() => selectList(list)}
							selected={list.id === list?.id}
						>
							{list.name}
						</ActionListItem>
					{/each}
				</ActionList>
			</Selector>

			<div class="search-wrap">
				<TextInput
					bind:value={searchVal}
					placeholder="{I18n.t('console.common.search')}..."
					style="width:250px"
					on:keydown={searchActions.onKeydown}
					on:blur={searchActions.onBlur}
					size="small"
				>
					{#snippet end()}
						{#if searchVal.trim() !== ''}
							<IconButton
								variant="invisible"
								color="gray"
								size={16}
								on:click={searchActions.onClear}
							>
								<IconX size={12} />
							</IconButton>
						{/if}
					{/snippet}
				</TextInput>

				{#if search !== searchVal}
					<span class="press-enter"> ⏎ </span>
				{/if}
			</div>
		</div>
		<div class="right">
			<ButtonGroup>
				<Button
					size="small"
					color="input"
					as="a"
					href={consoleUrlWithNewsletter('/tools/import')}
				>
					{#snippet end()}
						<IconBoxArrowInDown />
					{/snippet}
					{I18n.t('console.tools.import.title')}
				</Button>
				<Button size="small" on:click={() => (addingManually = true)}>
					{#snippet end()}
						<IconPlus />
					{/snippet}
					{I18n.t('console.subscribers.addSubscribers')}
				</Button>
			</ButtonGroup>
		</div>
	</div>

	<SubscriberList
		{status}
		{key}
		list_id={currentList?.id || null}
		search={search === '' ? null : search}
	/>

	{#if addingManually}
		<AddSubscribers bind:show={addingManually} add={() => (key += 1)} />
	{/if}
</SingleBox>

<style>
	.top {
		display: flex;
		padding: 20px 30px;
		border-bottom: 1px solid var(--border);
	}
	.left {
		flex: 1;
	}
	.search-wrap {
		display: inline;
		.press-enter {
			color: var(--text-light);
			font-size: 14px;
			margin-left: 4px;
		}
		:global(input) {
			font-size: 14px;
		}
	}
</style>
