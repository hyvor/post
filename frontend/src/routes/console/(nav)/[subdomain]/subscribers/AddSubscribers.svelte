<script lang="ts">
	import {
		FormControl,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { listStore, subscriberStore } from '../../../lib/stores/newsletterStore';
	import ListSelector from './ListSelector.svelte';
	import { createSubscriber } from '../../../lib/actions/subscriberActions';

	interface Props {
		show?: boolean;
	}

	let { show = $bindable(false) }: Props = $props();

	let email = $state('');
	let selectedList = $state($listStore.map((list) => list.id));
	let emailsError: null | string = $state(null);
	let status: 'pending' | 'subscribed' = $state('pending');
	let input: HTMLInputElement | undefined = $state(undefined);

	$effect(() => {
		if (show && input) {
			input.focus();
		}
	});

	let loading = $state(false);

	function addSubscriber() {
		if (selectedList.length === 0) {
			return;
		}

		loading = true;
		createSubscriber(email, {
			lists: selectedList,
			status,
			list_skip_resubscribe_on: [] // force adding
		})
			.then((data) => {
				show = false;
				subscriberStore.update((subscriber) => [data, ...subscriber]);
			})
			.catch((error) => {
				toast.error(`Failed to add subscriber: ${error.message}.`);
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

<Modal
	bind:show
	title="Add subscriber"
	footer={{
		confirm: {
			text: 'Add'
		},
		cancel: {
			text: 'Cancel'
		}
	}}
	on:confirm={addSubscriber}
	{loading}
>
	<SplitControl label="Email">
		<FormControl>
			<TextInput
				placeholder="user@example.org"
				bind:value={email}
				bind:input
				state={emailsError ? 'error' : 'default'}
			/>
			{#if emailsError}
				<Validation type="error">{emailsError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl label="Lists">
		<ListSelector bind:selectedList setAllOnMount={true} />
	</SplitControl>

	<SplitControl label="Status">
		<FormControl>
			<Radio bind:group={status} name="status" value="pending">Pending (Recommended)</Radio>
			<Radio bind:group={status} name="status" value="subscribed">Subscribed</Radio>
		</FormControl>

		<div class="status-note">
			{#if status === 'pending'}
				Subscriber will receive a confirmation email to opt-in.
			{:else}
				Make sure you have the subscriber's consent.
			{/if}
		</div>
	</SplitControl>
</Modal>

<style>
	.status-note {
		margin-top: 5px;
		font-size: 14px;
		color: var(--text-light);
	}
</style>
