<script lang="ts">
	import {
		Divider,
		IconMessage,
		LoadButton,
		Modal,
		SplitControl,
		toast
	} from '@hyvor/design/components';
	import IconExclamationOctagon from '@hyvor/icons/IconExclamationOctagon';
	import type { ImportingSubscriber, SubscriberImport } from '../types';
	import ImportingSubscriberRow from './ImportingSubscriberRow.svelte';
	import { onMount } from 'svelte';
	import { getImportingSubscribers } from '../lib/actions/subscriberImportActions';
	import { ITEMS_PER_PAGE } from '../lib/generalActions';

	interface Props {
		show: boolean;
		subscriberImport: SubscriberImport;
		onApprove: (subscriberImport: SubscriberImport) => void;
	}

	let { show = $bindable(), subscriberImport, onApprove }: Props = $props();

	let loading = $state(true);
	let hasMore = $state(true);
	let loadingMore = $state(false);
	let parseError = $state<string | null>(null);

	let importingSubscribers: ImportingSubscriber[] = $state([]);

	function load(more = false) {
		more ? (loadingMore = true) : (loading = true);

		getImportingSubscribers(
			subscriberImport.id,
			ITEMS_PER_PAGE,
			more ? importingSubscribers.length : 0
		)
			.then((data) => {
				if (more) {
					importingSubscribers = [...importingSubscribers, ...data];
				} else {
					importingSubscribers = data;
				}
				hasMore = data.length === ITEMS_PER_PAGE;
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				loading = false;
				loadingMore = false;
			});
	}

	onMount(() => {
		if (subscriberImport.status === 'requires_input') {
			parseError = 'File not parsed for subscribers. Import is in REQUIRES_INPUT status.';
			loading = false;
			return;
		}

		load();
	});
</script>

<div class="subscriber-import-modal">
	<Modal
		bind:show
		{loading}
		title="Subscriber Import: {subscriberImport.newsletter_subdomain}"
		size="large"
		footer={{
			cancel: {
				text: 'Close'
			},
			confirm: {
				text: 'Approve',
				props: {
					disabled: subscriberImport.status !== 'pending_approval'
				}
			}
		}}
		on:confirm={() => onApprove(subscriberImport)}
	>
		<div class="content-wrap">
			<SplitControl label="Columns">
				{subscriberImport.columns.length === 0
					? 'No columns found'
					: subscriberImport.columns.join(' | ')}
			</SplitControl>

			<Divider color="var(--accent-light)" margin={2} />

			{#if parseError}
				<IconMessage icon={IconExclamationOctagon} iconSize={45} message={parseError} />
			{:else if importingSubscribers.length === 0}
				<IconMessage empty message="No subscribers found in the import file" />
			{:else}
				<div class="list">
					{#each importingSubscribers as importingSubscriber (importingSubscriber.email)}
						<ImportingSubscriberRow {importingSubscriber} />
					{/each}

					<LoadButton
						text="Load More"
						loading={loadingMore}
						show={hasMore}
						on:click={() => load(true)}
					/>
				</div>
			{/if}
		</div>
	</Modal>
</div>

<style>
	.content-wrap {
		max-height: 80vh;
		overflow-y: auto;
	}

	.list {
		flex: 1;
		overflow: auto;
	}

	.subscriber-import-modal :global(.inner) {
		width: 90vw !important;
	}
</style>
