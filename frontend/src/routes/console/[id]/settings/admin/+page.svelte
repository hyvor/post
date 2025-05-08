<script lang="ts">
	import { Button, Loader, SplitControl } from '@hyvor/design/components';
	import TopBar from '../../../@components/content/TopBar.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import AdminInvitationModal from './AdminInvitationModal.svelte';
	import { getProjectInvites, getProjectUsers } from '../../../lib/actions/userActions';
	import { onMount } from 'svelte';
	import type { Invite, User } from '../../../types';
	import InviteRow from './InviteRow.svelte';
	import AdminRow from './AdminRow.svelte';

    let loading = true;
    let users: User[] = [];
	let invites: Invite[] = [];

    function load() {
        getProjectUsers()
            .then((data) => {
                users = data;
            })
            .catch((e) => {
                console.error(e);
            });

		getProjectInvites()
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
		Add Admin 
        {#snippet end()}
            <IconPlus />
        {/snippet}
	</Button>

	{#if inviterOpen}
		<AdminInvitationModal
            bind:show={inviterOpen}
			refreshInvite={(i: Invite) => (invites = [i, ...invites])}
        />
	{/if}
</TopBar>

{#if loading}
	<Loader full />
{:else}
	<div class="moderators">
		<SplitControl label="Moderators" column>
			{#each users as user}
				<AdminRow user={user} />
			{/each}
		</SplitControl>

		<SplitControl label="Invites" column>
			{#if !invites.length}
				<div class="empty">No invites</div>
			{:else}
				{#each invites as invite}
					<InviteRow 
						invite={invite} 
						refreshInviteDelete={(i: Invite) => (invites = invites.filter((e) => e.id !== i.id))}
					/>
				{/each}
			{/if}
		</SplitControl>

	</div>
{/if}

<style lang="scss">
	.moderators {
		padding: 15px 30px;
	}

	.empty {
		color: var(--text-light);
	}
</style>
