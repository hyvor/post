<script lang="ts">
	import {
		FormControl,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import type { Subscriber } from '../../../types';
	import { subscriberMetadataDefinitionStore } from '../../../lib/stores/newsletterStore';
	import ListSelector from './ListSelector.svelte';
	import {
		createSubscriber,
		type CreateSubscriberParams
	} from '../../../lib/actions/subscriberActions';

	interface Props {
		subscriber: Subscriber;
		show?: boolean;
		handleUpdate: (subscriber: Subscriber) => void;
	}

	let { subscriber, show = $bindable(false), handleUpdate }: Props = $props();

	let status = $derived(subscriber.status);
	let selectedList = $derived(subscriber.list_ids);
	let metadata = $derived({ ...subscriber.metadata });
	let loading = $state(false);

	function submit() {
		const data: CreateSubscriberParams = {
			lists_strategy: 'overwrite',
			metadata_strategy: 'overwrite',
			list_skip_resubscribe_on: []
		};

		if (status !== subscriber.status) {
			data.status = status;
		}

		if (selectedList.sort().join(',') !== subscriber.list_ids.sort().join(',')) {
			data.lists = selectedList;
		}

		if (JSON.stringify(metadata) !== JSON.stringify(subscriber.metadata)) {
			data.metadata = metadata;
		}

		loading = true;

		createSubscriber(subscriber.email, data)
			.then((res) => {
				toast.success('Subscriber updated successfully');
				show = false;
				handleUpdate(res);
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

<Modal
	title="Edit Subscriber"
	footer={{
		cancel: {
			text: 'Cancel'
		},
		confirm: {
			text: 'Save'
		}
	}}
	bind:show
	on:confirm={submit}
	{loading}
>
	<SplitControl label="Email">
		<FormControl>
			<TextInput value={subscriber.email} disabled />
		</FormControl>
	</SplitControl>

	<SplitControl label="Lists">
		<ListSelector bind:selectedList allowZero />
	</SplitControl>

	<SplitControl label="Status">
		<FormControl>
			<Radio bind:group={status} value="pending">Pending</Radio>
			<Radio bind:group={status} value="subscribed">Subscribed</Radio>
		</FormControl>
	</SplitControl>

	{#if $subscriberMetadataDefinitionStore.length > 0}
		<SplitControl label="Metadata" caption="Custom fields for this subscriber">
			{#snippet nested()}
				{#each $subscriberMetadataDefinitionStore as definition}
					<SplitControl label={definition.name}>
						<FormControl>
							<TextInput
								block
								bind:value={metadata[definition.key]}
								placeholder={`Enter ${definition.name.toLowerCase()}`}
							/>
						</FormControl>
					</SplitControl>
				{/each}
			{/snippet}
		</SplitControl>
	{/if}
</Modal>

<style>
	.list {
		margin-bottom: 6px;
	}
</style>
