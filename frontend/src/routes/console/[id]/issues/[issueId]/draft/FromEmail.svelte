<script lang="ts">
	import { Button, FormControl, Modal, Radio, SplitControl } from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';
	import { newsletterStore, sendingAddressesStore } from '../../../../lib/stores/newsletterStore';
	import { getAppConfig } from '../../../../lib/stores/consoleStore';
	import { draftIssueEditingStore } from './draftStore';
	import { debouncedUpdateDraftIssue } from './draftActions';
	import SendingAddresses from '../../../settings/sending/SendingAddresses.svelte';

	const I18n = getI18n();
	const appConfig = getAppConfig();

	let modal = $state(false);

	let addresses = $derived([
		$newsletterStore.slug + '@' + appConfig.app.default_email_domain,
		...$sendingAddressesStore.map((address) => address.email)
	]);
</script>

<SplitControl
	label={I18n.t('console.issues.draft.fromEmail')}
	caption={I18n.t('console.issues.draft.fromEmailCaption')}
>
	<div class="from-email">
		<FormControl>
			{#each addresses as address}
				<Radio
					bind:group={$draftIssueEditingStore.from_email}
					value={address}
					name="sending-email"
					style="font-weight:normal"
					on:change={debouncedUpdateDraftIssue}
				>
					{address}
				</Radio>
			{/each}
		</FormControl>
		<Button on:click={() => (modal = true)}>Manage Sending Emails</Button>
	</div>
</SplitControl>

<Modal bind:show={modal} title="Manage Sending Emails">
	<SendingAddresses />
</Modal>
