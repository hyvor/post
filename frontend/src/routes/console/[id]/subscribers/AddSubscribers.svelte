<script lang="ts">
	import {
		Checkbox,
		FormControl,
		Modal,
		SplitControl,
		Textarea,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { listStore } from '../../lib/stores/projectStore';
	import { createEventDispatcher } from 'svelte';
	import ListSelector from './ListSelector.svelte';
	import { createSubscriber } from '../../lib/actions/subscriberActions';

	export let show = false;

	let emailsString = '';
	let selectedList = $listStore.map((list) => list.id);

	let emailsError: null | string = null;

	let loading = false;

	function add() {
		const emails = emailsString
			.split('\n')
			.map((email) => email.trim())
			.filter((email) => email);

		if (emails.length === 0) {
			emailsError = 'Please enter at least one email.';
			return;
		}
		if (emails.length > 100) {
			emailsError = 'You can add up to 100 emails at once.';
			return;
		}

		if (selectedList.length === 0) {
			return;
		}

		loading = true;
		for (const email of emails) {
			createSubscriber(email, selectedList)
				.then(() => {
					show = false;
				})
				.catch((error) => {
					toast.error(`Failed to add subscriber ${error.message}.`);
				})
				.finally(() => {
					loading = false;
				});
		}
		
	}
</script>

<Modal
	bind:show
	title="Add subscribers"
	footer={{
		confirm: {
			text: 'Add'
		},
		cancel: {
			text: 'Cancel'
		}
	}}
	on:confirm={add}
	{loading}
>
	<SplitControl label="Emails" caption="Add one email per line">
		<FormControl>
			<Textarea
				rows={5}
				placeholder="user@example.com
other@example.org
"
				bind:value={emailsString}
				state={emailsError ? 'error' : 'default'}
			/>
			{#if emailsError}
				<Validation type="error">{emailsError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
	<SplitControl label="Segments">
		<ListSelector bind:selectedList />
	</SplitControl>
</Modal>
