<script lang="ts">
	import { Button, Loader, SplitControl } from '@hyvor/design/components';
	import TopBar from '../../../../@components/content/TopBar.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { getNewsletterInvites, getNewsletterUsers } from '../../../../lib/actions/userActions';
	import { onMount } from 'svelte';
	import type { Invite, User } from '../../../../types';
	import InviteRow from './InviteRow.svelte';
	import UserRow from './UserRow.svelte';
	import UserInvitationModal from './UserInvitationModal.svelte';
    import {getI18n} from "../../../../lib/i18n";

	let loading = true;
	let users: User[] = [];
	let invites: Invite[] = [];
    const I = getI18n();
	function load() {
		getNewsletterUsers()
			.then((data) => {
				users = data;
			})
			.catch((e) => {
				console.error(e);
			});

		getNewsletterInvites()
			.then((data) => {
				invites = data;
			})
			.catch((e) => {
				console.error(e);
			})
			.finally(() => {
				loading = false;
			});
	}

	onMount(() => {
		load();
	});

	let inviterOpen = false;
</script>

<TopBar>
	<Button on:click={() => (inviterOpen = true)}>
        {I.t('console.settings.users.add')}
		{#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>

	{#if inviterOpen}
		<UserInvitationModal
			bind:show={inviterOpen}
			refreshInvite={(i: Invite) => (invites = [i, ...invites])}
		/>
	{/if}
</TopBar>

{#if loading}
	<Loader full />
{:else}
	<div class="moderators">
		<SplitControl label="Users" column>
			{#each users as user}
				<UserRow
					{user}
					refreshUserDelete={(u: User) => (users = users.filter((e) => e.id !== u.id))}
				/>
			{/each}
		</SplitControl>
		{#if invites.length > 0}
			<SplitControl label="Invites" column>
				{#each invites as invite}
					<InviteRow
						{invite}
						refreshInviteDelete={(i: Invite) =>
							(invites = invites.filter((e) => e.id !== i.id))}
					/>
				{/each}
			</SplitControl>
		{/if}
	</div>
{/if}

<style lang="scss">
	.moderators {
		padding: 15px 30px;
	}
</style>
