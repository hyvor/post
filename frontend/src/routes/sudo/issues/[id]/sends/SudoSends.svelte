<script lang="ts">
	import { onMount } from 'svelte';
	import {
		LoadButton,
		Loader,
		toast,
		TextInput,
		ActionList,
		ActionListItem,
		IconButton,
		IconMessage
	} from '@hyvor/design/components';
	import type { IssueSend, SendType } from '../../../types';
	import { getIssueSends } from '../../../lib/actions/issueActions';
	import SudoSendsRow from './SudoSendsRow.svelte';
	import Selector from '../../../../console/@components/content/Selector.svelte';
	import IconX from '@hyvor/icons/IconX';

	interface Props {
		issueId: number;
	}

	let { issueId }: Props = $props();

	let searchVal: string = $state('');
	let search: string = $state('');

	let type: SendType = $state('all');
	const typeKey = $derived(type.charAt(0).toUpperCase() + type.slice(1));

	let loading = $state(true);
	let loadingMore = $state(false);
	let showStatus = $state(false);
	let hasMore = $state(false);
	let sends: IssueSend[] = $state([]);

	const SENDS_LIMIT = 50;

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);
		getIssueSends(issueId, SENDS_LIMIT, more ? sends.length : 0, search, type)
			.then((res) => {
				sends = more ? [...sends, ...res] : res;
				hasMore = res.length === SENDS_LIMIT;
			})
			.catch((err) => {
				toast.error('Failed to load issue sends: ' + err.message);
			})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	function selectType(s: SendType) {
		showStatus = false;
		type = s;
		load();
	}

	const searchActions = {
		onKeydown: (e: KeyboardEvent) => {
			if (e.key === 'Enter') {
				search = searchVal.trim();
				load();
			}
		},
		onBlur: () => {
			if (search !== searchVal) {
				search = searchVal.trim();
				load();
			}
		},
		onClear: () => {
			searchVal = '';
			search = '';
			load();
		}
	};

	onMount(() => {
		load();
	});
</script>

<div class="wrap">
	{#if loading}
		<Loader block padding={100} />
	{:else}
		<Selector name="Status" bind:show={showStatus} value={typeKey} width={200}>
			<ActionList selection="single" selectionAlign="end">
				<ActionListItem on:click={() => selectType('all')} selected={type === 'all'}>
					All
				</ActionListItem>
				<ActionListItem
					on:click={() => selectType('unsubscribed')}
					selected={type === 'unsubscribed'}
				>
					Unsubscribed
				</ActionListItem>
				<ActionListItem
					on:click={() => selectType('bounced')}
					selected={type === 'bounced'}
				>
					Bounced
				</ActionListItem>
				<ActionListItem
					on:click={() => selectType('complained')}
					selected={type === 'complained'}
				>
					Complained
				</ActionListItem>
			</ActionList>
		</Selector>
		<div class="search-wrap">
			<TextInput
				bind:value={searchVal}
				placeholder={'Search...'}
				style="width:250px"
				on:keydown={searchActions.onKeydown}
				on:blur={searchActions.onBlur}
				size="small"
			>
				<svelte:fragment slot="end">
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
				</svelte:fragment>
			</TextInput>

			{#if search !== searchVal}
				<span class="press-enter"> ⏎ </span>
			{/if}
		</div>
		<div class="sends-wrap">
			{#if sends.length === 0}
				<IconMessage message="No sends found" empty />
			{:else}
				{#each sends as send (send.id)}
					<SudoSendsRow {send} />
				{/each}
				<LoadButton
					show={hasMore}
					loading={loadingMore}
					on:click={() => load(true)}
					text="Load more"
				/>
			{/if}
		</div>
	{/if}
</div>

<style lang="scss">
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

	.sends-wrap {
		padding: 10px;
	}
</style>
