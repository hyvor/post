<script>
	import { Button } from '@hyvor/design/components';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { sendingProfilesStore } from '../../../lib/stores/newsletterStore';
	import SendingProfileRow from './SendingProfileRow.svelte';
	import { getI18n } from '../../../lib/i18n';
	import AddEditSendingProfileModal from './AddEditSendingProfileModal.svelte';

	let creating = $state(false);

	const I = getI18n();

	$effect(() => {
		console.log('creating', creating);
	})
</script>

<SettingsTop>
	<Button on:click={() => (creating = true)}>
		{I.t('console.settings.sendingProfiles.addSendingProfile')}
		{#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</SettingsTop>

<SettingsBody>
	<div class="help">
		Sending profiles can be used to configure multiple emails and branding to use when sending
		issues.
	</div>

	<div class="profiles">
		{#each $sendingProfilesStore as profile (profile.id)}
			<SendingProfileRow {profile} />
		{/each}
	</div>
</SettingsBody>

<AddEditSendingProfileModal bind:show={creating} />

<style>
	.help {
		font-size: 14px;
		color: var(--text-light);
	}
	.profiles {
		margin-top: 20px;
	}
</style>
