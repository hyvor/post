<script lang="ts">
	import {
		TableRow,
		Switch,
		Button,
		IconButton,
		Dropdown,
		ActionList,
		ActionListItem,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { User } from '../../../types';
	import ProfilePicture from '../../../@components/utils/ProfilePicture.svelte';
	import RoleTag from '../../../@components/Nav/RoleTag.svelte';
	import { projectStore } from '../../../lib/stores/projectStore';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deleteUser } from '../../../lib/actions/userActions';

	export let refreshUserDelete: (u: User) => void;
	export let user: User;

	async function handleRemove() {
		const confirmation = await confirm({
			title: "Remove Admin",
			content: "Are you sure you want to remove this admin?",
			confirmText: "Yes",
			cancelText: "Cancel",
			danger: true
		});
		if (!confirmation) return;

		confirmation.loading();

		deleteUser(user.id)
			.then(() => {
				toast.success('User deleted successfully');
				refreshUserDelete(user);
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				confirmation.close();
			});
	}
</script>

<TableRow>

	<div class="profile">
		<ProfilePicture size={25} user={user.user} />
		<div class="name-badge">
			<div class="name">
				{user.user.name}
				{#if user.user.username}
					<span class="username">@{user.user.username}</span>
				{/if}
			</div>

			<div class="badge">
				<RoleTag size="x-small" role={user.role} />
			</div>
		</div>
		<div class="buttons">
			{#if user.role == 'owner' && $projectStore.user_role != 'owner'}
				<IconButton size={27} on:click={handleRemove}>
					<IconTrash size={13} />
				</IconButton>
			{/if}
		</div>
	</div>
	

</TableRow>

<style>
	.profile {
		display: flex;
		flex: 1;
		align-items: center;
	}

	.name-badge {
		margin-left: 6px;
	}
	.buttons {
		display: flex;
		gap: 10px;
		align-items: center;
	}

	.username {
		color: var(--text-light);
		font-size: 12px;
	}

	.badge {
		font-weight: 600;
		text-transform: uppercase;
	}
	.badge :global(span) {
		font-size: 8px;
	}
</style>
