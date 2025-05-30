<script lang="ts">
	import {
		Checkbox,
		FormControl,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { Subscriber } from '../../types';
	import { listStore, subscriberMetadataDefinitionStore } from '../../lib/stores/newsletterStore';
	import { updateSubscriber } from '../../lib/actions/subscriberActions';

	export let subscriber: Subscriber;
	export let show = false;
	export let refreshList: () => void;

	let email = subscriber.email;
	let status = subscriber.status;
	let selectedList = subscriber.list_ids;
	let metadata = { ...subscriber.metadata };

	let emailError: null | string = null;

	let loading = false;

	function onSegmentChange(id: number) {
		if (selectedList.includes(id)) {
			selectedList = selectedList.filter((l) => l !== id);
		} else {
			selectedList = [...selectedList, id];
		}
	}

	function submit() {
		const data: Record<string, any> = {};

		if (email.trim() !== subscriber.email) {
			data.email = email;
		}

		if (status !== subscriber.status) {
			data.status = status;
		}

		if (selectedList.sort().join(',') !== subscriber.list_ids.sort().join(',')) {
			data.segments = selectedList;
		}

		if (JSON.stringify(metadata) !== JSON.stringify(subscriber.metadata)) {
			data.metadata = metadata;
		}

		loading = true;
		emailError = null;

		updateSubscriber(subscriber.id, data)
			.then((res) => {
				toast.success('Subscriber updated successfully');
				show = false;
				refreshList();
			})
			.catch((err) => {
				if (err.message === 'email_taken') {
					emailError = 'This email is already taken.';
				} else {
					toast.error(err.message);
				}
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
			<TextInput bind:value={email} block state={emailError ? 'error' : undefined} />
			{#if emailError}
				<Validation type="error">{emailError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl label="Segments">
		{#each $listStore as list}
			<div class="segment">
				<Checkbox
					checked={selectedList.includes(list.id)}
					on:change={() => onSegmentChange(list.id)}
					disabled={selectedList.length === 1 && selectedList[0] === list.id}
				>
					{list.name}
				</Checkbox>
			</div>
		{/each}
	</SplitControl>

	<SplitControl label="Status">
		<FormControl>
			<Radio bind:group={status} value="subscribed">Subscribed</Radio>
			<Radio bind:group={status} value="unsubscribed">Unsubscribed</Radio>
			<Radio bind:group={status} value="pending">Pending</Radio>
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
	.segment {
		margin-bottom: 6px;
	}
</style>
