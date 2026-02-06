<script lang="ts">
	import { Button, Loader, SplitControl } from '@hyvor/design/components';
	import TopBar from '../../../../@components/content/TopBar.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { getNewsletterUsers } from '../../../../lib/actions/userActions';
	import { onMount } from 'svelte';
	import type { User } from '../../../../types';
	import UserRow from './UserRow.svelte';
	import UserInvitationModal from './UserInvitationModal.svelte';
	import { getI18n } from '../../../../lib/i18n';

	let loading = true;
	let users: User[] = [];

	const I = getI18n();

	function load() {
		getNewsletterUsers()
			.then((data) => {
				users = data;
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
			refreshUsers={(u: User) => (users = [...users, u])}
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
	</div>
{/if}

<style lang="scss">
	.moderators {
		padding: 15px 30px;
	}
</style>
