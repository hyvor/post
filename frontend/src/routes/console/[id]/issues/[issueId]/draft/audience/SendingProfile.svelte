<script>
	import { Radio, SplitControl } from '@hyvor/design/components';
	import { sendingProfilesStore } from '../../../../../lib/stores/newsletterStore';

	let sendingProfileId = $state($sendingProfilesStore[0]?.id || 0);
</script>

<SplitControl label="Sending Profile">
	{#each $sendingProfilesStore as profile (profile.id)}
		<div class="profile">
			<Radio bind:group={sendingProfileId} value={profile.id} name="sending-profile">
				{profile.from_email} - {profile.from_name}
			</Radio>
		</div>
	{/each}
</SplitControl>

<style>
	.profile {
		padding: 6px 0;
	}
</style>
